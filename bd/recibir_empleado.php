<?php
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $consulta = "SELECT * FROM empleados WHERE id = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute([$id]);
    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($profesor);
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}
?>