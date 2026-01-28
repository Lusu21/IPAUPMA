<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Vacunas</title>
    <!-- Estilos CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/datatables.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <span>IPAUPMA</span> 
            </div>
        </div>
        <nav class="navegacion">
            <ul>
                <li>
                    <a href="admin.php">
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
                    <a id="dashboard" href="vacunas.php">
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

    <!-- Contenido principal -->
    <main class="contenido-principal">
        <div class="form-container">
            <h1 class="section-title">Inventario de Vacunas</h1>
            
            <!-- Botón Agregar -->
            <div class="btn-container">
                    <button id="btnNuevo" type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalVacuna">
                     Recibir Lote (Nuevo)
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table id="tablaVacunas" class="table table-striped table-bordered" style="width:100%">
                            <thead class="text-center">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Vacuna</th>
                                    <th>Laboratorio</th>
                                    <th>Lote</th>
                                    <th>Vencimiento</th>
                                    <th>Stock Actual</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Llenado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para Crear/Editar -->
    <div class="modal fade" id="modalVacuna" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">Nueva Vacuna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formVacunas">
                    <div class="modal-body">
                        <input type="hidden" id="id" name="id">
                        <input type="hidden" name="opcion" value="2">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Vacuna</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej: Aftosa, Rabia..." maxlength="60">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Laboratorio</label>
                            <input type="text" class="form-control" id="laboratorio" name="laboratorio" required maxlength="60">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lote</label>
                                <input type="text" class="form-control" id="lote" name="lote" required maxlength="30">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">F. Vencimiento</label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad Inicial (Dosis)</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" required min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/datatables.js"></script>
    <script src="js/script.js"></script> <!-- Tu script general de menú -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Iconos -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script>
    $(document).ready(function(){
        let tablaVacunas = $('#tablaVacunas').DataTable({
             "destroy": true,
            "ajax": {
                "url": "bd/crud_vacunas.php",
                "method": 'POST',
                "data": function(d) {
        // Esto envía un número aleatorio cada vez, obligando a actualizar
        d.opcion = 1;
        d.random = Math.random(); 
    },
    "cache": false, // Desactivar caché de DataTables
    "dataSrc": ""
            },
            "columns": [
                {"data": "id"},
                {"data": "nombre"},
                {"data": "laboratorio"},
                {"data": "lote"},
                {
                    "data": "fecha_vencimiento",
                    "render": function(data) {
                        const fecha = new Date(data);
                        const hoy = new Date();
                        // Aviso visual si vence pronto o ya venció
                        if(fecha < hoy) {
                             return `<span class="badge bg-danger">${data} (Vencida)</span>`;
                        } else {
                             return data;
                        }
                    }
                },
                {
                    "data": "cantidad", 
                    "render": function(data) {
                        // Aviso visual si hay poco stock
                        if(data <= 0) return `<span class="badge bg-secondary">Agotado</span>`;
                        if(data < 20) return `<span class="badge bg-warning text-dark">${data} (Bajo)</span>`;
                        return `<span class="badge bg-success" style="font-size:1em">${data}</span>`;
                    }
                },
                {
                    "defaultContent": `
                    <div class='text-center'>
                        <div class='btn-group'>
                            <button class='btn btn-warning btn-sm btnConsumo' title="Registrar Uso/Vacunación">
                                <ion-icon name="remove-circle-outline" style="font-size:1.2em;"></ion-icon>
                            </button>
                            <button class='btn btn-info btn-sm btnEditar' title="Editar Datos">
                                <ion-icon name='create-outline'></ion-icon>
                            </button>
                            <button class='btn btn-danger btn-sm btnBorrar' title="Eliminar Lote">
                                <ion-icon name='trash-outline'></ion-icon>
                            </button>
                        </div>
                    </div>`
                }
            ],
            // AQUÍ ESTÁ EL CAMBIO: TRADUCCIÓN INCRUSTADA
            "language": {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "search": "Buscar:",
                "infoThousands": ",",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });

        // --- 1. GUARDAR O EDITAR ---
        $('#formVacunas').submit(function(e){
            e.preventDefault();
            const formData = new FormData(this);
            // Si no tiene opción, es porque estamos guardando/editando
            if(!formData.get('opcion')) formData.append('opcion', 2);

            $.ajax({
                url: "bd/crud_vacunas.php",
                type: "POST",
                datatype: "json",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#modalVacuna').modal('hide');
                    tablaVacunas.ajax.reload(null, false);
                    Swal.fire('¡Éxito!', 'Operación realizada correctamente', 'success');
                }
            });
        });

        // --- 2. PREPARAR MODAL PARA NUEVO ---
        $("#btnNuevo").click(function(){
            $("#formVacunas").trigger("reset");
            $("#id").val("");
            $("#tituloModal").text("Recibir Nuevo Lote");
        });

        // --- 3. BOTÓN EDITAR (Cargar datos al modal) ---
        $(document).on("click", ".btnEditar", function(){
            let fila = $(this).closest("tr");
            let id = parseInt(fila.find('td:eq(0)').text());
            let nombre = fila.find('td:eq(1)').text();
            let laboratorio = fila.find('td:eq(2)').text();
            let lote = fila.find('td:eq(3)').text();
            
            // Limpiar badge si existe en fecha
            let fechaTexto = fila.find('td:eq(4)').text().split(' ')[0]; 
            // Limpiar badge si existe en cantidad
            let cantidadTexto = fila.find('td:eq(5)').text().split(' ')[0]; 
            // Si dice Agotado, es 0
            if(cantidadTexto === "Agotado") cantidadTexto = 0;

            $("#id").val(id);
            $("#nombre").val(nombre);
            $("#laboratorio").val(laboratorio);
            $("#lote").val(lote);
            $("#fecha_vencimiento").val(fechaTexto);
            $("#cantidad").val(parseInt(cantidadTexto));
            
            $("#tituloModal").text("Editar Datos del Lote");
            $('#modalVacuna').modal('show');
        });

        // --- 4. BOTÓN BORRAR (Lógico) ---
        $(document).on("click", ".btnBorrar", function(){
            let fila = $(this).closest("tr");
            let id = parseInt(fila.find('td:eq(0)').text());
            
            Swal.fire({
                title: '¿Eliminar lote?',
                text: "Esta acción quitará el lote del inventario visible.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "bd/crud_vacunas.php",
                        type: "POST",
                        datatype:"json",
                        data:  {opcion:3, id:id},
                        success: function() {
                            tablaVacunas.ajax.reload(null, false);
                            Swal.fire('Eliminado', 'El registro ha sido eliminado.', 'success');
                        }
                    });
                }
            });
        });

        // --- 5. BOTÓN REGISTRAR CONSUMO (Descontar día a día) ---
        $(document).on("click", ".btnConsumo", async function(){
            let fila = $(this).closest("tr");
            let id = parseInt(fila.find('td:eq(0)').text());
            let nombre = fila.find('td:eq(1)').text();
            
            // Obtener stock limpio (sin badges HTML)
            let celdaStock = fila.find('td:eq(5)');
            let stockTexto = celdaStock.text().split(' ')[0]; 
            if(stockTexto === "Agotado") stockTexto = "0";
            let stock_actual = parseInt(stockTexto);

            if(stock_actual <= 0) {
                Swal.fire('Agotado', 'Este lote ya no tiene vacunas disponibles.', 'error');
                return;
            }

            // Pop-up para pedir cantidad
            const { value: cantidad } = await Swal.fire({
                title: 'Registrar Vacunación',
                html: `
                    <p>Descontar dosis del lote: <b>${nombre}</b></p>
                    <p>Stock disponible: <b>${stock_actual}</b></p>
                    <label>Cantidad utilizada hoy:</label>
                `,
                input: 'number',
                inputAttributes: {
                    min: 1,
                    max: stock_actual,
                    step: 1
                },
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                confirmButtonText: 'Descontar Stock',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || value <= 0) {
                        return 'Debes escribir una cantidad válida';
                    }
                    if (parseInt(value) > stock_actual) {
                        return '¡No tienes tantas vacunas en este lote!';
                    }
                }
            });

            if (cantidad) {
                $.ajax({
                    url: "bd/crud_vacunas.php",
                    type: "POST",
                    datatype: "json",
                    data: {
                        opcion: 4, 
                        id: id, 
                        cantidad_usada: cantidad
                    },
                    success: function(response) {
                        let resp = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if(resp.success){
                            tablaVacunas.ajax.reload(null, false);
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            Toast.fire({
                                icon: 'success',
                                title: `Se descontaron ${cantidad} dosis.`
                            });
                        } else {
                            Swal.fire('Error', resp.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                    }
                });
            }
        });
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

    });
    </script>
</body>
</html>
