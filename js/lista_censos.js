// Inicialización de DataTable
$('#example').DataTable({
    dom: "<'row'<'col-sm-6 d-flex align-items-center'lB><'col-sm-6 mt-2 mb-3'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-6'i><'col-sm-6 text-end'p>>",
    buttons: [
        {
        extend: "excelHtml5",
        text: '<ion-icon name="receipt-sharp"></ion-icon>',
        titleAttr: "Exportar a Excel",
        className: "btn btn-success",
        
        // 🔥 LA CLAVE: Usar un atributo personalizado y manejar el clic por separado
        init: function(api, node, config) {
            // Quitar el manejador de eventos de DataTables
            $(node).off('click');
            
            // Agregar nuestro propio manejador
            $(node).on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Simplemente descargar el archivo
                window.location.href = 'bd/export_excel.php';
                
                return false;
            });
        }
    },
        {
            extend: "pdfHtml5",
            text: '<ion-icon name="document"></ion-icon>',
            titleAttr: "Exportar a PDF",
            className: "btn btn-danger"
        },
        {
            extend: "print",
            text: '<ion-icon name="print"></ion-icon>',
            titleAttr: "Imprimir",
            className: "btn btn-info"
        }
    ],
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

// Modal full-screen para editar productores
$(document).on('click', '.editar', function(){
    var id = $(this).data('id');
    
    console.log('Editando productor ID:', id);
    
    var modalEl = document.getElementById('modalEditarFull');
    var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl, { keyboard: false });

    $('#modalEditarBody').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Cargando datos del productor...</p>
        </div>
    `);
    
    modal.show();

    //CORREGIDO: Esta es la ruta CORRECTA que vamos a usar
    $.ajax({
        url: 'bd/editar_productor.php', // NUEVO ARCHIVO que vamos a crear
        type: 'GET',
        data: { id: id },
        success: function(html){
            $('#modalEditarBody').html(html);
        },
        error: function(xhr, status, error){
            console.error('Error:', error);
            $('#modalEditarBody').html(`
                <div class="alert alert-danger m-4">
                    <h4>Error al cargar</h4>
                    <p>No se pudo cargar el formulario de edición.</p>
                    <p><strong>Detalles:</strong> ${error} (Status: ${xhr.status})</p>
                    <p class="mt-3">
                        <button class="btn btn-primary" onclick="window.location.reload()">Recargar página</button>
                    </p>
                </div>
            `);
        }
    });
});

//Guardar edición del productor
$(document).on('click', '#btnGuardarEdicion', function(e){
    e.preventDefault();
    
    var frm = $('#formEditarProductor');
    if (!frm.length) {
        Swal.fire('Error', 'Formulario no encontrado', 'error');
        return;
    }

    var btn = $(this);
    btn.prop('disabled', true).text('Guardando...');

    $.ajax({
        url: 'bd/guardar_productor.php', // NUEVO ARCHIVO que vamos a crear
        type: 'POST',
        data: frm.serialize(),
        dataType: 'json',
        success: function(resp){
            if(resp.success){
                // Cerrar modal
                var modalEl = document.getElementById('modalEditarFull');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                
                // Recargar la tabla
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'Datos actualizados correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', resp.message || 'Error al guardar', 'error');
            }
        },
        error: function(xhr){
            Swal.fire('Error', 'Error del servidor: ' + xhr.status, 'error');
        },
        complete: function(){
            btn.prop('disabled', false).text('Guardar Cambios');
        }
    });
});

// Eliminar productor
$(document).on('click', '.eliminar', function(){
    var id = $(this).data('id');
    
    Swal.fire({
        title: '¿Eliminar productor?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'bd/eliminar_productor.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(resp){
                    if(resp.success){
                        Swal.fire('Eliminado!', 'Productor eliminado', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Swal.fire('Error', resp.message, 'error');
                    }
                },
                error: function(){
                    Swal.fire('Error', 'Error en la conexión', 'error');
                }
            });
        }
    });
});