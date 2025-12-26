<?php
// Habilitar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Verificar si hay sesión
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('No hay sesión activa');
    }

    // Verificar ID
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('ID no válido');
    }

    $obj = new conexion();
    $conexion = $obj->conectar();

    // Iniciar transacción para asegurar consistencia
    $conexion->beginTransaction();

    // 1. ACTUALIZAR PRODUCTOR
    $sql_productor = "UPDATE productores SET 
                        nombre = ?, 
                        apellido = ?, 
                        cedula = ?, 
                        parroquia_id = ?, 
                        sector = ?, 
                        telefono = ?, 
                        rif = ?, 
                        fecha_nacimiento = ?, 
                        grado_instruccion = ?, 
                        oficio_ocupacion = ?
                      WHERE id = ?";
    
    $stmt = $conexion->prepare($sql_productor);
    $stmt->execute([
        $_POST['nombre'] ?? '',
        $_POST['apellido'] ?? '',
        $_POST['cedula'] ?? '',
        $_POST['parroquia_id'] ?? NULL,
        $_POST['sector'] ?? '',
        $_POST['telefono'] ?? '',
        $_POST['rif'] ?? '',
        $_POST['fecha_nacimiento'] ?: NULL,
        $_POST['grado_instruccion'] ?? '',
        $_POST['oficio_ocupacion'] ?? '',
        $id
    ]);

    // Obtener ID del predio (si existe)
    $sql_check_predio = "SELECT id FROM predios WHERE productor_id = ? LIMIT 1";
    $stmt = $conexion->prepare($sql_check_predio);
    $stmt->execute([$id]);
    $predio_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    $predio_id = $predio_existe['id'] ?? null;

    // Convertir checkboxes a valores booleanos para PREDIO
    $posee_casa = isset($_POST['posee_casa']) ? 1 : 0;
    $posee_tanque = isset($_POST['posee_tanque']) ? 1 : 0;
    $posee_pozos = isset($_POST['posee_pozos']) ? 1 : 0;
    $posee_corral = isset($_POST['posee_corral']) ? 1 : 0;
    $posee_perimetral = isset($_POST['posee_perimetral']) ? 1 : 0;
    $posee_barbacoa = isset($_POST['posee_barbacoa']) ? 1 : 0;
    $registro_inti = isset($_POST['registro_inti']) ? 1 : 0;
    $registro_hierro = isset($_POST['registro_hierro']) ? 1 : 0;
    $servicio_agua = isset($_POST['servicio_agua']) ? 1 : 0;
    $servicio_gas = isset($_POST['servicio_gas']) ? 1 : 0;
    $servicio_electricidad = isset($_POST['servicio_electricidad']) ? 1 : 0;
    $servicio_internet = isset($_POST['servicio_internet']) ? 1 : 0;
    $servicio_transporte = isset($_POST['servicio_transporte']) ? 1 : 0;
    $servicio_maquinaria = isset($_POST['servicio_maquinaria']) ? 1 : 0;

    if ($predio_existe) {
        // Actualizar predio existente
        $sql_predio = "UPDATE predios SET 
                        nombre_predio = ?, 
                        hectareas = ?, 
                        posee_casa = ?,
                        posee_tanque = ?,
                        posee_pozos = ?,
                        posee_corral = ?,
                        posee_perimetral = ?,
                        posee_barbacoa = ?,
                        registro_inti = ?,
                        registro_hierro = ?,
                        servicio_agua = ?, 
                        servicio_gas = ?,
                        servicio_electricidad = ?, 
                        servicio_internet = ?,
                        servicio_transporte = ?,
                        servicio_maquinaria = ?
                       WHERE productor_id = ?";
        
        $stmt = $conexion->prepare($sql_predio);
        $stmt->execute([
            $_POST['nombre_predio'] ?? '',
            $_POST['hectareas'] ?? 0,
            $posee_casa,
            $posee_tanque,
            $posee_pozos,
            $posee_corral,
            $posee_perimetral,
            $posee_barbacoa,
            $registro_inti,
            $registro_hierro,
            $servicio_agua,
            $servicio_gas,
            $servicio_electricidad,
            $servicio_internet,
            $servicio_transporte,
            $servicio_maquinaria,
            $id
        ]);
        $predio_id = $predio_existe['id'];
    } else {
        // Insertar nuevo predio
        $sql_predio = "INSERT INTO predios 
                        (productor_id, nombre_predio, hectareas, 
                         posee_casa, posee_tanque, posee_pozos, posee_corral, 
                         posee_perimetral, posee_barbacoa,
                         registro_inti, registro_hierro,
                         servicio_agua, servicio_gas, servicio_electricidad, 
                         servicio_internet, servicio_transporte, servicio_maquinaria) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexion->prepare($sql_predio);
        $stmt->execute([
            $id,
            $_POST['nombre_predio'] ?? '',
            $_POST['hectareas'] ?? 0,
            $posee_casa,
            $posee_tanque,
            $posee_pozos,
            $posee_corral,
            $posee_perimetral,
            $posee_barbacoa,
            $registro_inti,
            $registro_hierro,
            $servicio_agua,
            $servicio_gas,
            $servicio_electricidad,
            $servicio_internet,
            $servicio_transporte,
            $servicio_maquinaria
        ]);
        $predio_id = $conexion->lastInsertId();
    }

    // 3. ACTUALIZAR RECOMENDACIONES (tabla separada)
    if ($predio_id) {
        $recomendaciones = trim($_POST['recomendaciones'] ?? '');
        
        // Verificar si existe registro de recomendaciones
        $sql_check_recomendaciones = "SELECT id FROM recomendaciones WHERE predio_id = ? LIMIT 1";
        $stmt = $conexion->prepare($sql_check_recomendaciones);
        $stmt->execute([$predio_id]);
        $recomendaciones_existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($recomendaciones)) {
            if ($recomendaciones_existe) {
                $sql_recomendaciones = "UPDATE recomendaciones SET 
                                        recomendaciones = ?,
                                        fecha_registro = CURDATE()
                                       WHERE predio_id = ?";
            } else {
                $sql_recomendaciones = "INSERT INTO recomendaciones 
                                        (predio_id, recomendaciones, fecha_registro)
                                       VALUES (?, ?, CURDATE())";
            }
            
            $stmt = $conexion->prepare($sql_recomendaciones);
            $stmt->execute([$recomendaciones, $predio_id]);
        } elseif ($recomendaciones_existe) {
            // Si hay registro pero ahora está vacío, eliminar
            $sql_delete_recomendaciones = "DELETE FROM recomendaciones WHERE predio_id = ?";
            $stmt = $conexion->prepare($sql_delete_recomendaciones);
            $stmt->execute([$predio_id]);
        }
    }

    // 4. ACTUALIZAR AGRICULTURA
    if ($predio_id) {
        // Verificar si existe registro de agricultura
        $sql_check_agricultura = "SELECT id FROM agricultura WHERE predio_id = ? LIMIT 1";
        $stmt = $conexion->prepare($sql_check_agricultura);
        $stmt->execute([$predio_id]);
        $agricultura_existe = $stmt->fetch(PDO::FETCH_ASSOC);

        // Campos de agricultura según tu tabla
        if ($agricultura_existe) {
            $sql_agricultura = "UPDATE agricultura SET 
                                tipo_cultivo = ?,
                                area_cultivada = ?,
                                tiempo_sembrado = ?,
                                canal_comercializacion = ?,
                                cultivo_principal = ?,
                                cultivo_secundario = ?,
                                cantidad_cultivada = ?,
                                venta_producto = ?
                               WHERE predio_id = ?";
        } else {
            $sql_agricultura = "INSERT INTO agricultura 
                                (predio_id, tipo_cultivo, area_cultivada, tiempo_sembrado,
                                 canal_comercializacion, cultivo_principal, cultivo_secundario,
                                 cantidad_cultivada, venta_producto)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }
        
        $stmt = $conexion->prepare($sql_agricultura);
        $stmt->execute([
            $predio_id,
            $_POST['tipo_cultivo'] ?? '',
            $_POST['area_cultivada'] ?? '',
            $_POST['tiempo_sembrado'] ?? '',
            $_POST['canal_comercializacion'] ?? '',
            $_POST['cultivo_principal'] ?? '',
            $_POST['cultivo_secundario'] ?? '',
            $_POST['cantidad_cultivada'] ?? '',
            $_POST['venta_producto'] ?? ''
        ]);
    }

    // 5. ACTUALIZAR GANADERÍA (con campo cant_maticas SIN tilde)
    if ($predio_id) {
        // Verificar si existe registro de ganadería
        $sql_check_ganaderia = "SELECT id FROM ganaderia WHERE predio_id = ? LIMIT 1";
        $stmt = $conexion->prepare($sql_check_ganaderia);
        $stmt->execute([$predio_id]);
        $ganaderia_existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ganaderia_existe) {
            $sql_ganaderia = "UPDATE ganaderia SET 
                                cant_vaca = ?,
                                cant_toro = ?,
                                cant_novillo = ?,
                                cant_maticas = ?,
                                cant_mautes = ?,
                                cant_becerros = ?,
                                cant_becerras = ?,
                                cant_bufalo = ?,
                                cant_bufala = ?,
                                cant_chivo = ?,
                                cant_cabra = ?,
                                cant_ovejo = ?,
                                cant_oveja = ?,
                                cant_verraco = ?,
                                cant_cerda_madre = ?,
                                cant_levantes = ?,
                                cant_lechones = ?,
                                cant_pollo_engorde = ?,
                                cant_gallinas_ponedoras = ?,
                                cant_gallinas_patio = ?,
                                cant_alevines = ?,
                                cant_peces = ?,
                                cant_reproductores = ?
                               WHERE predio_id = ?";
        } else {
            $sql_ganaderia = "INSERT INTO ganaderia 
                                (predio_id, cant_vaca, cant_toro, cant_novillo, cant_maticas,
                                 cant_mautes, cant_becerros, cant_becerras, cant_bufalo,
                                 cant_bufala, cant_chivo, cant_cabra, cant_ovejo, cant_oveja,
                                 cant_verraco, cant_cerda_madre, cant_levantes, cant_lechones,
                                 cant_pollo_engorde, cant_gallinas_ponedoras, cant_gallinas_patio,
                                 cant_alevines, cant_peces, cant_reproductores)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }
        
        $stmt = $conexion->prepare($sql_ganaderia);
        $stmt->execute([
            $predio_id,
            $_POST['cant_vaca'] ?? 0,
            $_POST['cant_toro'] ?? 0,
            $_POST['cant_novillo'] ?? 0,
            $_POST['cant_maticas'] ?? 0,
            $_POST['cant_mautes'] ?? 0,
            $_POST['cant_becerros'] ?? 0,
            $_POST['cant_becerras'] ?? 0,
            $_POST['cant_bufalo'] ?? 0,
            $_POST['cant_bufala'] ?? 0,
            $_POST['cant_chivo'] ?? 0,
            $_POST['cant_cabra'] ?? 0,
            $_POST['cant_ovejo'] ?? 0,
            $_POST['cant_oveja'] ?? 0,
            $_POST['cant_verraco'] ?? 0,
            $_POST['cant_cerda_madre'] ?? 0,
            $_POST['cant_levantes'] ?? 0,
            $_POST['cant_lechones'] ?? 0,
            $_POST['cant_pollo_engorde'] ?? 0,
            $_POST['cant_gallinas_ponedoras'] ?? 0,
            $_POST['cant_gallinas_patio'] ?? 0,
            $_POST['cant_alevines'] ?? 0,
            $_POST['cant_peces'] ?? 0,
            $_POST['cant_reproductores'] ?? 0
        ]);
    }

    // 6. ACTUALIZAR CARGA FAMILIAR
    // Primero eliminar los registros existentes
    $sql_delete_carga = "DELETE FROM carga_familiar WHERE productor_id = ?";
    $stmt = $conexion->prepare($sql_delete_carga);
    $stmt->execute([$id]);

    // Luego insertar los nuevos si existen
    if (isset($_POST['carga_nombre']) && is_array($_POST['carga_nombre'])) {
        $sql_carga = "INSERT INTO carga_familiar 
                      (productor_id, nombre, edad, cedula, parentesco, ocupacion) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql_carga);
        
        for ($i = 0; $i < count($_POST['carga_nombre']); $i++) {
            // Solo insertar si hay al menos un nombre
            $nombre = trim($_POST['carga_nombre'][$i] ?? '');
            if (!empty($nombre)) {
                $edad = $_POST['carga_edad'][$i] ?? NULL;
                // Convertir edad vacía a NULL
                $edad = ($edad === '' || $edad === null) ? NULL : (int)$edad;
                
                $stmt->execute([
                    $id,
                    $nombre,
                    $edad,
                    $_POST['carga_cedula'][$i] ?? '',
                    $_POST['carga_parentesco'][$i] ?? '',
                    $_POST['carga_ocupacion'][$i] ?? ''
                ]);
            }
        }
    }

    // Confirmar transacción
    $conexion->commit();

    $response['success'] = true;
    $response['message'] = 'Datos actualizados correctamente';
    $response['predio_id'] = $predio_id ?? null;

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($conexion) && $conexion) {
        try {
            $conexion->rollBack();
        } catch (Exception $rollbackError) {
            // Ignorar error de rollback
        }
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['error_detail'] = $e->getTraceAsString();
    error_log("Error en guardar_productor.php: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());
}

echo json_encode($response);