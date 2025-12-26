<?php
// Habilitar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'conexion.php';

$obj = new conexion();
$conexion = $obj->conectar();

// Obtener ID del productor
$id = $_GET['id'] ?? 0;

if (!$id) {
    echo '<div class="alert alert-danger">ID no válido</div>';
    exit;
}

// 1. Obtener datos del PRODUCTOR
$sql_productor = "SELECT p.*, par.nombre as parroquia_nombre 
                  FROM productores p 
                  LEFT JOIN parroquias par ON p.parroquia_id = par.id 
                  WHERE p.id = ?";
$stmt = $conexion->prepare($sql_productor);
$stmt->execute([$id]);
$productor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$productor) {
    echo '<div class="alert alert-warning">Productor no encontrado</div>';
    exit;
}

// 2. Obtener datos del PREDIO
$sql_predio = "SELECT * FROM predios WHERE productor_id = ? LIMIT 1";
$stmt = $conexion->prepare($sql_predio);
$stmt->execute([$id]);
$predio = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Obtener datos de AGRICULTURA
$sql_agricultura = "SELECT * FROM agricultura WHERE predio_id = ? LIMIT 1";
$stmt = $conexion->prepare($sql_agricultura);
$stmt->execute([$predio['id'] ?? 0]);
$agricultura = $stmt->fetch(PDO::FETCH_ASSOC);

// 4. Obtener datos de GANADERÍA
$sql_ganaderia = "SELECT * FROM ganaderia WHERE predio_id = ? LIMIT 1";
$stmt = $conexion->prepare($sql_ganaderia);
$stmt->execute([$predio['id'] ?? 0]);
$ganaderia = $stmt->fetch(PDO::FETCH_ASSOC);

// 5. Obtener CARGA FAMILIAR
$sql_carga = "SELECT * FROM carga_familiar WHERE productor_id = ?";
$stmt = $conexion->prepare($sql_carga);
$stmt->execute([$id]);
$carga_familiar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6. Obtener lista de PARROQUIAS
$sql_parroquias = "SELECT id, nombre FROM parroquias ORDER BY nombre";
$stmt = $conexion->prepare($sql_parroquias);
$stmt->execute();
$parroquias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- CSS para mejor visualización -->
<style>
    .section-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 25px 0 15px 0;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .form-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        border-left: 5px solid #3498db;
    }
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }
    .checkbox-group .form-check {
        background: white;
        padding: 8px 15px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        min-width: 120px;
    }
    .tab-content {
        padding: 20px 0;
    }
    .nav-tabs .nav-link {
        font-weight: 500;
        padding: 10px 20px;
    }
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
</style>

<div class="container-fluid py-3">
    <h4 class="mb-4 text-center" style="color: #2c3e50;">
        <i class="bi bi-person-badge"></i> Editar Productor: 
        <?php echo htmlspecialchars($productor['nombre'] . ' ' . $productor['apellido']); ?>
    </h4>
    
    <!-- Navegación por pestañas -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button">
                <i class="bi bi-person-circle"></i> Datos Personales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="predio-tab" data-bs-toggle="tab" data-bs-target="#predio" type="button">
                <i class="bi bi-house-door"></i> Predio
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="agricultura-tab" data-bs-toggle="tab" data-bs-target="#agricultura" type="button">
                <i class="bi bi-tree"></i> Agricultura
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ganaderia-tab" data-bs-toggle="tab" data-bs-target="#ganaderia" type="button">
                <i class="bi bi-egg-fried"></i> Ganadería
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="carga-tab" data-bs-toggle="tab" data-bs-target="#carga" type="button">
                <i class="bi bi-people"></i> Carga Familiar
            </button>
        </li>
    </ul>

    <form id="formEditarProductor" class="tab-content">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="predio_id" value="<?php echo $predio['id'] ?? ''; ?>">
        
        <!-- PESTAÑA 1: DATOS PERSONALES -->
        <div class="tab-pane fade show active" id="datos" role="tabpanel">
            <div class="section-title">
                <i class="bi bi-person-lines-fill"></i> Información Personal
            </div>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" maxlength="40" 
                           value="<?php echo htmlspecialchars($productor['nombre'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" maxlength="40"
                           value="<?php echo htmlspecialchars($productor['apellido'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" maxlength="10" 
                           value="<?php echo htmlspecialchars($productor['cedula'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Parroquia</label>
                    <select name="parroquia_id" class="form-select" required>
                        <option value="">Seleccionar parroquia</option>
                        <?php foreach ($parroquias as $parroquia): ?>
                            <option value="<?php echo $parroquia['id']; ?>"
                                <?php echo ($productor['parroquia_id'] == $parroquia['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($parroquia['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Sector</label>
                    <input type="text" name="sector" class="form-control" maxlength="40" 
                           value="<?php echo htmlspecialchars($productor['sector'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" maxlength="12" 
                           value="<?php echo htmlspecialchars($productor['telefono'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">RIF</label>
                    <input type="text" name="rif" class="form-control" maxlength="10"
                           value="<?php echo htmlspecialchars($productor['rif'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" 
                           value="<?php echo htmlspecialchars($productor['fecha_nacimiento'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Grado Instrucción</label>
                    <select class="form-select form-select-sm" name="grado_instruccion" required>
                        <option value="">Seleccione...</option>
                        <option value="PRIMARIA" <?php echo ($productor['grado_instruccion'] == 'PRIMARIA') ? 'selected' : ''; ?>>PRIMARIA</option>
                        <option value="BACHILLER" <?php echo ($productor['grado_instruccion'] == 'BACHILLER') ? 'selected' : ''; ?>>BACHILLER</option>
                        <option value="TÉCNICO MEDIO" <?php echo ($productor['grado_instruccion'] == 'TÉCNICO MEDIO') ? 'selected' : ''; ?>>TÉCNICO MEDIO</option>
                        <option value="UNIVERSITARIO" <?php echo ($productor['grado_instruccion'] == 'UNIVERSITARIO') ? 'selected' : ''; ?>>UNIVERSITARIO</option>
                        <option value="NINGUNO" <?php echo ($productor['grado_instruccion'] == 'NINGUNO') ? 'selected' : ''; ?>>NINGUNO</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Ocupación</label>
                    <input type="text" name="oficio_ocupacion" class="form-control" maxlength="40"
                           value="<?php echo htmlspecialchars($productor['oficio_ocupacion'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <!-- PESTAÑA 2: DATOS DEL PREDIO -->
        <div class="tab-pane fade" id="predio" role="tabpanel">
            <div class="section-title">
                <i class="bi bi-house-heart"></i> Información del Predio
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre del Predio</label>
                    <input type="text" name="nombre_predio" class="form-control" maxlength="40"
                           value="<?php echo htmlspecialchars($predio['nombre_predio'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Hectáreas</label>
                    <input type="number" step="0.01" name="hectareas" class="form-control" maxlength="3"
                           value="<?php echo htmlspecialchars($predio['hectareas'] ?? '0'); ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label mb-3">Infraestructura del Predio</label>
                    <div class="checkbox-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_casa" value="1" 
                                   <?php echo (!empty($predio['posee_casa'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Posee Casa</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_tanque" value="1" 
                                   <?php echo (!empty($predio['posee_tanque'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Posee Tanque</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_pozos" value="1" 
                                   <?php echo (!empty($predio['posee_pozos'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Posee Pozos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_corral" value="1" 
                                   <?php echo (!empty($predio['posee_corral'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Posee Corral</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_perimetral" value="1" 
                                   <?php echo (!empty($predio['posee_perimetral'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Cerca Perimetral</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="posee_barbacoa" value="1" 
                                   <?php echo (!empty($predio['posee_barbacoa'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Barbacoa</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <label class="form-label mb-3">Registros</label>
                    <div class="checkbox-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="registro_inti" value="1" 
                                   <?php echo (!empty($predio['registro_inti'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Registro INTI</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="registro_hierro" value="1" 
                                   <?php echo (!empty($predio['registro_hierro'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Registro Hierro</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <label class="form-label mb-3">Servicios Disponibles</label>
                    <div class="checkbox-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_agua" value="1" 
                                   <?php echo (!empty($predio['servicio_agua'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Agua</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_gas" value="1" 
                                   <?php echo (!empty($predio['servicio_gas'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Gas</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_electricidad" value="1" 
                                   <?php echo (!empty($predio['servicio_electricidad'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Electricidad</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_internet" value="1" 
                                   <?php echo (!empty($predio['servicio_internet'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Internet</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_transporte" value="1" 
                                   <?php echo (!empty($predio['servicio_transporte'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Transporte</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="servicio_maquinaria" value="1" 
                                   <?php echo (!empty($predio['servicio_maquinaria'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label">Maquinaria</label>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Recomendaciones</label>
                    <textarea name="recomendaciones" class="form-control" maxlength="255" rows="3"><?php 
                        echo htmlspecialchars($predio['recomendaciones'] ?? ''); 
                    ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- PESTAÑA 3: AGRICULTURA -->
        <div class="tab-pane fade" id="agricultura" role="tabpanel">
            <div class="section-title">
                <i class="bi bi-tree-fill"></i> Actividad Agrícola
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Cultivo</label>
                    <input type="text" name="tipo_cultivo" class="form-control" maxlength="20"
                           value="<?php echo htmlspecialchars($agricultura['tipo_cultivo'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Área Cultivada (m²)</label>
                    <input type="text" name="area_cultivada" class="form-control" maxlength="20"
                           value="<?php echo htmlspecialchars($agricultura['area_cultivada'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Tiempo Sembrado</label>
                    <input type="text" name="tiempo_sembrado" class="form-control" maxlength="20" 
                           value="<?php echo htmlspecialchars($agricultura['tiempo_sembrado'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Canal de Comercialización</label> 
                    <input type="text" name="canal_comercializacion" class="form-control" maxlength="30"
                           value="<?php echo htmlspecialchars($agricultura['canal_comercializacion'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Cultivo Principal</label>
                    <input type="text" name="cultivo_principal" class="form-control" maxlength="20"
                           value="<?php echo htmlspecialchars($agricultura['cultivo_principal'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Cultivo Secundario</label>
                    <input type="text" name="cultivo_secundario" class="form-control" maxlength="20" 
                           value="<?php echo htmlspecialchars($agricultura['cultivo_secundario'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Cantidad Cultivada</label>
                    <input type="text" name="cantidad_cultivada" class="form-control" maxlength="20"
                           value="<?php echo htmlspecialchars($agricultura['cantidad_cultivada'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Venta del Producto</label>
                    <input type="text" name="venta_producto" class="form-control" maxlength="40"
                           value="<?php echo htmlspecialchars($agricultura['venta_producto'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
       <!-- PESTAÑA 4: GANADERÍA (Diseño compacto) -->
<div class="tab-pane fade" id="ganaderia" role="tabpanel">
    <div class="section-title">
        <i class="bi bi-egg-fried"></i> Actividad Ganadera (Todos los Campos)
    </div>
    
    <div class="card compact-card border-danger">
        <div class="card-header bg-danger text-white">Agrícola Animal (Cantidades)</div>
        <div class="card-body">
            <div class="row text-center">
                <!-- COLUMNA 1: BOVINOS -->
                <div class="col-md-3 border-end">
                    <strong class="d-block mb-2 text-danger">BOVINOS</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Vacas</span>
                        <input type="number" name="cant_vaca" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_vaca'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Toros</span>
                        <input type="number" name="cant_toro" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_toro'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Novillos</span>
                        <input type="number" name="cant_novillo" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_novillo'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Máticas</span>
                        <input type="number" name="cant_maticas" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_maticas'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Mautes</span>
                        <input type="number" name="cant_mautes" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_mautes'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Becerros</span>
                        <input type="number" name="cant_becerros" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_becerros'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Becerras</span>
                        <input type="number" name="cant_becerras" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_becerras'] ?? '0'); ?>">
                    </div>
                </div>
                
                <!-- COLUMNA 2: BÚFALOS y CAPRINOS/OVINOS -->
                <div class="col-md-3 border-end">
                    <!-- BÚFALOS -->
                    <strong class="d-block mb-2 text-warning">BÚFALOS</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Búfalos</span>
                        <input type="number" name="cant_bufalo" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_bufalo'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Búfalas</span>
                        <input type="number" name="cant_bufala" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_bufala'] ?? '0'); ?>">
                    </div>
                    
                    <!-- CAPRINOS/OVINOS -->
                    <strong class="d-block mb-2 mt-3 text-primary">CAPRINOS/OVINOS</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Chivos</span>
                        <input type="number" name="cant_chivo" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_chivo'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Cabras</span>
                        <input type="number" name="cant_cabra" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_cabra'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Ovejos</span>
                        <input type="number" name="cant_ovejo" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_ovejo'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Ovejas</span>
                        <input type="number" name="cant_oveja" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_oveja'] ?? '0'); ?>">
                    </div>
                </div>
                
                <!-- COLUMNA 3: PORCINOS y AVES -->
                <div class="col-md-3 border-end">
                    <!-- PORCINOS -->
                    <strong class="d-block mb-2 text-info">PORCINOS</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Verracos</span>
                        <input type="number" name="cant_verraco" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_verraco'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Cerdas Madre</span>
                        <input type="number" name="cant_cerda_madre" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_cerda_madre'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Levantes</span>
                        <input type="number" name="cant_levantes" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_levantes'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Lechones</span>
                        <input type="number" name="cant_lechones" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_lechones'] ?? '0'); ?>">
                    </div>
                    
                    <!-- AVES -->
                    <strong class="d-block mb-2 mt-3 text-warning">AVES</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Pollos Engorde</span>
                        <input type="number" name="cant_pollo_engorde" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_pollo_engorde'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Gallinas Ponedoras</span>
                        <input type="number" name="cant_gallinas_ponedoras" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_gallinas_ponedoras'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Gallinas Patio</span>
                        <input type="number" name="cant_gallinas_patio" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_gallinas_patio'] ?? '0'); ?>">
                    </div>
                </div>
                
                <!-- COLUMNA 4: PISCICULTURA -->
                <div class="col-md-3">
                    <strong class="d-block mb-2 text-success">PISCICULTURA</strong>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Alevines</span>
                        <input type="number" name="cant_alevines" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_alevines'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Peces</span>
                        <input type="number" name="cant_peces" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_peces'] ?? '0'); ?>">
                    </div>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text w-50">Reproductores</span>
                        <input type="number" name="cant_reproductores" class="form-control" 
                               value="<?php echo htmlspecialchars($ganaderia['cant_reproductores'] ?? '0'); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- PESTAÑA 5: CARGA FAMILIAR -->
        <div class="tab-pane fade" id="carga" role="tabpanel">
            <div class="section-title">
                <i class="bi bi-people-fill"></i> Carga Familiar
            </div>
            
            <div id="carga-familiar-container">
                <?php if (empty($carga_familiar)): ?>
                    <div class="alert alert-info">
                        No hay miembros registrados en la carga familiar.
                    </div>
                <?php else: ?>
                    <?php foreach ($carga_familiar as $index => $miembro): ?>
                        <div class="miembro-familiar border p-3 mb-3 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Miembro <?php echo $index + 1; ?></h6>
                                <button type="button" class="btn btn-sm btn-danger btn-eliminar-miembro">X</button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="carga_nombre[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($miembro['nombre'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Edad</label>
                                    <input type="number" name="carga_edad[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($miembro['edad'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Cédula</label>
                                    <input type="text" name="carga_cedula[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($miembro['cedula'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Parentesco</label>
                                    <input type="text" name="carga_parentesco[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($miembro['parentesco'] ?? ''); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Ocupación</label>
                                    <input type="text" name="carga_ocupacion[]" class="form-control" 
                                           value="<?php echo htmlspecialchars($miembro['ocupacion'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-3">
                <button type="button" class="btn btn-success" id="btn-agregar-miembro">
                    <i class="bi bi-person-plus"></i> Agregar Miembro Familiar
                </button>
            </div>
        </div>
        
        <!-- Información de guardado -->
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle"></i> Haz clic en "Guardar Cambios" para guardar todos los datos de todas las pestañas.
        </div>
    </form>
</div>

<!-- Script para agregar miembros familiares -->
<script>
$(document).ready(function() {
    // Agregar nuevo miembro familiar (con límite de 3)
    $('#btn-agregar-miembro').click(function() {
        const container = $('#carga-familiar-container');
        const miembros = container.find('.miembro-familiar');
        const totalMiembros = miembros.length;
        
        if (totalMiembros >= 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Límite alcanzado',
                text: 'Solo se permiten hasta 3 miembros familiares.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }
        
        const count = totalMiembros + 1;
        
        const nuevoMiembro = `
            <div class="miembro-familiar border p-3 mb-3 rounded">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Nuevo Miembro ${count}</h6>
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-miembro">X</button>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="carga_nombre[]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Edad</label>
                        <input type="number" name="carga_edad[]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" name="carga_cedula[]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Parentesco</label>
                        <input type="text" name="carga_parentesco[]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ocupación</label>
                        <input type="text" name="carga_ocupacion[]" class="form-control">
                    </div>
                </div>
            </div>
        `;
        
        container.append(nuevoMiembro);

        
        
        // Scroll al nuevo miembro
        container.find('.miembro-familiar:last')[0].scrollIntoView({ behavior: 'smooth' });
        
        // Deshabilitar botón si llega a 3
        if (container.find('.miembro-familiar').length >= 3) {
            $('#btn-agregar-miembro').prop('disabled', true);
        }
    });
    
    // Eliminar miembro familiar
    $(document).on('click', '.btn-eliminar-miembro', function() {
        $(this).closest('.miembro-familiar').remove();
        
        // Habilitar botón si baja de 3
        const container = $('#carga-familiar-container');
        if (container.find('.miembro-familiar').length < 3) {
            $('#btn-agregar-miembro').prop('disabled', false);
        }
    });
    
    // Activar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

     $(document).on('input', 'input[type="text"], textarea', function() {
        this.value = this.value.toUpperCase();
    });

    
    // Convertir texto a mayúsculas al teclear
    $(document).on('input', 'input[type="text"], textarea', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Limitar entrada en campos de texto
    $(document).on('input', 'input[type="text"]', function() {
        const name = $(this).attr('name');
        
        if (name.includes('nombre') || name.includes('apellido') || name.includes('parentesco') || name.includes('ocupacion') || name.includes('sector') || name.includes('oficio_ocupacion')) {
            // Solo letras y espacios
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        } else if (name.includes('cedula') || name.includes('telefono') || name.includes('rif')) {
            // Letras, números y guiones
            this.value = this.value.replace(/[^a-zA-Z0-9\-]/g, '');
        } else {
            // Para otros campos de texto: letras, números, espacios y guiones
            this.value = this.value.replace(/[^a-zA-Z0-9\s\-]/g, '');
        }
    });
    
    // Reforzar límite en campos numéricos (aunque HTML ya lo hace, por si acaso)
    $(document).on('input', 'input[type="number"]', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Para textareas (como recomendaciones), permitir letras, números, espacios, guiones, puntos, comas, punto y coma, y dos puntos
    $(document).on('input', 'textarea', function() {
        this.value = this.value.replace(/[^a-zA-Z0-9\s\-\.\,\;\:]/g, '');
    });
});
</script>