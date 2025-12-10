<?php
// 1. Iniciar sesión y verificar si el usuario es administrador
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php?error=acceso-denegado"); // Redirige al login si no hay sesión activa o no es admin
    exit();
}

// 2. Validar rol de administrador (desde la base de datos)
require_once 'bd/admin_conex.php'; // Incluye tu conexión a la DB
$stmt = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || $result->fetch_assoc()['rol'] !== 'admin') {
    //Registrar intento de acceso no autorizado (opcional)
    error_log("Intento de acceso no autorizado: User ID {$_SESSION['user_id']}");

    //Redirigir con mensaje genérico (no revelar detalles)
    header("Location: index.php?error=acceso-denegado");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Una fecha en el pasado.
?>

<?php
// Conectar a la base de datos MySQL
$host = "localhost"; // o tu host
$usuario = "root"; // tu usuario, por defecto en XAMPP
$password = ""; // tu contraseña, en XAMPP suele ser vacía
$basedatos = "ipaupma"; // reemplaza con tu base de datos

$conn = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consultar administradores
$sqlAdmin = "SELECT `id`, `username`, `password`, `rol` FROM `usuarios` WHERE `rol`='admin'";
$resultAdmin = $conn->query($sqlAdmin);

// Consultar usuarios normales
$sqlUsuario = "SELECT `id`, `username`, `password`, `rol` FROM `usuarios` WHERE `rol`='presi'";
$resultUsuario = $conn->query($sqlUsuario);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPAUPMA</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <div class="menu">
        <ion-icon name="menu-outline"></ion-icon>
        <ion-icon name="close-outline"></ion-icon>
    </div>

    <!-- Barra lateral -->
    <div class="barra-lateral">
        <div>
            <div class="nombre-pagina">
                <ion-icon id="cloud" name="cloud-outline"></ion-icon>
                <span>IPAUPMA</span> 
            </div>
        </div>
        <nav class="navegacion">
            <ul>
                <li>
                    <a id="dashboard" href="dashboard.php">
                        <ion-icon name="desktop-outline"></ion-icon>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="usuarios.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="lista_censos.php">
                        <ion-icon name="person-outline"></ion-icon>
                        <span>Lista de Censados</span>
                    </a>
                </li>
                <li>
                    <a href="inscripcion_censo.php">
                        <ion-icon name="person-add-outline"></ion-icon>
                        <span>Inscripción Censo</span>
                    </a>
                </li>
                <li>
                    <a href="../bd/logout.php" id="logoutLink">
                        <ion-icon name="log-out-outline"></ion-icon>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div>
            <div class="linea"></div>
            <div class="modo-oscuro">
                <div class="info">
                    <ion-icon name="moon-outline"></ion-icon>
                    <span>Modo Oscuro</span>
                </div>
                <div class="switch">
                    <div class="base">
                        <div class="circulo"></div>
                    </div>
                </div>
            </div>
            <div class="usuario">
                <img src="img/alcaldia.png" alt="">
                <div class="info-usuario">
                    <div class="perfil">
                        <span class="perfil"><br>Alcaldía Bolivariana<br>de Maracaibo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal (fuera de la barra lateral) -->
    <main class="contenido-principal">
    </main>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    // Obtener el username del usuario conectado
    $stmtUser = $conn->prepare("SELECT username FROM usuarios WHERE id = ?");
    $stmtUser->bind_param("i", $_SESSION['user_id']);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    if ($rowUser = $resultUser->fetch_assoc()) {
        $username = $rowUser['username'];
    } else {
        $username = "Usuario"; // Valor por defecto
    }
    ?>
    <script>
        // Solo en la sesión actual, muestra la bienvenida en el primer ingreso
        const username = <?php echo json_encode($username); ?>;
        if (!sessionStorage.getItem('bienvenidaMostrada')) {
            Swal.fire({
                title: '¡Bienvenido ' + username + '!',
                text: 'Has ingresado al panel de administración del Sistema de Gestión IPAUPMA.',
                icon: 'success',
                showClass: {
                    popup: 'animate__animated animate__bounceInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                confirmButtonText: 'Gracias'
            });
            sessionStorage.setItem('bienvenidaMostrada', 'true');
        }
    </script>


    <!-- Alerta de cierre de sesión -->
    <script>
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault(); // Evita que el enlace navegue inmediatamente
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas cerrar sesión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar',
                showClass: {
                    popup: 'animate__animated animate__bounceInDown' // Entrada impactante
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp' // Salida suave
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'bd/logout.php'; // Redirige a logout
                }
            });
        });
    </script>
</body>

</html>