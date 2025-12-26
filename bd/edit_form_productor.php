<?php
require_once 'bd/conexion.php';
$obj = new conexion();
$conexion = $obj->conectar();

$id = $_GET['id'] ?? null;
if (!$id) {
    echo '<div class="alert alert-danger">ID no proporcionado</div>';
    exit;
}

// obtener productor
$sql = "SELECT p.*, par.nombre AS parroquia_nombre FROM productores p
        LEFT JOIN parroquias par ON p.parroquia_id = par.id
        WHERE p.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prod) {
    echo '<div class="alert alert-warning">Productor no encontrado</div>';
    exit;
}

// obtener predio (si existe)
$stmt = $conexion->prepare("SELECT * FROM predios WHERE productor_id = ? LIMIT 1");
$stmt->execute([$id]);
$predio = $stmt->fetch(PDO::FETCH_ASSOC);

// obtener parroquias para select
$parroqs = [];
$stmt = $conexion->prepare("SELECT id, nombre FROM parroquias ORDER BY nombre");
$stmt->execute();
$parroqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formulario (id=formEditarAjax)
?>
<form id="formEditarAjax" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($prod['id']); ?>">
    <div class="container-fluid">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input name="nombre" class="form-control" value="<?php echo htmlspecialchars($prod['nombre']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Apellido</label>
                <input name="apellido" class="form-control" value="<?php echo htmlspecialchars($prod['apellido']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cédula</label>
                <input name="cedula" class="form-control" value="<?php echo htmlspecialchars($prod['cedula']); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Parroquia</label>
                <select name="parroquia_id" class="form-select">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($parroqs as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php if ($prod['parroquia_id']==$p['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($p['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Sector</label>
                <input name="sector" class="form-control" value="<?php echo htmlspecialchars($prod['sector']); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input name="telefono" class="form-control" value="<?php echo htmlspecialchars($prod['telefono'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">RIF</label>
                <input name="rif" class="form-control" value="<?php echo htmlspecialchars($prod['rif'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Fecha Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($prod['fecha_nacimiento'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Grado Instrucción</label>
                <input name="grado_instruccion" class="form-control" value="<?php echo htmlspecialchars($prod['grado_instruccion'] ?? ''); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label">Ocupación</label>
                <input name="oficio_ocupacion" class="form-control" value="<?php echo htmlspecialchars($prod['oficio_ocupacion'] ?? ''); ?>">
            </div>

            <!-- Predio -->
            <div class="col-12"><hr><h6>Datos del Predio</h6></div>
            <div class="col-md-6">
                <label class="form-label">Nombre predio</label>
                <input name="nombre_predio" class="form-control" value="<?php echo htmlspecialchars($predio['nombre_predio'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hectáreas</label>
                <input name="hectareas" class="form-control" value="<?php echo htmlspecialchars($predio['hectareas'] ?? ''); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Servicios (checkboxes)</label>
                <div class="d-flex gap-2 flex-wrap">
                    <label class="form-check form-check-inline"><input type="checkbox" name="servicio_agua" class="form-check-input" <?php if(!empty($predio['servicio_agua'])) echo 'checked'; ?>>Agua</label>
                    <label class="form-check form-check-inline"><input type="checkbox" name="servicio_electricidad" class="form-check-input" <?php if(!empty($predio['servicio_electricidad'])) echo 'checked'; ?>>Electricidad</label>
                    <label class="form-check form-check-inline"><input type="checkbox" name="servicio_internet" class="form-check-input" <?php if(!empty($predio['servicio_internet'])) echo 'checked'; ?>>Internet</label>
                </div>
            </div>

            <!-- área de observaciones / recomendaciones -->
            <div class="col-12">
                <label class="form-label">Recomendaciones</label>
                <textarea name="recomendaciones" class="form-control" rows="3"><?php echo htmlspecialchars($predio['recomendaciones'] ?? ''); ?></textarea>
            </div>

            <!-- footer not submit (submit handled por JS) -->
            <div class="col-12 text-end pt-2">
                <small class="text-muted">Guarda usando el botón en el modal.</small>
            </div>
        </div>
    </div>
</form>
<?php
// fin