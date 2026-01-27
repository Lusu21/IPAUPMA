<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Verificar si es administrador para ver esta página
if ($_SESSION['rol'] != 'admin') {
    header("Location: index.php?error=acceso-denegado");
    exit();
}

include_once 'bd/conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

// Obtener empleados
$consulta = "SELECT id, nombre_apellido, cedula, cargo, correo, estado FROM empleados";
$resultado = $conexion->prepare($consulta);
$resultado->execute();
$data = $resultado->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Empleados</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="icon" href="icons/logo.ico" />
    <style>
        .section-title {
            font-size: 1.5rem;
            border-left: 4px solid #0d6efd;
            padding-left: 0.8rem;
            margin: 3rem 0 1rem;
            color: #2c3e50;
        }
        .btn-container {
            margin-bottom: 20px;
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
                <span>IPAUPMA</span>
            </div>
        </div>
        <nav class="navegacion">
            <ul>
                <li><a href="admin.php"><ion-icon name="desktop-outline"></ion-icon><span>Panel General</span></a></li>
                <li><a href="usuarios.php"><ion-icon name="people-outline"></ion-icon><span>Usuarios</span></a></li>
                <li><a id="profesores" href="empleados.php"><ion-icon name="id-card-outline"></ion-icon><span>Empleados</span></a></li>
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

    <!-- Contenido principal -->
    <main class="contenido-principal">
        <div class="form-container">
            <h1 class="section-title">Lista de Empleados</h1>
            
            <div class="btn-container">
                <!-- Botón CORREGIDO: ID ÚNICO -->
                <button type="button" class="btn btn-secondary" id="btnNuevoEmpleado" 
                        data-bs-toggle="modal" data-bs-target="#modalNuevoEmpleado">
                    Nuevo Empleado
                </button>
            </div>
            
            <div class="container my-5">
                <div class="row">
                    <table id="tablaEmpleados" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Nombres</th>
                                <th>Cedula</th>
                                <th>Cargo</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $dat): ?>
                                <tr>
                                    <td><?php echo $dat['id']; ?></td>
                                    <td><?php echo htmlspecialchars($dat['nombre_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($dat['cedula']); ?></td>
                                    <td><?php echo htmlspecialchars($dat['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($dat['correo']); ?></td>
                                    <td>
                                        <?php if ($dat['estado'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-primary btn-sm btn-editar-empleado" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarEmpleado"
                                                data-id="<?php echo $dat['id']; ?>">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-empleado" 
                                                data-id="<?php echo $dat['id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($dat['nombre_apellido']); ?>">
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
        </div>
    </main>

    <!-- ========== MODALES CORREGIDOS ========== -->

    <!-- 1. MODAL NUEVO EMPLEADO (ID ÚNICO) -->
    <div class="modal fade" id="modalNuevoEmpleado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formNuevoEmpleado" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre_apellido" required 
                                   maxlength="50" placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cédula *</label>
                            <input type="text" class="form-control" name="cedula" required 
                                   maxlength="11" placeholder="Ej: 10.419.153">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo *</label>
                            <input type="text" class="form-control" name="cargo" required 
                                   maxlength="50" placeholder="Ej: Presidente">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" 
                                   maxlength="30" placeholder="ejemplo@correo.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado *</label>
                            <select class="form-control" name="estado" required>
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. MODAL EDITAR EMPLEADO (ID ÚNICO) -->
    <div class="modal fade" id="modalEditarEmpleado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarEmpleado" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="idEmpleadoEditar">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre_apellido" id="nombreEmpleadoEditar" 
                                   required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cédula *</label>
                            <input type="text" class="form-control" name="cedula" id="cedulaEditar" 
                                   required maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo *</label>
                            <input type="text" class="form-control" name="cargo" id="cargoEditar" 
                                   required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" id="correoEditar" 
                                   maxlength="30">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado *</label>
                            <select class="form-control" name="estado" id="estadoEmpleadoEditar" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== SCRIPTS CORREGIDOS ========== -->
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/datatables.js"></script>
    <script src="js/sweetalert2.all.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    
    <script>
    $(document).ready(function() {
        console.log("=== INICIANDO SISTEMA DE EMPLEADOS ===");
        
        // Verificar que los elementos existen
        console.log("Botón Nuevo Empleado:", $('#btnNuevoEmpleado').length ? "✅ ENCONTRADO" : "❌ NO ENCONTRADO");
        console.log("Modal Nuevo Empleado:", $('#modalNuevoEmpleado').length ? "✅ ENCONTRADO" : "❌ NO ENCONTRADO");
        
        // Inicializar DataTable
        $('#tablaEmpleados').DataTable({
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

        // Guardar nuevo Empleado
        $('#formNuevoEmpleado').on('submit', function(e) {
            e.preventDefault();
            console.log("Enviando formulario de nuevo empleado...");
            
            $.ajax({
                url: 'bd/guardar_empleado.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(resp) {
                    console.log("Respuesta del servidor:", resp);
                    if (resp.success) {
                        $('#modalNuevoEmpleado').modal('hide');
                        Swal.fire({
                            title: '¡Éxito!',
                            text: resp.message || 'Empleado guardado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', resp.message || 'No se pudo guardar el empleado.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en AJAX:", error);
                    Swal.fire('Error', 'Ocurrió un error en el servidor: ' + error, 'error');
                }
            });
        });

        // Cargar datos en el modal de editar empleado
        $(document).on('click', '.btn-editar-empleado', function() {
            var id = $(this).data('id');
            console.log("Cargando datos del empleado ID:", id);
            
            $.ajax({
                url: 'bd/recibir_empleado.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(data) {
                    console.log("Datos recibidos:", data);
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }
                    
                    $('#idEmpleadoEditar').val(data.id);
                    $('#nombreEmpleadoEditar').val(data.nombre_apellido);
                    $('#cedulaEditar').val(data.cedula);
                    $('#cargoEditar').val(data.cargo);
                    $('#correoEditar').val(data.correo);
                    $('#estadoEmpleadoEditar').val(data.estado);
                    
                    // Mostrar el modal
                    $('#modalEditarEmpleado').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error("Error al cargar empleado:", error);
                    Swal.fire('Error', 'Error al cargar los datos del empleado: ' + error, 'error');
                }
            });
        });

        // Guardar cambios al editar empleado
        $('#formEditarEmpleado').on('submit', function(e) {
            e.preventDefault();
            console.log("Enviando formulario de editar empleado...");
            
            $.ajax({
                url: 'bd/editar_empleado.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(resp) {
                    console.log("Respuesta del servidor:", resp);
                    if (resp.success) {
                        $('#modalEditarEmpleado').modal('hide');
                        Swal.fire({
                            title: '¡Éxito!',
                            text: resp.message || 'Empleado actualizado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', resp.message || 'No se pudo actualizar el empleado.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en AJAX:", error);
                    Swal.fire('Error', 'Ocurrió un error en el servidor: ' + error, 'error');
                }
            });
        });

        // Eliminar empleado
        $(document).on('click', '.btn-eliminar-empleado', function() {
            var id = $(this).data('id');
            var nombre = $(this).data('nombre');
            
            Swal.fire({
                title: '¿Eliminar empleado?',
                html: `¿Estás seguro de eliminar a <strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Eliminando empleado ID:", id);
                    
                    $.ajax({
                        url: 'bd/eliminar_empleado.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(resp) {
                            console.log("Respuesta del servidor:", resp);
                            if (resp.success) {
                                Swal.fire('Eliminado!', resp.message || 'Empleado eliminado correctamente', 'success');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                Swal.fire('Error', resp.message || 'No se pudo eliminar el empleado.', 'error');
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
        $('#modalNuevoEmpleado').on('hidden.bs.modal', function() {
            $('#formNuevoEmpleado')[0].reset();
        });
        
        $('#modalEditarEmpleado').on('hidden.bs.modal', function() {
            $('#formEditarEmpleado')[0].reset();
        });
        
        console.log("=== SISTEMA DE EMPLEADOS INICIADO CORRECTAMENTE ===");
    });
    </script>
</body>
</html>