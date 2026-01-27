<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php?error=acceso-denegado");
    exit();
}

// 2. Validar rol de administrador (desde la base de datos)
require_once 'bd/admin_conex.php';
$stmt = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || $result->fetch_assoc()['rol'] !== 'admin') {
    error_log("Intento de acceso no autorizado: User ID {$_SESSION['user_id']}");
    header("Location: index.php?error=acceso-denegado");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Conexión a la base de datos
include_once 'bd/conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

// Obtener usuarios
$consulta = "SELECT u.id, u.username, u.rol, u.estado FROM usuarios u";
$resultado = $conexion->prepare($consulta);
$resultado->execute();
$data = $resultado->fetchAll(PDO::FETCH_ASSOC);

// Obtener empleados para el select (solo los que no tienen usuario)
// CORREGIDO: Usar nombre_apellido directamente
$stmt_empleados = $conexion->query("SELECT id, nombre_apellido FROM empleados WHERE nombre_apellido NOT IN (SELECT username FROM usuarios)");
$empleados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

$resultado->closeCursor();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/datatables.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="icons/logo.ico" />
    <style>
        .section-title {
            font-size: 1.5rem;
            border-left: 4px solid #0d6efd;
            padding-left: 0.8rem;
            margin: 3rem 0 1rem;
            color: #2c3e50;
        }

        .table {
            counter-reset: num;
        }

        .table tr {
            counter-increment: row-num;
        }

        .table tr td:first-child::before {
            content: counter(row-num);
        }
        
        .btn-container {
            margin-bottom: 20px;
        }
        
        .small-text {
            font-size: 0.85rem;
            color: #666;
        }
    </style>
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
                <li><a href="admin.php"><ion-icon name="desktop-outline"></ion-icon><span>Panel General</span></a></li>
                <li><a id="usuarios" href="usuarios.php"><ion-icon name="people-outline"></ion-icon><span>Usuarios</span></a></li>
                <li>
                    <a href="empleados.php">
                        <ion-icon name="id-card-outline"></ion-icon>
                        <span>Empleados</span>
                    </a>
                </li>
                <li><a href="lista_censos.php"><ion-icon name="person-outline"></ion-icon><span>Lista de Censados</span></a></li>
                <li><a href="inscripcion_censo.php"><ion-icon name="person-add-outline"></ion-icon><span>Inscripcion Censo</span></a></li>
                <li>
                    <a href="vacunas.php">
                        <ion-icon name="medkit-outline"></ion-icon>
                        <span>Vacunas</span>
                    </a>
                </li>
                <li><a href="bd/logout.php" id="logoutLink"><ion-icon name="log-out-outline"></ion-icon><span>Cerrar Sesión</span></a></li>
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

    <main class="contenido-principal">
        <div class="form-container">
            <h1 class="section-title">Lista de Usuarios</h1>
            
            <div class="btn-container">
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAgregarUsuario">
                    Agregar Usuario
                </button>
            </div>
            
            <div class="container my-5">
                <div class="row">
                    <table id="tablaUsuarios" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $dat): ?>
                                <tr>
                                    <td></td>
                                    <td><?= htmlspecialchars($dat['username']) ?></td>
                                    <td><?= htmlspecialchars($dat['rol']) ?></td>
                                    <td>
                                        <?php if ($dat['estado'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-editar-usuario" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarUsuario"
                                                data-id="<?= $dat['id'] ?>"
                                                data-username="<?= htmlspecialchars($dat['username']) ?>"
                                                data-rol="<?= htmlspecialchars($dat['rol']) ?>"
                                                data-estado="<?= $dat['estado'] ?>">
                                            <ion-icon name="create-outline"></ion-icon>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-eliminar-usuario" 
                                                data-id="<?= $dat['id'] ?>"
                                                data-username="<?= htmlspecialchars($dat['username']) ?>">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL AGREGAR USUARIO -->
    <div class="modal fade" id="modalAgregarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formAgregarUsuario" method="POST" action="bd/agregar_usuario.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Empleado <span class="small-text">(Opcional)</span></label>
                            <select id="select-empleado" name="username_or_new" class="form-control" onchange="toggleUsernameInput()">
                                <option value="" selected>Crear nuevo usuario sin empleado</option>
                                <?php if (!empty($empleados)): ?>
                                    <?php foreach ($empleados as $empleado): ?>
                                        <option value="<?= htmlspecialchars($empleado['nombre_apellido']) ?>">
                                            <?= htmlspecialchars($empleado['nombre_apellido']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay empleados disponibles</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text small-text">Selecciona un empleado para usar su nombre como usuario, o crea uno nuevo.</div>
                        </div>

                        <div class="mb-3" id="input-username-container" style="display: block;">
                            <label class="form-label">Nombre de Usuario *</label>
                            <input type="text" name="nuevo_username" class="form-control" id="input-username" 
                                   placeholder="Ingrese el nombre de usuario" maxlength="30" required>
                            <div class="form-text small-text">Este será el nombre para iniciar sesión.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="Mínimo 8 caracteres" minlength="8" maxlength="30">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" class="form-control" required>
                                <option value="admin">Administrador</option>
                                <option value="usuario" selected>Usuario</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado *</label>
                            <select name="estado" class="form-control" required>
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO -->
    <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarUsuario" method="POST" action="bd/editar_usuario.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="idUsuarioEditar">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario</label>
                            <input type="text" name="username" class="form-control" id="usernameEditar" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña <span class="small-text">(Opcional)</span></label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Dejar en blanco para mantener la actual">
                            <div class="form-text small-text">Complete solo si desea cambiar la contraseña.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" class="form-control" id="rolEditar" required>
                                <option value="admin">Administrador</option>
                                <option value="usuario">Usuario</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado *</label>
                            <select name="estado" class="form-control" id="estadoUsuarioEditar" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== SCRIPTS ========== -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/datatables.js"></script>
    <script src="js/script.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>

    <script>
    $(document).ready(function() {
        console.log("=== INICIANDO SISTEMA DE USUARIOS ===");
        
        // Verificar que los elementos existen
        //console.log("Botón Agregar Usuario:", $('[data-bs-target="#modalAgregarUsuario"]').length ? "✅ ENCONTRADO" : "❌ NO ENCONTRADO");
        //console.log("Modal Agregar Usuario:", $('#modalAgregarUsuario').length ? "✅ ENCONTRADO" : "❌ NO ENCONTRADO");
        
        // Inicializar DataTable
        $('#tablaUsuarios').DataTable({
            lengthMenu: [5, 10, 20, 50, 100],
            language: {
                search: "Buscar:",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                lengthMenu: "Mostrar _MENU_",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });

        // Función para mostrar/ocultar el campo de nuevo nombre de usuario
            function toggleUsernameInput() {
            const selectEmpleado = document.getElementById('select-empleado');
            const inputUsernameContainer = document.getElementById('input-username-container');
            const inputUsername = document.getElementById('input-username');

            if (selectEmpleado.value === "") {
                // Opción vacía seleccionada - mostrar campo para escribir
                inputUsernameContainer.style.display = "block";
                inputUsername.setAttribute('required', 'required');
                inputUsername.value = '';
                inputUsername.placeholder = "Ingrese el nombre de usuario";
            } else {
                // Empleado seleccionado - ocultar campo y usar su nombre
                inputUsernameContainer.style.display = "none";
                inputUsername.removeAttribute('required');
                inputUsername.value = selectEmpleado.value;
            }
        }

        // Inicializar la función
        if (document.getElementById('select-empleado')) {
            toggleUsernameInput();
            document.getElementById('select-empleado').addEventListener('change', toggleUsernameInput);
        }

        // Llenar datos en el modal de editar usuario
        $(document).on('click', '.btn-editar-usuario', function() {
            const id = $(this).data('id');
            const username = $(this).data('username');
            const rol = $(this).data('rol');
            const estado = $(this).data('estado');
            
            console.log("Cargando datos del usuario:", {id, username, rol, estado});
            
            $('#idUsuarioEditar').val(id);
            $('#usernameEditar').val(username);
            $('#rolEditar').val(rol);
            $('#estadoUsuarioEditar').val(estado);
        });

        // Manejar envío del formulario de agregar usuario
        $('#formAgregarUsuario').on('submit', function(e) {
            const selectEmpleado = document.getElementById('select-empleado');
            const inputUsername = document.getElementById('input-username');
            
            // Si se seleccionó un empleado, usar ese valor como username
            if (selectEmpleado.value && selectEmpleado.value !== "" && selectEmpleado.value !== "crear_nuevo") {
                inputUsername.value = selectEmpleado.value;
            }
            
            // Validar que el username no esté vacío
            if (!inputUsername.value.trim()) {
                e.preventDefault();
                Swal.fire('Error', 'El nombre de usuario es requerido', 'error');
                return false;
            }
            
            console.log("Enviando formulario con username:", inputUsername.value);
            return true;
        });

        // Eliminar usuario
        $(document).on('click', '.btn-eliminar-usuario', function() {
            const id = $(this).data('id');
            const username = $(this).data('username');
            
            Swal.fire({
                title: '¿Eliminar usuario?',
                html: `¿Estás seguro de eliminar al usuario <strong>${username}</strong>?<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Eliminando usuario ID:", id);
                    
                    $.ajax({
                        url: 'bd/eliminar_usuario.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(resp) {
                            console.log("Respuesta del servidor:", resp);
                            if (resp.success) {
                                Swal.fire('Eliminado!', resp.message || 'Usuario eliminado correctamente', 'success');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                Swal.fire('Error', resp.message || 'No se pudo eliminar el usuario.', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error en AJAX:", error);
                            Swal.fire('Error', 'Error en la conexión: ' + error, 'error');
                        }
                    });
                }
            });
        });

        // Alerta de cierre de sesión
        $('#logoutLink').on('click', function(e) {
            e.preventDefault();
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
                    popup: 'animate__animated animate__bounceInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'bd/logout.php';
                }
            });
        });

        // Limpiar campos al cerrar modales
        $('#modalAgregarUsuario').on('hidden.bs.modal', function() {
            $('#formAgregarUsuario')[0].reset();
            toggleUsernameInput();
        });
        
        $('#modalEditarUsuario').on('hidden.bs.modal', function() {
            $('#formEditarUsuario')[0].reset();
        });
        
        console.log("=== SISTEMA DE USUARIOS INICIADO CORRECTAMENTE ===");
    });
    </script>

    <?php if (isset($_GET['exito'])): ?>
        <script>
            Swal.fire("Éxito", "Usuario <?= $_GET['exito'] ?> correctamente", "success").then(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('exito');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'usuario_existe'): ?>
        <script>
            Swal.fire("Error", "Ese usuario ya existe", "error").then(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'no_usuario_valido'): ?>
        <script>
            Swal.fire("Error", "No se proporcionó un nombre de usuario válido.", "error").then(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'password_corta'): ?>
        <script>
            Swal.fire("Error", "La contraseña debe tener al menos 6 caracteres.", "error").then(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'bd_fallo'): ?>
        <script>
            Swal.fire("Error", "Hubo un fallo al interactuar con la base de datos. Intente nuevamente.", "error").then(() => {
                const url = new URL(window.location.href);
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            });
        </script>
    <?php endif; ?>

</html>