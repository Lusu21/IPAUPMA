<?php
// Archivo: IPAUPMA/bd/crud_vacunas.php
session_start();
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

// Recepción de datos desde JS
$opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';

// Preparar respuesta JSON por defecto
$data = null;

switch($opcion){
    case 1: // LISTAR 
        $consulta = "SELECT id, nombre, laboratorio, lote, fecha_vencimiento, cantidad FROM inventario_vacunas WHERE estado=1 ORDER BY id DESC";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute();
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        ob_clean(); // Borra el "1" o cualquier basura anterior

        print json_encode($data, JSON_UNESCAPED_UNICODE);
        break;

    case 2: // REGISTRAR O EDITAR
        $id = (isset($_POST['id'])) ? $_POST['id'] : '';
        $nombre = $_POST['nombre'];
        $laboratorio = $_POST['laboratorio'];
        $lote = $_POST['lote'];
        $fecha_vencimiento = $_POST['fecha_vencimiento'];
        $cantidad = $_POST['cantidad'];

        if($id == '' || $id == null){
            // Insertar Nuevo
            $consulta = "INSERT INTO inventario_vacunas (nombre, laboratorio, lote, fecha_vencimiento, cantidad) VALUES(?, ?, ?, ?, ?)";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute([$nombre, $laboratorio, $lote, $fecha_vencimiento, $cantidad]);
        } else {
            // Actualizar Existente
            $consulta = "UPDATE inventario_vacunas SET nombre=?, laboratorio=?, lote=?, fecha_vencimiento=?, cantidad=? WHERE id=?";
            $resultado = $conexion->prepare($consulta);
            $resultado->execute([$nombre, $laboratorio, $lote, $fecha_vencimiento, $cantidad, $id]);
        }
        print json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        break;

    case 3: // ELIMINAR (Borrado lógico)
        $id = $_POST['id'];
        $consulta = "UPDATE inventario_vacunas SET estado=0 WHERE id=?";
        $resultado = $conexion->prepare($consulta);
        $resultado->execute([$id]);
        print json_encode(["success" => true], JSON_UNESCAPED_UNICODE);
        break;

    case 4: // REGISTRAR USO (Descontar Stock)
        $id = $_POST['id'];
        $cantidad_usada = $_POST['cantidad_usada'];

        // 1. Consultar stock actual
        $sql = "SELECT cantidad FROM inventario_vacunas WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id]);
        $stock_actual = $stmt->fetchColumn();

        // 2. Validar que no quede en negativo
        if ($stock_actual >= $cantidad_usada) {
            $nuevo_stock = $stock_actual - $cantidad_usada;
            
            $update = "UPDATE inventario_vacunas SET cantidad = ? WHERE id = ?";
            $res = $conexion->prepare($update);
            $res->execute([$nuevo_stock, $id]);
            
            // Devolvemos el nuevo stock para actualizar la tabla si se quiere (o recargar)
            print json_encode(["success" => true, "message" => "Stock actualizado correctamente.", "nuevo_stock" => $nuevo_stock], JSON_UNESCAPED_UNICODE);
        } else {
            print json_encode(["success" => false, "message" => "Error: La cantidad a descontar supera el stock actual."], JSON_UNESCAPED_UNICODE);
        }
        break;
}

$conexion = NULL;
?>
