<?php
include_once 'conexion.php';
$objeto = new conexion();
$conexion = $objeto->conectar();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID no proporcionado";
    exit;
}

// Consulta principal para obtener datos del productor
$consulta = "SELECT 
    p.*,
    pr.nombre as parroquia_nombre
    FROM productores p
    LEFT JOIN parroquias pr ON p.parroquia_id = pr.id
    WHERE p.id = ?";
    
$stmt = $conexion->prepare($consulta);
$stmt->execute([$id]);
$productor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$productor) {
    echo "Productor no encontrado";
    exit;
}

// Obtener datos del predio
$consulta_predio = "SELECT * FROM predios WHERE productor_id = ?";
$stmt_predio = $conexion->prepare($consulta_predio);
$stmt_predio->execute([$id]);
$predio = $stmt_predio->fetch(PDO::FETCH_ASSOC);

// Obtener datos de agricultura si existe predio
$agricultura = [];
$ganaderia = [];
$recomendaciones = [];
$familia = [];

if ($predio) {
    // Agricultura
    $consulta_agricultura = "SELECT * FROM agricultura WHERE predio_id = ?";
    $stmt_agricultura = $conexion->prepare($consulta_agricultura);
    $stmt_agricultura->execute([$predio['id']]);
    $agricultura = $stmt_agricultura->fetch(PDO::FETCH_ASSOC);
    
    // Ganadería
    $consulta_ganaderia = "SELECT * FROM ganaderia WHERE predio_id = ?";
    $stmt_ganaderia = $conexion->prepare($consulta_ganaderia);
    $stmt_ganaderia->execute([$predio['id']]);
    $ganaderia = $stmt_ganaderia->fetch(PDO::FETCH_ASSOC);
    
    // Recomendaciones
    $consulta_recomendaciones = "SELECT * FROM recomendaciones WHERE predio_id = ?";
    $stmt_recomendaciones = $conexion->prepare($consulta_recomendaciones);
$stmt_recomendaciones->execute([$predio['id']]);
    $recomendaciones = $stmt_recomendaciones->fetch(PDO::FETCH_ASSOC);
}

// Obtener carga familiar
$consulta_familia = "SELECT * FROM carga_familiar WHERE productor_id = ?";
$stmt_familia = $conexion->prepare($consulta_familia);
$stmt_familia->execute([$id]);
$familia = $stmt_familia->fetchAll(PDO::FETCH_ASSOC);

// Función para formatear valores booleanos
function formatBoolean($value) {
    return $value ? 'Sí' : 'No';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Productor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .info-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 200px;
        }
        .info-value {
            color: #212529;
        }
        .section-title {
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
            margin-top: 20px;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        @media print {
            .btn {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <h2 class="mb-4">Información del Productor</h2>

    <!-- DATOS DEL PRODUCTOR -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Datos Personales del Productor</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">ID:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['id'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nombres:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['nombre'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Apellidos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['apellido'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cédula:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['cedula'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">RIF:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['rif'] ?? ''); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Parroquia:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['parroquia_nombre'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Sector:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['sector'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['telefono'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha Nacimiento:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['fecha_nacimiento'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Grado Instrucción:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['grado_instruccion'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ocupación/Oficio:</span>
                        <span class="info-value"><?php echo htmlspecialchars($productor['oficio_ocupacion'] ?? ''); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARGA FAMILIAR -->
    <?php if (!empty($familia)): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Carga Familiar</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Edad</th>
                            <th>Cédula</th>
                            <th>Parentesco</th>
                            <th>Ocupación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($familia as $familiar): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($familiar['nombre'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($familiar['edad'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($familiar['cedula'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($familiar['parentesco'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($familiar['ocupacion'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Carga Familiar</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                No hay carga familiar registrada para este productor.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DATOS DEL PREDIO -->
    <?php if ($predio): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Datos del Predio</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Nombre del Predio:</span>
                        <span class="info-value"><?php echo htmlspecialchars($predio['nombre_predio'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Hectáreas:</span>
                        <span class="info-value"><?php echo htmlspecialchars($predio['hectareas'] ?? '0'); ?></span>
                    </div>
                    
                    <h6 class="section-title">Infraestructura</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Casa:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_casa'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanque:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_tanque'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Pozo:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_pozos'] ?? 0); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Corral:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_corral'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Cercado:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_perimetral'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Barbacoa:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['posee_barbacoa'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="section-title">Registros</h6>
                    <div class="info-row">
                        <span class="info-label">INTI:</span>
                        <span class="info-value"><?php echo formatBoolean($predio['registro_inti'] ?? 0); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Hierro:</span>
                        <span class="info-value"><?php echo formatBoolean($predio['registro_hierro'] ?? 0); ?></span>
                    </div>
                    
                    <h6 class="section-title">Servicios</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Agua:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_agua'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Gas:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_gas'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Electricidad:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_electricidad'] ?? 0); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Internet:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_internet'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Transporte:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_transporte'] ?? 0); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Maquinaria:</span>
                                <span class="info-value"><?php echo formatBoolean($predio['servicio_maquinaria'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIVIDAD AGRÍCOLA -->
    <?php if ($agricultura): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Actividad Agrícola</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Tipo de Cultivo:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['tipo_cultivo'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Área Cultivada:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['area_cultivada'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tiempo de Sembrado:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['tiempo_sembrado'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cantidad Cultivada:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['cantidad_cultivada'] ?? ''); ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-row">
                        <span class="info-label">Cultivo Principal:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['cultivo_principal'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cultivo Secundario:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['cultivo_secundario'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Canal Comercialización:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['canal_comercializacion'] ?? ''); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Venta Producto:</span>
                        <span class="info-value"><?php echo htmlspecialchars($agricultura['venta_producto'] ?? ''); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GANADERÍA -->
    <?php if ($ganaderia): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ganadería y Animales</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h6 class="section-title">Bovino</h6>
                    <div class="info-row">
                        <span class="info-label">Vacas:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_vaca'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Toros:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_toro'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Novillos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_novillo'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Máticas:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_maticas'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mautes:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_mautes'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Becerros:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_becerros'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Becerras:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_becerras'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Búfalos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_bufalo'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Búfalas:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_bufala'] ?? '0'); ?></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6 class="section-title">Caprino/Ovino</h6>
                    <div class="info-row">
                        <span class="info-label">Chivos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_chivo'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cabras:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_cabra'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ovejos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_ovejo'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ovejas:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_oveja'] ?? '0'); ?></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6 class="section-title">Porcino</h6>
                    <div class="info-row">
                        <span class="info-label">Verracos:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_verraco'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cerdas Madre:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_cerda_madre'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Levantes:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_levantes'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lechones:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_lechones'] ?? '0'); ?></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6 class="section-title">Avícola y Piscicultura</h6>
                    <div class="info-row">
                        <span class="info-label">Pollos Engorde:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_pollo_engorde'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gallinas Ponedoras:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_gallinas_ponedoras'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gallinas de Patio:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_gallinas_patio'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alevines:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_alevines'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Peces:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_peces'] ?? '0'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Reproductores:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ganaderia['cant_reproductores'] ?? '0'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- RECOMENDACIONES -->
    <?php if ($recomendaciones): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recomendaciones y Observaciones</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Recomendaciones:</span>
                <span class="info-value"><?php echo nl2br(htmlspecialchars($recomendaciones['recomendaciones'] ?? '')); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Registro:</span>
                <span class="info-value"><?php echo htmlspecialchars($recomendaciones['fecha_registro'] ?? ''); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="alert alert-info">
        No se encontró información del predio para este productor.
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="pdf_productor.php?id=<?php echo urlencode($id); ?>" target="_blank" class="btn btn-danger me-2">Exportar PDF</a>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>