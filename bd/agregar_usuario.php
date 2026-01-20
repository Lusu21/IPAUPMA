<?php
// 1. Verificar sesión y permisos
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php?error=acceso-denegado");
    exit();
}

// 2. Incluir conexión a base de datos
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

// 3. Determinar el nombre de usuario (LÓGICA PRINCIPAL)
$username = '';

// Opción A: Se seleccionó un empleado del dropdown
if (isset($_POST['username_or_new']) && !empty(trim($_POST['username_or_new']))) {
    $username = trim($_POST['username_or_new']);
} 
// Opción B: Se escribió un nombre personalizado (opción vacía seleccionada)
elseif (isset($_POST['nuevo_username']) && !empty(trim($_POST['nuevo_username']))) {
    $username = trim($_POST['nuevo_username']);
} 
// Opción C: Error - no se proporcionó ningún nombre de usuario
else {
    header("Location: ../usuarios.php?error=no_usuario_valido");
    exit();
}

// 4. Validaciones adicionales
if (strlen($username) < 3) {
    header("Location: ../usuarios.php?error=no_usuario_valido");
    exit();
}

// 5. Obtener otros datos del formulario
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? 'usuario';
$estado = intval($_POST['estado'] ?? 1);

// 6. Validar contraseña
if (strlen($password) < 6) {
    header("Location: ../usuarios.php?error=password_corta");
    exit();
}

try {
    // 7. Verificar si el usuario ya existe en la base de datos
    $consulta_verificar = "SELECT COUNT(*) as total FROM usuarios WHERE username = :username";
    $resultado_verificar = $conexion->prepare($consulta_verificar);
    $resultado_verificar->execute([':username' => $username]);
    $existe = $resultado_verificar->fetch(PDO::FETCH_ASSOC);
    
    if ($existe['total'] > 0) {
        header("Location: ../usuarios.php?error=usuario_existe");
        exit();
    }

    // 8. Hashear la contraseña para seguridad
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 9. Insertar el nuevo usuario en la base de datos
    $consulta_insertar = "INSERT INTO usuarios (username, password, rol, estado) 
                          VALUES (:username, :password, :rol, :estado)";
    
    $resultado_insertar = $conexion->prepare($consulta_insertar);
    $resultado_insertar->execute([
        ':username' => $username,
        ':password' => $password_hash,
        ':rol' => $rol,
        ':estado' => $estado
    ]);

    // 10. Redirigir a la página de usuarios con mensaje de éxito
    header("Location: ../usuarios.php?exito=agregado");
    exit();

} catch (PDOException $e) {
    // 11. Manejar errores de base de datos
    error_log("ERROR en agregar_usuario.php - " . date('Y-m-d H:i:s') . " - " . $e->getMessage());
    
    // Si es error de duplicado (aunque ya verificamos, por si acaso)
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        header("Location: ../usuarios.php?error=usuario_existe");
    } else {
        header("Location: ../usuarios.php?error=bd_fallo");
    }
    exit();
}