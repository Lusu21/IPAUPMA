<?php
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

$nombre_apellido = $_POST['nombre_apellido'];
$cedula = $_POST['cedula'];
$cargo = $_POST['cargo'];
$correo = $_POST['correo'];
$estado = $_POST['estado'];

$sql = "INSERT INTO empleados (nombre_apellido, cedula, cargo, correo, estado) VALUES (?, ?, ?, ?, ?)";
$query = $conexion->prepare($sql);
$result = $query->execute([$nombre_apellido, $cedula, $cargo, $correo, $estado]);

echo json_encode(['success' => $result]);
?>
