<?php
// Asegúrate de que session_start() es lo primero en tu archivo, antes de cualquier HTML.
session_start();

// Incluye el archivo de autenticación que maneja la sesión y la conexión a la base de datos.
// Si el usuario no tiene una sesión activa, lo redirigimos inmediatamente al login.
// Esto es una medida de seguridad extra, aunque en logout.php ya esperamos que haya sesión.
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Asegúrate de que esta ruta sea correcta
    exit();
}

// 1. Limpia todas las variables de la sesión actual.
$_SESSION = array();

// 2. Si se usan cookies de sesión, destruye la cookie de sesión.
// Esto es crucial para invalidar la sesión en el navegador del usuario.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruye la sesión en el servidor.
session_destroy();

// 4. Encabezados para evitar el almacenamiento en caché del navegador.
// Estos le dicen al navegador que no guarde una copia de esta página.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Una fecha en el pasado muy distante.

// 5. JavaScript para redirigir y borrar el historial del navegador.
// window.location.replace() es clave aquí porque reemplaza la entrada actual del historial,
// impidiendo que el botón "atrás" vuelva a la página de donde vienes.
echo '<script>
    window.location.replace("../index.php");
    history.pushState(null, "", "../index.php");
    window.onpopstate = function() {
        history.go(1); // Previene que el usuario regrese a la página anterior.
    };
</script>';
// 6. Finaliza el script para asegurarte de que no se ejecute más código PHP.
exit(); // Asegura que no se ejecute más código PHP después de la redirección.
?>