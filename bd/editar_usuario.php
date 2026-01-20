<?php
if (!isset($conexion)) {
    try {
        $host = 'localhost';
        $db = 'ipaupma'; 
        $user = 'root';      
        $pass = '';          
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $conexion = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];
    $password = $_POST['password']; 

    try {
        // Preparar la consulta UPDATE
        $sql = "UPDATE usuarios SET username = ?, rol = ?, estado = ? ";
        $params = [$username, $rol, $estado];

        // Si se proporciona una nueva contraseña, hashearla y añadirla a la consulta
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ? ";
            $params[] = $hashed_password;
        }

        $sql .= "WHERE id = ?";
        $params[] = $id;

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        // Redirigir con mensaje de éxito
        header("Location: ../usuarios.php?exito=editado");
        exit();

    } catch (PDOException $e) {
        // Redirigir con mensaje de error
        header("Location: ../usuarios.php?error=bd_fallo_edicion");
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir o mostrar un error
    header("Location: ../usuarios.php?error=metodo_no_permitido");
    exit();
}
?>