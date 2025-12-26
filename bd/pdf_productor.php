<?php
require_once __DIR__ . '/../dompdf/autoload.inc.php';
include_once 'conexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* ===========================
   CONEXIÓN
=========================== */
$objeto = new conexion();
$conexion = $objeto->conectar();

$id = $_GET['id'] ?? null;
if(!$id){ 
    die('ID no proporcionado'); 
}

/* ===========================
   CONSULTAS (IGUAL A info_productor.php)
=========================== */
$consulta = "SELECT p.*, pr.nombre AS parroquia_nombre
             FROM productores p
             LEFT JOIN parroquias pr ON p.parroquia_id = pr.id
             WHERE p.id = ?";
$stmt = $conexion->prepare($consulta);
$stmt->execute([$id]);
$productor = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$productor){ 
    die('Productor no encontrado'); 
}

$stmt = $conexion->prepare("SELECT * FROM predios WHERE productor_id = ?");
$stmt->execute([$id]);
$predio = $stmt->fetch(PDO::FETCH_ASSOC);

$agricultura = $ganaderia = $recomendaciones = [];
if($predio){
    $stmt = $conexion->prepare("SELECT * FROM agricultura WHERE predio_id = ?");
    $stmt->execute([$predio['id']]);
    $agricultura = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conexion->prepare("SELECT * FROM ganaderia WHERE predio_id = ?");
    $stmt->execute([$predio['id']]);
    $ganaderia = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conexion->prepare("SELECT * FROM recomendaciones WHERE predio_id = ?");
    $stmt->execute([$predio['id']]);
    $recomendaciones = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $conexion->prepare("SELECT * FROM carga_familiar WHERE productor_id = ?");
$stmt->execute([$id]);
$familia = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   FUNCIONES
=========================== */
function siNo($v){ 
    return ($v == 1 || $v == '1' || $v === true || strtolower($v) == 'sí') ? 'Sí' : ''; 
}

function safe($v){ 
    if ($v === null || $v === false) {
        return '';
    }
    return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
}

/* ===========================
   DOMPDF
=========================== */
$options = new Options();
$options->set('defaultFont','Helvetica');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

$imgPath = realpath(__DIR__ . '/../img/Menbrete.jpg');
if (!$imgPath || !file_exists($imgPath)) {
    die('Imagen no encontrada en: ' . __DIR__ . '/../img/Menbrete.jpg');
}
$imgData = base64_encode(file_get_contents($imgPath));
$imgSrc = 'data:image/jpeg;base64,' . $imgData;

/* ===========================
   HTML PDF (PLANILLA CENSO COMPLETA - UNA HOJA)
=========================== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page{ 
    size:A4; 
    margin:3mm 5mm;
    margin-top: 5mm;
}
body{ 
    font-size:9px; 
    font-family:Helvetica, Arial, sans-serif; 
    line-height: 1.1;
    -webkit-font-smoothing: antialiased;
}
table{ 
    width:100%; 
    border-collapse:collapse;
    margin-bottom: 4px;
    table-layout: fixed;
}
td,th{ 
    border:1px solid #000; 
    padding:3px 4px;
    vertical-align: middle;
    height: 20px;
    overflow: hidden;
    word-wrap: break-word;
}
.section{ 
    background:#eaeaea; 
    font-weight:bold; 
    text-align:center; 
    font-size: 9.5px;
    padding: 4px 0;
}
.center{ text-align:center; }
.no td{ border:none; }
.subsection{ 
    font-weight: bold; 
    background: #f5f5f5;
    font-size: 8.5px;
}
.animal-cell { width: 12.5%; }
.wide-cell { width: 25%; }
.medium-cell { width: 16.6%; }
.family-cell { width: 20%; }
.full-width { width: 100%; }
.text-center { text-align: center; }
.text-left { text-align: left; }
.compact-row { height: 18px; }
.space-bottom { margin-bottom: 8px; }
</style>
</head>
<body>

<!-- LOGO Y TÍTULO -->
<table class="no space-bottom">
<tr><td class="center">
<img src="' . $imgSrc . '" style="width:100%;max-height:75px">
</td></tr>
</table>

<table class="space-bottom"><tr><td class="center"><b style="font-size:11px;">CENSO AGRÍCOLA Y PECUARIO</b></td></tr></table>

<!-- DATOS BÁSICOS -->
<table class="compact-row">
<tr>
<td class="wide-cell"><strong>Parroquia:</strong> ' . safe($productor['parroquia_nombre']) . '</td>
<td class="wide-cell"><strong>Sector:</strong> ' . safe($productor['sector']) . '</td>
<td class="wide-cell"><strong>Fecha Registro:</strong> ' . (isset($recomendaciones['fecha_registro']) ? safe($recomendaciones['fecha_registro']) : date('d/m/Y')) . '</td>
</tr>
</table>

<!-- DATOS DEL PRODUCTOR -->
<table class="compact-row">
<tr class="section"><td colspan="6">DATOS DEL PRODUCTOR</td></tr>
<tr>
<td colspan="3"><strong>Nombre y Apellido:</strong> ' . safe($productor['nombre']) . ' ' . safe($productor['apellido']) . '</td>
<td><strong>C.I.:</strong> ' . safe($productor['cedula']) . '</td>
<td colspan="2"><strong>RIF:</strong> ' . safe($productor['rif']) . '</td>
</tr>
<tr>
<td><strong>Teléfono:</strong> ' . safe($productor['telefono']) . '</td>
<td><strong>Nacimiento:</strong> ' . safe($productor['fecha_nacimiento']) . '</td>
<td><strong>Instrucción:</strong> ' . safe($productor['grado_instruccion']) . '</td>
<td colspan="3"><strong>Ocupación:</strong> ' . safe($productor['oficio_ocupacion']) . '</td>
</tr>
</table>';

// CARGA FAMILIAR - CON MEJOR MANEJO DE DATOS
$html .= '
<!-- CARGA FAMILIAR -->
<table class="compact-row">
<tr class="section"><td colspan="5">CARGA FAMILIAR</td></tr>
<tr class="subsection">
<td class="family-cell"><strong>Nombre</strong></td>
<td class="text-center"><strong>Edad</strong></td>
<td class="family-cell"><strong>C.I.</strong></td>
<td class="text-center"><strong>Parentesco</strong></td>
<td class="family-cell"><strong>Ocupación</strong></td>
</tr>';

// Mostrar hasta 3 familiares o filas vacías
for($i = 0; $i < 3; $i++){
    if(isset($familia[$i])) {
        $familiar = $familia[$i];
        $html .= '<tr class="compact-row">
        <td>' . safe($familiar['nombre']) . '</td>
        <td class="text-center">' . safe($familiar['edad']) . '</td>
        <td>' . safe($familiar['cedula']) . '</td>
        <td class="text-center">' . safe($familiar['parentesco']) . '</td>
        <td>' . safe($familiar['ocupacion']) . '</td>
        </tr>';
    } else {
        // Fila vacía si no hay más familiares
        $html .= '<tr class="compact-row">
        <td>&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td>&nbsp;</td>
        <td class="text-center">&nbsp;</td>
        <td>&nbsp;</td>
        </tr>';
    }
}

$html .= '
</table>';

// Si hay predio, mostrar toda la información
if($predio):
$html .= '

<!-- DATOS DEL PREDIO -->
<table class="compact-row">
<tr class="section"><td colspan="8">DATOS DEL PREDIO</td></tr>
<tr>
<td colspan="4"><strong>Nombre del Predio:</strong> ' . safe($predio['nombre_predio']) . '</td>
<td colspan="4"><strong>Hectáreas:</strong> ' . safe($predio['hectareas']) . '</td>
</tr>
</table>

<!-- INFRAESTRUCTURA Y REGISTROS -->
<table class="compact-row">
<tr class="subsection"><td colspan="8">INFRAESTRUCTURA Y REGISTROS</td></tr>
<tr>
<td>Casa: ' . siNo($predio['posee_casa']) . '</td>
<td>Tanque: ' . siNo($predio['posee_tanque']) . '</td>
<td>Pozo: ' . siNo($predio['posee_pozos']) . '</td>
<td>Cercado: ' . siNo($predio['posee_perimetral']) . '</td>
<td>Barbacoa: ' . siNo($predio['posee_barbacoa']) . '</td>
<td>Corral: ' . siNo($predio['posee_corral']) . '</td>
<td>INTI: ' . siNo($predio['registro_inti']) . '</td>
<td>Hierro: ' . siNo($predio['registro_hierro']) . '</td>
</tr>
</table>

<!-- SERVICIOS -->
<table class="compact-row">
<tr class="subsection"><td colspan="7">SERVICIOS DISPONIBLES</td></tr>
<tr>
<td>Agua: ' . siNo($predio['servicio_agua']) . '</td>
<td>Gas: ' . siNo($predio['servicio_gas']) . '</td>
<td>Electricidad: ' . siNo($predio['servicio_electricidad']) . '</td>
<td>Internet: ' . siNo($predio['servicio_internet']) . '</td>
<td>Maquinaria: ' . siNo($predio['servicio_maquinaria']) . '</td>
<td>Transporte: ' . siNo($predio['servicio_transporte']) . '</td>
<td>Teléfono: ' . siNo(isset($predio['servicio_telefono']) ? $predio['servicio_telefono'] : 0) . '</td>
</tr>
</table>';

// AGRICULTURA
if($agricultura && !empty(array_filter($agricultura))):
$html .= '
<!-- ACTIVIDAD AGRÍCOLA -->
<table class="compact-row">
<tr class="section"><td colspan="8">ACTIVIDAD AGRÍCOLA</td></tr>
<tr>
<td colspan="2"><strong>Tipo Cultivo:</strong> ' . safe($agricultura['tipo_cultivo']) . '</td>
<td colspan="2"><strong>Área Cultivada:</strong> ' . safe($agricultura['area_cultivada']) . '</td>
<td colspan="2"><strong>Tiempo Sembrado:</strong> ' . safe($agricultura['tiempo_sembrado']) . '</td>
<td colspan="2"><strong>Cantidad:</strong> ' . safe($agricultura['cantidad_cultivada']) . '</td>
</tr>
<tr>
<td colspan="3"><strong>Cultivo Principal:</strong> ' . safe($agricultura['cultivo_principal']) . '</td>
<td colspan="3"><strong>Cultivo Secundario:</strong> ' . safe($agricultura['cultivo_secundario']) . '</td>
<td colspan="2"><strong>Canal:</strong> ' . safe($agricultura['canal_comercializacion']) . '</td>
</tr>
<tr>
<td colspan="8"><strong>Venta Producto:</strong> ' . safe($agricultura['venta_producto']) . '</td>
</tr>
</table>';
endif;

// GANADERÍA - BOVINO
if($ganaderia && !empty(array_filter($ganaderia))):
$html .= '
<!-- GANADERÍA - BOVINO -->
<table class="compact-row">
<tr class="section"><td colspan="8">GANADERÍA - BOVINO</td></tr>
<tr>
<td class="animal-cell text-center"><strong>Vacas</strong><br>' . safe($ganaderia['cant_vaca'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Toros</strong><br>' . safe($ganaderia['cant_toro'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Novillos</strong><br>' . safe($ganaderia['cant_novillo'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Máticas</strong><br>' . safe($ganaderia['cant_maticas'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Mautes</strong><br>' . safe($ganaderia['cant_mautes'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Becerros</strong><br>' . safe($ganaderia['cant_becerros'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Becerras</strong><br>' . safe($ganaderia['cant_becerras'] ?? '0') . '</td>
<td class="animal-cell text-center"><strong>Búfalos</strong><br>' . safe($ganaderia['cant_bufalo'] ?? '0') . '</td>
</tr>
</table>

<!-- GANADERÍA - OTROS ANIMALES -->
<table class="compact-row">
<tr class="section"><td colspan="8">GANADERÍA - OTROS ANIMALES</td></tr>
<tr class="subsection"><td colspan="8">CAPRINO/OVINO</td></tr>
<tr>
<td class="medium-cell text-center"><strong>Chivos</strong><br>' . safe($ganaderia['cant_chivo'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Cabras</strong><br>' . safe($ganaderia['cant_cabra'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Ovejos</strong><br>' . safe($ganaderia['cant_ovejo'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Ovejas</strong><br>' . safe($ganaderia['cant_oveja'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Búfalas</strong><br>' . safe($ganaderia['cant_bufala'] ?? '0') . '</td>
<td colspan="3"></td>
</tr>
</table>

<table class="compact-row">
<tr class="subsection"><td colspan="8">PORCINO</td></tr>
<tr>
<td class="medium-cell text-center"><strong>Verracos</strong><br>' . safe($ganaderia['cant_verraco'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Cerdas Madre</strong><br>' . safe($ganaderia['cant_cerda_madre'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Levantes</strong><br>' . safe($ganaderia['cant_levantes'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Lechones</strong><br>' . safe($ganaderia['cant_lechones'] ?? '0') . '</td>
<td colspan="4"></td>
</tr>
</table>

<table class="compact-row">
<tr class="subsection"><td colspan="8">AVÍCOLA Y PISCICULTURA</td></tr>
<tr>
<td class="medium-cell text-center"><strong>Pollos</strong><br>' . safe($ganaderia['cant_pollo_engorde'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Gallinas Pon.</strong><br>' . safe($ganaderia['cant_gallinas_ponedoras'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Gallinas Patio</strong><br>' . safe($ganaderia['cant_gallinas_patio'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Alevines</strong><br>' . safe($ganaderia['cant_alevines'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Peces</strong><br>' . safe($ganaderia['cant_peces'] ?? '0') . '</td>
<td class="medium-cell text-center"><strong>Reproductores</strong><br>' . safe($ganaderia['cant_reproductores'] ?? '0') . '</td>
<td colspan="2"></td>
</tr>
</table>';
endif;

// RECOMENDACIONES
if($recomendaciones && !empty($recomendaciones['recomendaciones'])):
$html .= '
<!-- RECOMENDACIONES -->
<table style="height: 60px;">
<tr class="section"><td colspan="8">RECOMENDACIONES Y OBSERVACIONES</td></tr>
<tr>
<td colspan="8" style="height: 20px; vertical-align: top; padding: 5px;">' . nl2br(safe($recomendaciones['recomendaciones'])) . '</td>
</tr>
</table>';
endif;

else:
$html .= '
<table class="compact-row">
<tr class="section"><td colspan="8">PREDIO</td></tr>
<tr><td colspan="8" class="center">NO SE ENCONTRÓ INFORMACIÓN DEL PREDIO</td></tr>
</table>';
endif;

$html .= '

<!-- FIRMAS -->
<table style="margin-top: 15px; border: none;">
<tr>
<td style="width: 40%; border-top: 1px solid #000; padding-top: 20px; border: none; vertical-align: top;">
<strong>FIRMA DEL PRODUCTOR</strong><br><br>
_____________________________
</td>
<td style="width: 20%; border: none; text-align: center; vertical-align: middle; padding: 0 5px;">
<em style="font-size: 7px;">Documento<br>generado el:<br>' . date('d/m/Y H:i:s') . '</em>
</td>
<td style="width: 40%; border-top: 1px solid #000; padding-top: 20px; border: none; vertical-align: top;">
<strong>FIRMA DEL ENCUESTADOR</strong><br><br>
_____________________________
</td>
</tr>
</table>

</body>
</html>';

// Para depuración, puedes ver el HTML generado
// echo $html; exit;

try {
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4','portrait');
    $dompdf->render();
    $dompdf->stream("planilla_censo_".$id.".pdf",["Attachment"=>false]);
} catch (Exception $e) {
    die('Error al generar PDF: ' . $e->getMessage());
}