<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include_once 'bd/conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

// Obtener productores (compatible PDO o mysqli)
$data = [];
try {
    $sqlProd = "SELECT p.id, p.nombre, p.apellido, p.cedula, p.sector, 
                       par.id as parroquia_id, par.nombre as parroquia_nombre 
                FROM productores p 
                LEFT JOIN parroquias par ON p.parroquia_id = par.id";
    if ($conexion instanceof PDO) {
        $stmt = $conexion->prepare($sqlProd);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($conexion instanceof mysqli) {
        $res = $conexion->query($sqlProd);
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        // fallback para clases custom que implementen ->query()
        if (method_exists($conexion, 'query')) {
            $res = $conexion->query($sqlProd);
            if (is_object($res) && method_exists($res, 'fetch_all')) {
                $data = $res->fetch_all(MYSQLI_ASSOC);
            } elseif (is_array($res)) {
                $data = $res;
            }
        }
    }
} catch (Exception $e) {
    $data = [];
}

// Obtener parroquias para el filtro (id, nombre)
$parroquias = [];
try {
    $sqlPar = "SELECT id, nombre FROM parroquias ORDER BY nombre";
    if ($conexion instanceof PDO) {
        $stmt = $conexion->prepare($sqlPar);
        $stmt->execute();
        $parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($conexion instanceof mysqli) {
        $res = $conexion->query($sqlPar);
        $parroquias = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        if (method_exists($conexion, 'query')) {
            $res = $conexion->query($sqlPar);
            if (is_object($res) && method_exists($res, 'fetch_all')) {
                $parroquias = $res->fetch_all(MYSQLI_ASSOC);
            } elseif (is_array($res)) {
                $parroquias = $res;
            }
        }
    }
} catch (Exception $e) {
    $parroquias = [];
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Censados</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/datatables.css" rel="stylesheet">
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
                <div class="nombre-textos">
                    <span class="brand">IPAUPMA</span>
                    <span class="tagline"></span>
                </div>
            </div>
        </div>

        <nav class="navegacion">
            <ul>
                <li><a href="dashboard.php"><ion-icon name="desktop-outline"></ion-icon><span>Dashboard</span></a></li>
                <li><a href="usuarios.php"><ion-icon name="people-outline"></ion-icon><span>Usuarios</span></a></li>
                <li><a id="lista-estudiantes" href="lista-estudiantes.php"><ion-icon name="person-outline"></ion-icon><span>Lista de Censados</span></a></li>
                <li><a id="añadir_estudiante" href="inscripcion_censo.php"><ion-icon name="person-add-outline"></ion-icon><span>inscripcion censo</span></a></li>
                <li><a href="bd/logout.php" id="logoutLink"><ion-icon name="log-out-outline"></ion-icon><span>Cerrar Sesión</span></a></li>
            </ul>
        </nav>

        <div>
            <div class="linea"></div>
            <div class="modo-oscuro">
                <div class="info"><ion-icon name="moon-outline"></ion-icon><span>Modo Oscuro</span></div>
                <div class="switch"><div class="base"><div class="circulo"></div></div></div>
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
    

    <!-- Contenido principal -->
    <main class="contenido-principal">
        <div class="container my-4">

            <!-- Contenedor para el filtro de parroquias -->
            <div id="filterParroquiaContainer" style="margin-bottom:8px; display:flex; gap:8px; align-items:center;">
                <label for="filterParroquia" style="margin:0; font-weight:500;">Parroquia:</label>
                <select id="filterParroquia" class="form-select" style="width:240px;">
                    <option value="">Todas</option>
                    <?php foreach ($parroquias as $p): ?>
                        <option value="<?php echo htmlspecialchars($p['nombre']); ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cédula</th>
                            <th>Sector</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $dat): ?>
                        <tr data-parroquia="<?php echo htmlspecialchars($dat['parroquia_nombre'] ?? ''); ?>">
                            <td><?php echo htmlspecialchars($dat['id']); ?></td>
                            <td><?php echo htmlspecialchars($dat['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($dat['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($dat['cedula']); ?></td>
                            <td><?php echo htmlspecialchars($dat['sector']); ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-warning btn-sm editar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditar"
                                        data-id="<?php echo htmlspecialchars($dat['id']); ?>">
                                        <ion-icon name="create-outline"></ion-icon>
                                    </button>
                                    <a class="btn btn-info btn-sm info" target="_blank" href="bd/info_estudiante.php?id=<?php echo urlencode($dat['id']); ?>">
                                        <ion-icon name="information-circle-outline"></ion-icon>
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm eliminar" data-id="<?php echo htmlspecialchars($dat['id']); ?>">
                                        <ion-icon name="trash-outline"></ion-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- Modal (mantener tu modal actual) -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="formEditar" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarLabel">Editar Estudiante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body row">
                        <input type="hidden" id="ID" name="ID">
                        <!-- campos: conservar los inputs que ya tienes -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts: jQuery -> DataTables -> lista_censos.js -> resto -->
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/datatables.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
    <script src="js/lista_censos.js"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@latest/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>

    <script>
    // Este bloque solo adjunta el filtro a la tabla ya inicializada en js/lista_censos.js.
    (function () {
        // asegurar que DataTable esté disponible e inicializada por lista_censos.js
        if (typeof $.fn.DataTable === 'undefined') return;

        var table;
        try {
            table = $('#example').DataTable();
        } catch (err) {
            // si todavía no está inicializada, intentar nuevamente tras breve retraso
            setTimeout(function () {
                try { table = $('#example').DataTable(); attachFilter(table); } catch(e){ /* fallar silencioso */ }
            }, 200);
            return;
        }

        attachFilter(table);

        function attachFilter(tableInstance) {
            if (!tableInstance) return;

            // mover el select junto al control "Mostrar" (length)
            var lengthNode = $('#example_length');
            if ($('#filterParroquiaContainer').length && lengthNode.length) {
                $('#filterParroquiaContainer').insertBefore(lengthNode);
            }

            // evitar múltiples binding
            $('#filterParroquia').off('change.filterPar').on('change.filterPar', function () {
                var val = $(this).val();
                
                // Usar la función de búsqueda personalizada de DataTables
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        if (!val) return true; // Mostrar todos si no hay filtro
                        
                        var row = tableInstance.row(dataIndex).node();
                        var parroquia = $(row).data('parroquia');
                        return parroquia === val;
                    }
                );
                
                tableInstance.draw();
                
                // Limpiar la función de búsqueda para no acumular múltiples filtros
                $.fn.dataTable.ext.search.pop();
            });
        }

    })();

    // Confirmación logout
    (function(){
        var logout = document.getElementById('logoutLink');
        if (!logout) return;
        logout.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas cerrar sesión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = 'bd/logout.php';
            });
        });
    })();
    </script>
</body>
</html>