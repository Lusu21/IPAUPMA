<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// 1. Incluimos tu archivo de clase
include 'bd/conexion.php';

// 2. IMPORTANTE: Creamos la variable $pdo llamando a tu función estática
$pdo = conexion::conectar();

// 3. Ahora la consulta ya funcionará porque $pdo tiene valor
try {
    $stmt = $pdo->query("SELECT * FROM parroquias ORDER BY nombre ASC");
    $parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $parroquias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Productor</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        #formCenso input,
        #formCenso select {
            text-transform: uppercase;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        .form-container {
            max-width: 1400px;
            margin: 1rem auto;
            padding: 0 15px;
        }
        .compact-card {
            margin-bottom: 0.8rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-width: 2px;
        }
        .compact-card .card-header {
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }
        .compact-card .card-body {
            padding: 0.8rem;
        }
        .form-label {
            font-size: 0.82rem;
            margin-bottom: 0.2rem;
            font-weight: 500;
        }
        .form-control, .form-select {
            padding: 0.35rem 0.7rem;
            font-size: 0.82rem;
            height: calc(1.4em + 0.7rem);
        }
        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.85rem;
        }
        .section-title {
            font-size: 1.2rem;
            border-left: 4px solid #0d6efd;
            padding-left: 0.8rem;
            margin: 1.5rem 0 1rem;
            color: #2c3e50;
        }
        .table-input { 
            border: none; 
            background: transparent; 
            width: 100%; 
            padding: 0.3rem 0.5rem;
            font-size: 0.8rem;
            line-height: 1.2;
        }
        .table-input:focus { 
            outline: none; 
            background: #f8f9fa; 
        }
        #tablaFamilia input { text-transform: none; }
    </style>
</head>
<body>

    <div class="menu">
        <ion-icon name="menu-outline"></ion-icon>
        <ion-icon name="close-outline"></ion-icon>
    </div>

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
                <li><a href="lista_censos.php"><ion-icon name="person-outline"></ion-icon><span>Lista de Censados</span></a></li>
                <li><a id="añadir-estudiante" href="inscripcion_censo.php"><ion-icon name="person-add-outline"></ion-icon><span>inscripcion Censo</span></a></li>
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

<main class="contenido-principal">
    <div class="form-container">
        <h1 class="section-title">Registro de Censo Agrícola</h1>
        
        <form id="formCenso" method="POST" action="bd/guardar_censo2.php">
            
            <div class="card compact-card border-primary">
                <div class="card-header bg-primary text-white">Datos del Productor</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label required-field">Parroquia</label>
                            <select class="form-select form-select-sm" name="parroquia_id" required>
                                <option value="">SELECCIONAR</option>
                                <?php foreach($parroquias as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required-field">Sector</label>
                            <input type="text" class="form-control form-control-sm" name="sector" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required-field">Nombres</label>
                            <input type="text" class="form-control form-control-sm" name="nombre" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required-field">Apellidos</label>
                            <input type="text" class="form-control form-control-sm" name="apellido" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required-field">Cédula</label>
                            <input type="text" class="form-control form-control-sm" name="cedula" id="cedula" required maxlength="10">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">RIF</label>
                            <input type="text" class="form-control form-control-sm" name="rif">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_nacimiento">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="telefono">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grado Instrucción</label>
                            <select class="form-select form-select-sm" name="grado_instruccion">
                                <option value="Primaria">PRIMARIA</option>
                                <option value="Bachiller">BACHILLER</option>
                                <option value="Técnico Medio">TÉCNICO MEDIO</option>
                                <option value="Universitario">UNIVERSITARIO</option>
                                <option value="Ninguno">NINGUNO</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ocupación u Oficio</label>
                            <input type="text" class="form-control form-control-sm" name="oficio_ocupacion">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card compact-card border-secondary">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center py-1">
                    <span>Carga Familiar</span>
                    <button type="button" class="btn btn-light btn-sm py-0" onclick="agregarFamiliar()">+ Agregar</button>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive"> 
                        <table class="table table-bordered mb-0" id="tablaFamilia">
                            <thead class="table-light">
                                <tr style="font-size: 0.85rem;">
                                    <th>Nombre y Apellido</th>
                                    <th width="80">Edad</th>
                                    <th>C.I. Nº</th>
                                    <th>Parentesco</th>
                                    <th>Ocupación</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card compact-card border-success">
                <div class="card-header bg-success text-white">Identificación del Predio y Servicios</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nombre del Predio</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_predio">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Nº Hectáreas</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="hectareas">
                        </div>
                        
                        <div class="col-md-12">
                            <div class="p-2 border rounded bg-light">
                                <small class="fw-bold d-block mb-2 text-muted">POSEE (Indique cantidad o marque):</small>
                                <div class="row g-2">
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_casa" value="1"> <label>Casa</label></div></div>
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_tanque" value="1"> <label>Tanque</label></div></div>
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_pozos" value="1"> <label>Pozo</label></div></div>
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_corral" value="1"> <label>Corral</label></div></div>
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_perimetral" value="1"> <label>Cercado</label></div></div>
                                    <div class="col-auto"><div class="form-check"><input class="form-check-input" type="checkbox" name="posee_barbacoa" value="1"> <label>Barbacoa</label></div></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="fw-bold text-muted">REGISTROS:</small><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="registro_inti" value="1"> <label>INTI</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="registro_hierro" value="1"> <label>HIERRO</label>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <small class="fw-bold text-muted">SERVICIOS QUE POSEE:</small><br>
                                    <div class="d-flex flex-wrap gap-3">
                                        <label><input type="checkbox" name="servicio_agua" value="1"> Agua</label>
                                        <label><input type="checkbox" name="servicio_gas" value="1"> Gas</label>
                                        <label><input type="checkbox" name="servicio_electricidad" value="1"> Electricidad</label>
                                        <label><input type="checkbox" name="servicio_internet" value="1"> Internet</label>
                                        <label><input type="checkbox" name="servicio_maquinaria" value="1"> Maquinaria</label>
                                        <label><input type="checkbox" name="servicio_transporte" value="1"> Transporte</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card compact-card border-warning">
                <div class="card-header bg-warning text-dark">Actividad Agrícola</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Cultivo</label>
                            <input type="text" class="form-control form-control-sm" name="tipo_cultivo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Área Cultivada</label>
                            <input type="text" class="form-control form-control-sm" name="area_cultivada">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tiempo de Sembrado</label>
                            <input type="text" class="form-control form-control-sm" name="tiempo_sembrado">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Canal Comercialización</label>
                            <input type="text" class="form-control form-control-sm" name="canal_comercializacion">
                        </div>
                        
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Cultivo Principal</label>
                            <input type="text" class="form-control form-control-sm" name="cultivo_principal">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">Cultivo Secundario</label>
                            <input type="text" class="form-control form-control-sm" name="cultivo_secundario">
                        </div>
                        <div class="col-md-4 mt-2">
                            <label class="form-label">¿Dónde vende su producto?</label>
                            <input type="text" class="form-control form-control-sm" name="venta_producto">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card compact-card border-danger">
                <div class="card-header bg-danger text-white">Agrícola Animal (Cantidades)</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <strong class="d-block mb-2 text-danger">BOVINO</strong>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Vacas</span><input type="number" name="cant_vaca" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Toros</span><input type="number" name="cant_toro" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Novillos</span><input type="number" name="cant_novillo" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Becerros</span><input type="number" name="cant_becerros" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Búfalos</span><input type="number" name="cant_bufalo" class="form-control"></div>
                        </div>
                        <div class="col-md-3 border-end">
                            <strong class="d-block mb-2 text-danger">CAPRINO/PORCINO</strong>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Chivos</span><input type="number" name="cant_chivo" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Cabras</span><input type="number" name="cant_cabra" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Ovejos</span><input type="number" name="cant_ovejo" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Cerdos</span><input type="number" name="cant_verraco" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Lechones</span><input type="number" name="cant_lechones" class="form-control"></div>
                        </div>
                        <div class="col-md-3 border-end">
                            <strong class="d-block mb-2 text-danger">AVÍCOLA</strong>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Pollos</span><input type="number" name="cant_pollo_engorde" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Ponedoras</span><input type="number" name="cant_gallinas_ponedoras" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">De Patio</span><input type="number" name="cant_gallinas_patio" class="form-control"></div>
                        </div>
                        <div class="col-md-3">
                            <strong class="d-block mb-2 text-danger">PISCICULTURA</strong>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Alevines</span><input type="number" name="cant_alevines" class="form-control"></div>
                            <div class="input-group input-group-sm mb-1"><span class="input-group-text w-50">Peces</span><input type="number" name="cant_peces" class="form-control"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card compact-card">
                <div class="card-body py-2">
                    <label class="form-label fw-bold small">RECOMENDACIONES / OBSERVACIONES</label>
                    <textarea class="form-control" name="recomendaciones" rows="2"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3 pb-5">
                <button type="reset" class="btn btn-outline-secondary">Limpiar Formulario</button>
                <button type="submit" class="btn btn-primary">Guardar Censo</button>
            </div>
        </form>
    </div>
</main>

    <script src="js/sweetalert2.all.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>

    <script>
        // Función para agregar familiares dinámicamente
        function agregarFamiliar() {
            const table = document.getElementById('tablaFamilia').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            newRow.innerHTML = `
                <td><input type="text" name="fam_nombre[]" class="table-input" placeholder="Nombre completo"></td>
                <td><input type="number" name="fam_edad[]" class="table-input" placeholder="0"></td>
                <td><input type="text" name="fam_cedula[]" class="table-input" placeholder="V-12345678"></td>
                <td><input type="text" name="fam_parentesco[]" class="table-input" placeholder="Ej: HIJO"></td>
                <td><input type="text" name="fam_ocupacion[]" class="table-input" placeholder="ESTUDIANTE"></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm py-0" onclick="eliminarFila(this)">x</button></td>
            `;
        }

        function eliminarFila(btn) {
            btn.closest('tr').remove();
        }

        // Validación para campos numéricos
        ['cedula', 'telefono'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });

        // Logout con confirmación
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, salir'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'bd/logout.php';
                }
            });
        });
    </script>

<?php if (isset($_GET['exito'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: '¡Registrado!',
    text: 'El censo fue registrado correctamente',
    confirmButtonText: 'Aceptar'
});
</script>
<?php elseif (isset($_GET['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'No se pudo registrar el censo',
    confirmButtonText: 'Aceptar'
});
</script>
<?php endif; ?>

</body>
</html>