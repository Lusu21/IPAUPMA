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

// Consultar totales dinámicos
$sqlcen = "SELECT COUNT(*) as total FROM productores";
$resultEst = $conn->query($sqlcen);
$totalproductores = $resultEst->fetch_assoc()['total'] ?? 0;

$sqlpar = "SELECT COUNT(*) as total FROM parroquias";
$resultPar = $conn->query($sqlpar);
$totalparroquias = $resultPar->fetch_assoc()['total'] ?? 0;

$sqlusu = "SELECT COUNT(*) as total FROM usuarios";
$resultUsu = $conn->query($sqlusu);
$totalusuarios = $resultUsu->fetch_assoc()['total'] ?? 0;

$sqlemp = "SELECT COUNT(*) as total FROM empleados";
$resultEmp = $conn->query($sqlemp);
$totalempleados = $resultEmp->fetch_assoc()['total'] ?? 0;

$sqlAnimales = "
    SELECT 
        SUM(cant_vaca + cant_toro + cant_novillo + cant_maticas + cant_mautes + 
            cant_becerros + cant_becerras + cant_bufalo + cant_bufala + 
            cant_chivo + cant_cabra + cant_ovejo + cant_oveja + 
            cant_verraco + cant_cerda_madre + cant_levantes + cant_lechones + 
            cant_pollo_engorde + cant_gallinas_ponedoras + cant_gallinas_patio + 
            cant_alevines + cant_peces + cant_reproductores) as total_animales
    FROM ganaderia
";

$resultAnimales = $conn->query($sqlAnimales);
$totalAnimales = $resultAnimales->fetch_assoc()['total_animales'] ?? 0;

// MODIFICACIÓN: Consulta para incluir animales por parroquia
$sql = "SELECT 
    p.id as parroquia_id,
    p.nombre as parroquia,
    COUNT(pr.id) as total_productores,
    (COUNT(pr.id) * 100.0 / (SELECT COUNT(*) FROM productores)) as porcentaje,
    COALESCE(SUM(
        g.cant_vaca + g.cant_toro + g.cant_novillo + g.cant_maticas + g.cant_mautes + 
        g.cant_becerros + g.cant_becerras + g.cant_bufalo + g.cant_bufala + 
        g.cant_chivo + g.cant_cabra + g.cant_ovejo + g.cant_oveja + 
        g.cant_verraco + g.cant_cerda_madre + g.cant_levantes + g.cant_lechones + 
        g.cant_pollo_engorde + g.cant_gallinas_ponedoras + g.cant_gallinas_patio + 
        g.cant_alevines + g.cant_peces + g.cant_reproductores
    ), 0) as total_animales
FROM parroquias p
LEFT JOIN productores pr ON p.id = pr.parroquia_id
LEFT JOIN predios pred ON pr.id = pred.productor_id
LEFT JOIN ganaderia g ON pred.id = g.predio_id
GROUP BY p.id
ORDER BY total_productores DESC";

// Agregar consulta para últimos registros de productores
$sqlUltimos = "SELECT pr.nombre as nombre_productor, p.nombre as parroquia 
               FROM productores pr 
               LEFT JOIN parroquias p ON pr.parroquia_id = p.id 
               ORDER BY pr.id DESC LIMIT 4";
$resultUltimos = $conn->query($sqlUltimos);
$ultimosRegistros = [];
while ($row = $resultUltimos->fetch_assoc()) {
    $ultimosRegistros[] = $row;
}

$result = $conn->query($sql);

// Calcular estadísticas
$totales = [];
$parroquiasConCategoria = [];
while($row = $result->fetch_assoc()) {
    $totales[] = $row['total_productores'];
    
    // Guardar datos temporalmente para calcular percentiles
    $parroquiasConCategoria[] = [
        'nombre' => $row['parroquia'],
        'total_productores' => $row['total_productores'],
        'total_animales' => $row['total_animales'],
        'porcentaje' => $row['porcentaje']
    ];
}

// Calcular percentiles
if (!empty($totales)) {
    sort($totales);
    $percentil70 = $totales[floor(count($totales) * 0.7)] ?? 0;
    $percentil30 = $totales[floor(count($totales) * 0.3)] ?? 0;
} else {
    $percentil70 = 0;
    $percentil30 = 0;
}

// Asignar categorías dinámicamente
foreach ($parroquiasConCategoria as &$parroquia) {
    if ($parroquia['total_productores'] >= $percentil70) {
        $parroquia['categoria'] = 'alta';
    } elseif ($parroquia['total_productores'] >= $percentil30) {
        $parroquia['categoria'] = 'media';
    } else {
        $parroquia['categoria'] = 'baja';
    }
}

// Calcular máximo para barras
$maxTotal = 0;
if (!empty($parroquiasConCategoria)) {
    $maxTotal = max(array_column($parroquiasConCategoria, 'total_productores'));
}

// Calcular promedio
$promedio = $totalproductores > 0 ? $totalproductores / $totalparroquias : 0;

// Preparar datos para Chart.js
$labels = [];
$data = [];
$dataAnimales = []; // Array para datos de animales
$backgroundColors = [];
foreach ($parroquiasConCategoria as $parroquia) {
    $labels[] = $parroquia['nombre'];
    $data[] = $parroquia['total_productores'];
    $dataAnimales[] = $parroquia['total_animales']; // Guardar datos de animales
    if ($parroquia['categoria'] == 'alta') {
        $backgroundColors[] = 'rgba(52, 152, 219, 0.8)'; // Azul sólido para alta
    } elseif ($parroquia['categoria'] == 'media') {
        $backgroundColors[] = 'rgba(46, 204, 113, 0.8)'; // Verde sólido para media
    } else {
        $backgroundColors[] = 'rgba(243, 156, 18, 0.8)'; // Naranja sólido para baja
    }
}
$labelsJson = json_encode($labels);
$dataJson = json_encode($data);
$dataAnimalesJson = json_encode($dataAnimales); // JSON para datos de animales
$backgroundColorsJson = json_encode($backgroundColors);

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
    <link rel="stylesheet" href="css/graficas.css">
    <link rel="icon" href="icons/logo.ico" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
     <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
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
                    <a id="dashboard" href="admin.php">
                        <ion-icon name="desktop-outline"></ion-icon>
                        <span>Panel General</span>
                    </a>
                </li>
                <li>
                    <a href="usuarios.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li>
                    <a href="empleados.php">
                        <ion-icon name="id-card-outline"></ion-icon>
                        <span>Empleados</span>
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
                    <a href="vacunas.php">
                        <ion-icon name="medkit-outline"></ion-icon>
                        <span>Vacunas</span>
                    </a>
                </li>
                <li>
                    <a href="bd/logout.php" id="logoutLink">
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
         <!-- Cards alineados en el dashboard -->
        <div class="row">
        <!-- Card  Productores -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Productores
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalproductores ?></div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <ion-icon name="man-outline" style="font-size: 3.0rem; color: #198754;"></ion-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
              <!-- Card  Parroquias -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Parroquias
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalparroquias ?></div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <ion-icon name="business-outline" style="font-size: 3rem; color: #0d6efd;"></ion-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <!-- Card  Usuarios -->
                  <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Usuarios
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalusuarios ?></div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <ion-icon name="person-outline" style="font-size: 3rem; color: #20c9e6;"></ion-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- card Empleados -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Empleados
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalempleados ?></div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <ion-icon name="id-card-outline" style="font-size: 3rem; color: #ffc107;"></ion-icon>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Productores por Parroquia -->
        <div class="dashboard-container">
            <!-- Encabezado -->
            <div class="header">
                <h1>
                    <ion-icon name="podium-outline"></ion-icon>
                    Productores por Parroquia
                </h1>
            </div>
            
            <!-- Estadísticas rápidas -->
            <div class="stats-summary">
                <div class="stat-box">
                     <div class="stat-number"><?php echo number_format($totalAnimales); ?></div>
                    <div class="stat-label">Total Animales</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $maxTotal; ?></div>
                    <div class="stat-label">Máximo (<?php echo $parroquiasConCategoria[0]['nombre']; ?>)</div>
                </div>
            </div>
            
            <!-- Área del gráfico -->
            <div class="chart-area">
                <h2 class="chart-title">
                    <i class="fas fa-chart-line"></i>
                    Distribución de Productores por Parroquia
                </h2>
                <canvas id="chartProductores" style="max-width: 100%; max-height: 400px;"></canvas> <!-- Canvas para Chart.js -->
                
                <!-- Leyenda -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(90deg, #3498db, #2980b9);"></div>
                        <span>Alta densidad (≥<?php echo $percentil70; ?> productores)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(90deg, #2ecc71, #27ae60);"></div>
                        <span>Media densidad (<?php echo $percentil30; ?>-<?php echo $percentil70; ?> productores)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: linear-gradient(90deg, #f39c12, #e67e22);"></div>
                        <span>Baja densidad (<<?php echo $percentil30; ?> productores)</span>
                    </div>
                </div>
                <!-- Últimos Registros -->
                <div class="ultimos-registros" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-family: monospace; font-size: 0.85rem;">
                    <strong>ÚLTIMOS REGISTROS</strong><br>
                    <br>
                    <?php foreach ($ultimosRegistros as $registro): ?>
                        • <?php echo htmlspecialchars(substr($registro['nombre_productor'], 0, 15)); ?> - <?php echo htmlspecialchars(substr($registro['parroquia'], 0, 30)); ?><br>
                    <?php endforeach; ?>
            </div>
        </div>   
    </main>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
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

        // Gráfico de barras verticales con Chart.js
        const ctx = document.getElementById('chartProductores').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar', // Barras verticales
            data: {
                labels: <?php echo $labelsJson; ?>,
                datasets: [{
                    label: 'Cantidad de Productores',
                    data: <?php echo $dataJson; ?>,
                    backgroundColor: <?php echo $backgroundColorsJson; ?>,
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                aspectRatio: 2, // Corregido: era "AspectRatio" (mayúscula incorrecta)
                plugins: {
                    legend: {
                        display: false // Ocultar leyenda interna, usamos la externa
                    },
                    tooltip: {
                        callbacks: {
                            // MODIFICACIÓN: Mostrar productores y animales en el tooltip
                            label: function(context) {
                                const parroquiaIndex = context.dataIndex;
                                const productores = <?php echo $dataJson; ?>[parroquiaIndex];
                                const animales = <?php echo $dataAnimalesJson; ?>[parroquiaIndex];
                                
                                return [
                                    `Productores: ${productores}`,
                                    `Animales: ${animales}`
                                ];
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end', // Posición encima de la barra
                        align: 'end', // Alineación
                        formatter: (value) => value, // Mostrar el valor numérico
                        color: '#000', // Color del texto
                        font: {
                            size: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            display: false // Ocultar los números del eje Y
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45, // Rotar etiquetas si son largas
                            minRotation: 0
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>