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
            className: "btn btn-success"
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

// Al hacer clic en editar, cargar datos en el modal
$(document).on('click', '.editar', function() {
    var id = $(this).data('id');
    $.ajax({
        url: 'bd/recibir.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            console.log(data); // Para depuración
            if(data && !data.error){
                $('#ID').val(data.ID);
                $('#Nombres').val(data.Nombres);
                $('#Apellidos').val(data.Apellidos);
                $('#Cedula_Identidad_o_Estudiantil').val(data['Cedula Identidad o Estudiantil']);
                $('#Representante').val(data.Representante);
                $('#Telefono').val(data.Telefono);
                $('#Sexo').val(data.Sexo);
                $('#Lugar_Nacimiento').val(data['Lugar Nacimiento']);
                $('#Entidad_Federal').val(data['Entidad Federal']);
                $('#Etnia').val(data.Etnia);
                $('#Fecha_de_Nacimiento').val(data['Fecha de Nacimiento']);
                $('#Edad').val(data.Edad);
                $('#Correo_Estudiante').val(data['Correo Estudiante']);
                $('#Direccion_Estudiante').val(data['Direccion Estudiante']);
                $('#Posee_Discapacidad').val(data['Posee Discapacidad']);
                $('#Carnet_Discapacidad').val(data['Carnet Discapacidad']);
                $('#Recibe_Apoyo_Especial').val(data['Recibe Apoyo Especial']);
                $('#Recibe_Apoyo_de_Otro_Organismo').val(data['Recibe Apoyo de Otro Organismo']);
                $('#Recibe_Ayuda_Tecnica').val(data['Recibe Ayuda Tecnica']);
                $('#Embarazo').val(data.Embarazo);
                $('#Tiene_Control').val(data['Tiene Control']);
                $('#Medico_Tratante').val(data['Medico Tratante']);
                $('#Centro_Hospitalario').val(data['Centro Hospitalario']);
                $('#Pantalon').val(data.Pantalon);
                $('#Camisa').val(data.Camisa);
                $('#Zapato').val(data.Zapato);
                $('#Año').val(data.Año);
                $('#Seccion').val(data.Seccion);
                $('#Cedula_Representante').val(data['Cedula Representante']);
                $('#Parentesco').val(data.Parentesco);
                $('#Autor_Autorizado').val(data['Autor Autorizado']);
                $('#Correo_Representante').val(data['Correo Representante']);
                $('#Direccion_Representante').val(data['Direccion Representante']);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se encontraron datos del estudiante.'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la petición AJAX: ' + error
            });
        }
    });
});

// Al enviar el formulario, actualizar datos
$('#formEditar').on('submit', function(e){
    e.preventDefault();
    $.ajax({
        url: 'bd/actualizar_estudiante.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(resp){
            console.log(resp); // Para depuración
            if(resp.success){
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: 'Estudiante actualizado correctamente',
                    timer: 1800,
                    showConfirmButton: false
                });
                $('#modalEditar').modal('hide');
                setTimeout(function(){
                    location.reload();
                }, 1800);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar el estudiante'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la petición AJAX: ' + error
            });
        }
    });
});

// Eliminar estudiante con SweetAlert2
$(document).on('click', '.eliminar', function(){
    var id = $(this).data('id');
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'bd/eliminar_estudiante.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(resp){
                    if(resp.success){
                        Swal.fire(
                            '¡Eliminado!',
                            'El estudiante ha sido eliminado.',
                            'success'
                        );
                        setTimeout(function(){
                            location.reload();
                        }, 1200);
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el estudiante',
                            'error'
                        );
                    }
                },
                error: function(){
                    Swal.fire(
                        'Error',
                        'Error en la petición AJAX',
                        'error'
                    );
                }
            });
        }
    });
});