<?php
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

$id = $_POST['id'];
$nombre_apellido = $_POST['nombre_apellido'];
$cedula = $_POST['cedula'];
$cargo = $_POST['cargo'];
$correo = $_POST['correo'];
$estado = $_POST['estado'];

$sql = "UPDATE empleados SET nombre_apellido=?, cedula=?, cargo=?, correo=?, estado=? WHERE id=?";
$query = $conexion->prepare($sql);
$result = $query->execute([$nombre_apellido, $cedula, $cargo, $correo, $estado, $id]);
echo json_encode(['success' => $result]);
?>