<?php
session_start();
$error = ""; 
$conn = new mysqli("localhost", "root", "", "ipaupma");

if ($conn->connect_error) {
    $error = "No se pudo conectar a la base de datos. Intenta más tarde."; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) { 
    $username = $_POST['username'];
    $password = $_POST['password'];


    $stmt = $conn->prepare("SELECT id, username, password, rol, estado FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        
        if ($password == $user['password']) {
            // Verificacion del estado del usuario
            if ($user['estado'] == 1) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['rol'] = $user['rol'];

                
                if ($user['rol'] == 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard_prof.php"); 
                }
                exit(); 
            } else {
                $error = "Tu cuenta se encuentra inactiva. Contacta al administrador.";
            }
        } else {
            $error = "Contraseña incorrecto.";
        }
    } else {
        $error = "Usuario incorrecto."; 
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/datatables.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles_log.css" />
    <link rel="stylesheet" href="css/styles.css" />
</head>

<body style="background-image: url('img/fondo.jpg'); background-size: cover; background-position: center;">
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="check-circle-fill" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
        </symbol>
        <symbol id="info-fill" viewBox="0 0 16 16">
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
        </symbol>
        <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
    </svg>
    
    <br>
    <div class="container">
        <div class="accordion" id="accordionPanelsStayOpenExample">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                        Visión Institucional
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        Fomentar y ejecutar políticas, planes y estrategias en materia agrícola que permitan independizar a las comunidades del Municipio Maracaibo en su desarrollo agrícola, pecuario y pesquero sentando las bases que permitan la creación de una Escuela Técnica Agrícola y el mercado de mayoristas del Oeste de Maracaibo.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        Misión institucional
                    </button>
                </h2>
                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Impulsar el desarrollo agroproductivo en Maracaibo mediante la capacitación de productores, con el fin de incrementar la producción, reducir costos para el consumidor, promover el autoabastecimiento y desarrollar cultivos tradicionales y de exportación bajo un modelo sostenible.
                    </div>
                </div>
            </div>
        </div>

        <form method="post" class="login-form">
            <img class="login-from-img" src="img/logo3.jpg" alt="Logo" style="width:140px;height:150px;border-radius:33px; margin-top: 1px;margin-bottom: 1px;">
            <h2>Iniciar sesión</h2>
            <div class="input-group">
                <ion-icon name="person-outline"></ion-icon>
                <input type="text" name="username" placeholder="Usuario" required />
            </div>
            <div class="input-group">
                <ion-icon name="key-outline"></ion-icon>
                <input type="password" name="password" placeholder="Contraseña" required />
            </div>
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Peligro:">
                        <use xlink:href="#exclamation-triangle-fill" />
                    </svg>
                    <div>
                        <?php echo $error; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="login-form-buttons">
                <button type="reset">Limpiar</button>
                <button type="submit">Ingresar</button>
            </div>
        </form>
    </div>
    <br><br>
    <footer>
        <img src="img/Menbrete.jpg" alt="Logo" style="width:600px;height:100px;border-radius:20px; margin-top: 1px;margin-bottom: 1px;">
        <div style="margin-top: 5px;">
            <a target="_blank" href="https://www.instagram.com/ipaupma.mcbo/" class="text-decoration-none">
                Instagram
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                    <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                </svg>
            </a>
            <a target="_blank" href="https://www.facebook.com/profile.php?id=100078270407405&mibextid=rS40aB7S9Ucbxw6v" class="text-decoration-none ms-3">
            Facebook
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook ms-1" viewBox="0 0 16 16">
                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/>
            </svg>
        </a>
        </div>
    </footer>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
    <script>
    // Si llegas al login, reemplaza el historial para que no puedas volver atrás
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
    </body>
</html>