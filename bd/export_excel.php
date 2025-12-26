<?php
include_once 'conexion.php';

try {
    $objeto = new conexion();
    $conexion = $objeto->conectar();
    
    // Obtener todas las parroquias
    $queryParroquias = "SELECT id, nombre FROM parroquias ORDER BY nombre";
    $stmtParroquias = $conexion->prepare($queryParroquias);
    $stmtParroquias->execute();
    $parroquias = $stmtParroquias->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'Censo_Completo_Estadisticas_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    
    $sep = ';';
    
    // ===== PARTE 1: ENCABEZADO =====
    fputcsv($output, ['CENSO AGRÍCOLA Y PECUARIO - IPAUPMA'], $sep);
    fputcsv($output, ['Reporte organizado por parroquias'], $sep);
    fputcsv($output, ['Fecha generación: ' . date('d/m/Y H:i:s')], $sep);
    fputcsv($output, [], $sep);
    fputcsv($output, [], $sep);
    
    $statsTotales = [
        'parroquias' => 0,
        'productores' => 0,
        'hectareas' => 0,
        'bovinos' => 0,
        'aves' => 0,
        'cerdos' => 0
    ];
    
    // ===== PARTE 2: DATOS POR PARROQUIA =====
    foreach ($parroquias as $parroquia) {
        // Consulta detallada
        $query = "
            SELECT 
                p.cedula,
                p.nombre,
                p.apellido,
                p.telefono,
                p.oficio_ocupacion,
                pr.nombre_predio,
                pr.hectareas,
                pr.servicio_agua,
                pr.servicio_electricidad,
                pr.servicio_gas,
                a.cultivo_principal,
                a.area_cultivada,
                g.cant_vaca,
                g.cant_toro,
                g.cant_novillo,
                g.cant_pollo_engorde,
                g.cant_cerda_madre
            FROM productores p
            LEFT JOIN predios pr ON p.id = pr.productor_id
            LEFT JOIN agricultura a ON pr.id = a.predio_id
            LEFT JOIN ganaderia g ON pr.id = g.predio_id
            WHERE p.parroquia_id = ?
            ORDER BY p.apellido, p.nombre
        ";
        
        $stmt = $conexion->prepare($query);
        $stmt->execute([$parroquia['id']]);
        $productores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($productores)) {
            $statsTotales['parroquias']++;
            
            // ---- SECCIÓN DE PARROQUIA ----
            fputcsv($output, ['=== ' . strtoupper($parroquia['nombre']) . ' ==='], $sep);
            fputcsv($output, [], $sep);
            
            // Tabla de productores
            fputcsv($output, [
                'No.', 'Cédula', 'Nombre', 'Apellido', 'Teléfono', 'Ocupación',
                'Predio', 'Hectáreas', 'Cultivo', 'Área', 'Servicios', 'Vacas',
                'Toros', 'Novillos', 'Pollos', 'Cerdas'
            ], $sep);
            
            $num = 1;
            $statsParroquia = [
                'productores' => 0,
                'hectareas' => 0,
                'vacas' => 0,
                'toros' => 0,
                'novillos' => 0,
                'pollos' => 0,
                'cerdas' => 0
            ];
            
            foreach ($productores as $p) {
                $servicios = '';
                if ($p['servicio_agua'] == 1) $servicios .= 'Agua ';
                if ($p['servicio_electricidad'] == 1) $servicios .= 'Luz ';
                if ($p['servicio_gas'] == 1) $servicios .= 'Gas';
                
                fputcsv($output, [
                    $num,
                    $p['cedula'] ?? '',
                    $p['nombre'] ?? '',
                    $p['apellido'] ?? '',
                    $p['telefono'] ?? '',
                    $p['oficio_ocupacion'] ?? '',
                    $p['nombre_predio'] ?? '',
                    $p['hectareas'] ?? '',
                    $p['cultivo_principal'] ?? '',
                    $p['area_cultivada'] ?? '',
                    trim($servicios),
                    $p['cant_vaca'] ?? 0,
                    $p['cant_toro'] ?? 0,
                    $p['cant_novillo'] ?? 0,
                    $p['cant_pollo_engorde'] ?? 0,
                    $p['cant_cerda_madre'] ?? 0
                ], $sep);
                
                // Acumular estadísticas
                $statsParroquia['productores']++;
                $statsParroquia['hectareas'] += ($p['hectareas'] ?? 0);
                $statsParroquia['vacas'] += ($p['cant_vaca'] ?? 0);
                $statsParroquia['toros'] += ($p['cant_toro'] ?? 0);
                $statsParroquia['novillos'] += ($p['cant_novillo'] ?? 0);
                $statsParroquia['pollos'] += ($p['cant_pollo_engorde'] ?? 0);
                $statsParroquia['cerdas'] += ($p['cant_cerda_madre'] ?? 0);
                
                $num++;
            }
            
            // ---- ESTADÍSTICAS DE LA PARROQUIA ----
            fputcsv($output, [], $sep);
            fputcsv($output, ['ESTADÍSTICAS DE ' . $parroquia['nombre']], $sep);
            fputcsv($output, ['Total productores:', $statsParroquia['productores']], $sep);
            fputcsv($output, ['Total hectáreas:', number_format($statsParroquia['hectareas'], 2)], $sep);
            fputcsv($output, ['Promedio hectáreas/productor:', 
                ($statsParroquia['productores'] > 0 ? 
                 number_format($statsParroquia['hectareas'] / $statsParroquia['productores'], 2) : 0)], $sep);
            fputcsv($output, ['Total bovinos (vacas+toros+novillos):', 
                $statsParroquia['vacas'] + $statsParroquia['toros'] + $statsParroquia['novillos']], $sep);
            fputcsv($output, ['Total aves (pollos):', $statsParroquia['pollos']], $sep);
            fputcsv($output, ['Total cerdas:', $statsParroquia['cerdas']], $sep);
            fputcsv($output, [], $sep);
            fputcsv($output, [], $sep);
            
            // Acumular para totales generales
            $statsTotales['productores'] += $statsParroquia['productores'];
            $statsTotales['hectareas'] += $statsParroquia['hectareas'];
            $statsTotales['bovinos'] += ($statsParroquia['vacas'] + $statsParroquia['toros'] + $statsParroquia['novillos']);
            $statsTotales['aves'] += $statsParroquia['pollos'];
            $statsTotales['cerdos'] += $statsParroquia['cerdas'];
        }
    }
    
    // ===== PARTE 3: RESUMEN GENERAL =====
    fputcsv($output, [], $sep);
    fputcsv($output, ['════════════════════════════════════════════════════'], $sep);
    fputcsv($output, ['RESUMEN GENERAL DEL CENSO IPAUPMA'], $sep);
    fputcsv($output, ['════════════════════════════════════════════════════'], $sep);
    fputcsv($output, [], $sep);
    
    fputcsv($output, ['Parroquias con registros:', $statsTotales['parroquias']], $sep);
    fputcsv($output, ['Total productores censados:', $statsTotales['productores']], $sep);
    fputcsv($output, ['Total hectáreas registradas:', number_format($statsTotales['hectareas'], 2)], $sep);
    fputcsv($output, ['Promedio nacional hectáreas/productor:', 
        ($statsTotales['productores'] > 0 ? 
         number_format($statsTotales['hectareas'] / $statsTotales['productores'], 2) : 0)], $sep);
    fputcsv($output, ['Total cabezas de ganado bovino:', $statsTotales['bovinos']], $sep);
    fputcsv($output, ['Total aves de corral:', $statsTotales['aves']], $sep);
    fputcsv($output, ['Total cerdas reproductoras:', $statsTotales['cerdos']], $sep);
    fputcsv($output, [], $sep);
    
    // ===== PARTE 4: DISTRIBUCIÓN POR PARROQUIA =====
    if ($statsTotales['productores'] > 0) {
        fputcsv($output, ['DISTRIBUCIÓN POR PARROQUIA (% del total)'], $sep);
        fputcsv($output, [], $sep);
        
        // Volver a recorrer para mostrar porcentajes
        foreach ($parroquias as $parroquia) {
            $queryCount = "SELECT COUNT(*) as total FROM productores WHERE parroquia_id = ?";
            $stmtCount = $conexion->prepare($queryCount);
            $stmtCount->execute([$parroquia['id']]);
            $count = $stmtCount->fetchColumn();
            
            if ($count > 0) {
                $porcentaje = ($count / $statsTotales['productores']) * 100;
                fputcsv($output, [
                    $parroquia['nombre'],
                    $count . ' productores',
                    number_format($porcentaje, 1) . '%'
                ], $sep);
            }
        }
    }
    
    fputcsv($output, [], $sep);
    fputcsv($output, ['--- FIN DEL REPORTE ---'], $sep);
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    die('Error al generar reporte: ' . $e->getMessage());
}
?>