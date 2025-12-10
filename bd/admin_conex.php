<?php
// conexion.php
$servidor = "localhost";
$usuario = "root";       // Usuario de XAMPP por defecto
$contraseña = "";        // Contraseña vacía por defecto
$basedatos = "ipaupma"; // Nombre de tu base de datos
$puerto = 3306;          // Puerto de MySQL (o 8080 si lo cambiaste)

// Conexión con MySQLi
$conn = new mysqli($servidor, $usuario, $contraseña, $basedatos, $puerto);

// Verificar errores
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: Configurar charset (recomendado)
$conn->set_charset("utf8mb4");
?>