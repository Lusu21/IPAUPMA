<?php
session_start();
// 1. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. Incluir conexión (ruta relativa al propio archivo)
require_once __DIR__ . '/conexion.php';

// 3. Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../inscripcion_censo.php");
    exit();
}

try {
    // 4. Conectar a la base de datos
    $pdo = conexion::conectar();
    
    // Iniciar transacción: crucial para asegurar que si una parte falla, todo se revierte
    if ($pdo instanceof PDO) {
        $pdo->beginTransaction();
    }

    // ----------------------------------------------------
    // I. INSERTAR DATOS DEL PRODUCTOR (Tabla: productores)
    // ----------------------------------------------------
    
    // Limpiar y obtener datos del productor
    $parroquia_id       = $_POST['parroquia_id'] ?? null;
    $nombre             = trim(strtoupper($_POST['nombre'] ?? ''));
    $sector             = trim(strtoupper($_POST['sector'] ?? ''));
    $apellido           = trim(strtoupper($_POST['apellido'] ?? ''));
    $cedula             = trim($_POST['cedula'] ?? '');
    $rif                = trim(strtoupper($_POST['rif'] ?? ''));
    $fecha_nacimiento   = $_POST['fecha_nacimiento'] ?? null;
    $grado_instruccion  = $_POST['grado_instruccion'] ?? null;
    $oficio_ocupacion   = trim(strtoupper($_POST['oficio_ocupacion'] ?? ''));
    $telefono           = trim($_POST['telefono'] ?? '');
    
    $sql_productor = "INSERT INTO productores (
        parroquia_id, nombre, sector, apellido, cedula, rif, 
        fecha_nacimiento, grado_instruccion, oficio_ocupacion, telefono
    ) VALUES (
        :parroquia_id, :nombre, :sector, :apellido, :cedula, :rif, 
        :fecha_nacimiento, :grado_instruccion, :oficio_ocupacion, :telefono
    )";

    $stmt_productor = $pdo->prepare($sql_productor);
    $stmt_productor->execute([
        'parroquia_id'      => $parroquia_id,
        'nombre'            => $nombre,
        'sector'            => $sector,
        'apellido'          => $apellido,
        'cedula'            => $cedula,
        'rif'               => $rif,
        'fecha_nacimiento'  => $fecha_nacimiento ?: null,
        'grado_instruccion' => $grado_instruccion,
        'oficio_ocupacion'  => $oficio_ocupacion,
        'telefono'          => $telefono
    ]);
    
    // Obtener el ID del productor insertado
    $productor_id = $pdo->lastInsertId();

    // --------------------------------------------------
    // II. INSERTAR DATOS DEL PREDIO (Tabla: predios)
    // --------------------------------------------------
    
    // Obtener y sanitizar datos del predio
    $nombre_predio          = trim(strtoupper($_POST['nombre_predio'] ?? ''));
    $hectareas              = floatval($_POST['hectareas'] ?? 0.0);
    
    // Las checkboxes no enviadas son NULL, por lo que se convierten a 0
    $posee_casa             = isset($_POST['posee_casa']) ? 1 : 0;
    $posee_tanque           = isset($_POST['posee_tanque']) ? 1 : 0;
    $posee_pozos            = isset($_POST['posee_pozos']) ? 1 : 0;
    $posee_corral           = isset($_POST['posee_corral']) ? 1 : 0;
    $posee_perimetral       = isset($_POST['posee_perimetral']) ? 1 : 0;
    $posee_barbacoa         = isset($_POST['posee_barbacoa']) ? 1 : 0;
    $registro_inti          = isset($_POST['registro_inti']) ? 1 : 0;
    $registro_hierro        = isset($_POST['registro_hierro']) ? 1 : 0;
    $servicio_agua          = isset($_POST['servicio_agua']) ? 1 : 0;
    $servicio_gas           = isset($_POST['servicio_gas']) ? 1 : 0;
    $servicio_electricidad  = isset($_POST['servicio_electricidad']) ? 1 : 0;
    $servicio_internet      = isset($_POST['servicio_internet']) ? 1 : 0;
    $servicio_transporte    = isset($_POST['servicio_transporte']) ? 1 : 0;
    $servicio_maquinaria    = isset($_POST['servicio_maquinaria']) ? 1 : 0;

    $sql_predio = "INSERT INTO predios (
        productor_id, nombre_predio, hectareas, posee_casa, posee_tanque, 
        posee_pozos, posee_corral, posee_perimetral, posee_barbacoa, 
        registro_inti, registro_hierro, servicio_agua, servicio_gas, 
        servicio_electricidad, servicio_internet, servicio_transporte, servicio_maquinaria
    ) VALUES (
        :productor_id, :nombre_predio, :hectareas, :posee_casa, :posee_tanque, 
        :posee_pozos, :posee_corral, :posee_perimetral, :posee_barbacoa, 
        :registro_inti, :registro_hierro, :servicio_agua, :servicio_gas, 
        :servicio_electricidad, :servicio_internet, :servicio_transporte, :servicio_maquinaria
    )";

    $stmt_predio = $pdo->prepare($sql_predio);
    $stmt_predio->execute([
        'productor_id'          => $productor_id,
        'nombre_predio'         => $nombre_predio,
        'hectareas'             => $hectareas,
        'posee_casa'            => $posee_casa,
        'posee_tanque'          => $posee_tanque,
        'posee_pozos'           => $posee_pozos,
        'posee_corral'          => $posee_corral,
        'posee_perimetral'      => $posee_perimetral,
        'posee_barbacoa'        => $posee_barbacoa,
        'registro_inti'         => $registro_inti,
        'registro_hierro'       => $registro_hierro,
        'servicio_agua'         => $servicio_agua,
        'servicio_gas'          => $servicio_gas,
        'servicio_electricidad' => $servicio_electricidad,
        'servicio_internet'     => $servicio_internet,
        'servicio_transporte'   => $servicio_transporte,
        'servicio_maquinaria'   => $servicio_maquinaria,
    ]);
    
    // Obtener el ID del predio insertado
    $predio_id = $pdo->lastInsertId();

    // --------------------------------------------------------
    // III. INSERTAR ACTIVIDAD AGRÍCOLA (Tabla: agricultura)
    // --------------------------------------------------------
    
    $tipo_cultivo           = trim(strtoupper($_POST['tipo_cultivo'] ?? ''));
    $area_cultivada         = trim(strtoupper($_POST['area_cultivada'] ?? ''));
    $tiempo_sembrado        = trim(strtoupper($_POST['tiempo_sembrado'] ?? ''));
    $canal_comercializacion = trim(strtoupper($_POST['canal_comercializacion'] ?? ''));
    $cultivo_principal      = trim(strtoupper($_POST['cultivo_principal'] ?? ''));
    $cultivo_secundario     = trim(strtoupper($_POST['cultivo_secundario'] ?? ''));
    $venta_producto         = trim(strtoupper($_POST['venta_producto'] ?? ''));
    
    $sql_agricultura = "INSERT INTO agricultura (
        predio_id, tipo_cultivo, area_cultivada, tiempo_sembrado, 
        canal_comercializacion, cultivo_principal, cultivo_secundario, venta_producto
    ) VALUES (
        :predio_id, :tipo_cultivo, :area_cultivada, :tiempo_sembrado, 
        :canal_comercializacion, :cultivo_principal, :cultivo_secundario, :venta_producto
    )";
    
    $stmt_agricultura = $pdo->prepare($sql_agricultura);
    $stmt_agricultura->execute([
        'predio_id'              => $predio_id,
        'tipo_cultivo'           => $tipo_cultivo,
        'area_cultivada'         => $area_cultivada,
        'tiempo_sembrado'        => $tiempo_sembrado,
        'canal_comercializacion' => $canal_comercializacion,
        'cultivo_principal'      => $cultivo_principal,
        'cultivo_secundario'     => $cultivo_secundario,
        'venta_producto'         => $venta_producto
    ]);
    
    // -----------------------------------------------------
    // IV. INSERTAR GANADERÍA/ANIMALES (Tabla: ganaderia)
    // -----------------------------------------------------

    $cantidades_ganado = [
        'cant_vaca'              => intval($_POST['cant_vaca'] ?? 0),
        'cant_toro'              => intval($_POST['cant_toro'] ?? 0),
        'cant_novillo'           => intval($_POST['cant_novillo'] ?? 0),
        'cant_becerros'          => intval($_POST['cant_becerros'] ?? 0),
        'cant_bufalo'            => intval($_POST['cant_bufalo'] ?? 0),
        'cant_chivo'             => intval($_POST['cant_chivo'] ?? 0),
        'cant_cabra'             => intval($_POST['cant_cabra'] ?? 0),
        'cant_ovejo'             => intval($_POST['cant_ovejo'] ?? 0),
        'cant_verraco'           => intval($_POST['cant_verraco'] ?? 0),
        'cant_lechones'          => intval($_POST['cant_lechones'] ?? 0),
        'cant_pollo_engorde'     => intval($_POST['cant_pollo_engorde'] ?? 0),
        'cant_gallinas_ponedoras'=> intval($_POST['cant_gallinas_ponedoras'] ?? 0),
        'cant_gallinas_patio'    => intval($_POST['cant_gallinas_patio'] ?? 0),
        'cant_alevines'          => intval($_POST['cant_alevines'] ?? 0),
        'cant_peces'             => intval($_POST['cant_peces'] ?? 0),
        // Campos de la DB no presentes en el form, se setean a 0
        'cant_máticas'           => 0,
        'cant_mautes'            => 0,
        'cant_becerras'          => 0,
        'cant_bufala'            => 0,
        'cant_oveja'             => 0,
        'cant_cerda_madre'       => 0,
        'cant_levantes'          => 0,
        'cant_reproductores'     => 0,
    ];

    $sql_ganaderia = "INSERT INTO ganaderia (
        predio_id, cant_vaca, cant_toro, cant_novillo, cant_máticas, 
        cant_mautes, cant_becerros, cant_becerras, cant_bufalo, cant_bufala, 
        cant_chivo, cant_cabra, cant_ovejo, cant_oveja, cant_verraco, 
        cant_cerda_madre, cant_levantes, cant_lechones, cant_pollo_engorde, 
        cant_gallinas_ponedoras, cant_gallinas_patio, cant_alevines, cant_peces, 
        cant_reproductores
    ) VALUES (
        :predio_id, :cant_vaca, :cant_toro, :cant_novillo, :cant_máticas, 
        :cant_mautes, :cant_becerros, :cant_becerras, :cant_bufalo, :cant_bufala, 
        :cant_chivo, :cant_cabra, :cant_ovejo, :cant_oveja, :cant_verraco, 
        :cant_cerda_madre, :cant_levantes, :cant_lechones, :cant_pollo_engorde, 
        :cant_gallinas_ponedoras, :cant_gallinas_patio, :cant_alevines, :cant_peces, 
        :cant_reproductores
    )";
    
    $stmt_ganaderia = $pdo->prepare($sql_ganaderia);
    $stmt_ganaderia->execute(array_merge(['predio_id' => $predio_id], $cantidades_ganado));

    // ------------------------------------------------------------
    // V. INSERTAR RECOMENDACIONES (Tabla: recomendaciones)
    // ------------------------------------------------------------

    $recomendaciones = trim($_POST['recomendaciones'] ?? '');
    $fecha_registro = date('Y-m-d');
    
    $sql_recomendaciones = "INSERT INTO recomendaciones (
        predio_id, recomendaciones, fecha_registro
    ) VALUES (
        :predio_id, :recomendaciones, :fecha_registro
    )";

    $stmt_recomendaciones = $pdo->prepare($sql_recomendaciones);
    $stmt_recomendaciones->execute([
        'predio_id' => $predio_id,
        'recomendaciones' => $recomendaciones,
        'fecha_registro' => $fecha_registro
    ]);

    // -----------------------------------------------------------
    // VI. INSERTAR CARGA FAMILIAR (Tabla: carga_familiar)
    // -----------------------------------------------------------

    if (isset($_POST['fam_nombre']) && is_array($_POST['fam_nombre'])) {
        $nombres = $_POST['fam_nombre'];
        $edades = $_POST['fam_edad'];
        $cedulas = $_POST['fam_cedula'];
        $parentescos = $_POST['fam_parentesco'];
        $ocupaciones = $_POST['fam_ocupacion'];
        
        $sql_familiar = "INSERT INTO carga_familiar (
            productor_id, nombre, edad, cedula, parentesco, ocupacion
        ) VALUES (
            :productor_id, :nombre, :edad, :cedula, :parentesco, :ocupacion
        )";
        $stmt_familiar = $pdo->prepare($sql_familiar);

        foreach ($nombres as $index => $fam_nombre) {
            // Se asume que los arrays están sincronizados
            $fam_nombre_val    = trim(strtoupper($fam_nombre));
            $fam_edad_val      = intval($edades[$index] ?? 0);
            $fam_cedula_val    = trim($cedulas[$index] ?? '');
            $fam_parentesco_val= trim(strtoupper($parentescos[$index] ?? ''));
            $fam_ocupacion_val = trim(strtoupper($ocupaciones[$index] ?? ''));
            
            // Solo insertar si al menos el nombre no está vacío
            if (!empty($fam_nombre_val)) {
                $stmt_familiar->execute([
                    'productor_id' => $productor_id,
                    'nombre'       => $fam_nombre_val,
                    'edad'         => $fam_edad_val,
                    'cedula'       => $fam_cedula_val,
                    'parentesco'   => $fam_parentesco_val,
                    'ocupacion'    => $fam_ocupacion_val,
                ]);
            }
        }
    }

    // 7. Si todo sale bien, confirmar los cambios
    $pdo->commit();
    
    // Redirigir con mensaje de éxito
    header("Location: ../inscripcion_censo.php?exito=1");
    exit();

} catch (Exception $e) {
    // 8. Si algo falla, revertir los cambios
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Redirigir con mensaje de error
    // En un entorno de producción, es mejor no mostrar $e->getMessage() directamente
    error_log("Error al guardar el censo: " . $e->getMessage()); 
    header("Location: inscripcion_censo.php?error=1");
    exit();
}
?>
// filepath: c:\xampp\htdocs\IPAUPMA\bd\guardar_censo.php
<?php
session_start();
// 1. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 2. Incluir conexión (ruta relativa al propio archivo)
require_once __DIR__ . '/conexion.php';

// 3. Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../inscripcion_censo.php");
    exit();
}

try {
    // 4. Conectar a la base de datos
    $pdo = conexion::conectar();
    
    // Iniciar transacción: crucial para asegurar que si una parte falla, todo se revierte
    if ($pdo instanceof PDO) {
        $pdo->beginTransaction();
    }

    // ----------------------------------------------------
    // I. INSERTAR DATOS DEL PRODUCTOR (Tabla: productores)
    // ----------------------------------------------------
    
    // Limpiar y obtener datos del productor
    $parroquia_id       = $_POST['parroquia_id'] ?? null;
    $nombre             = trim(strtoupper($_POST['nombre'] ?? ''));
    $sector             = trim(strtoupper($_POST['sector'] ?? ''));
    $apellido           = trim(strtoupper($_POST['apellido'] ?? ''));
    $cedula             = trim($_POST['cedula'] ?? '');
    $rif                = trim(strtoupper($_POST['rif'] ?? ''));
    $fecha_nacimiento   = $_POST['fecha_nacimiento'] ?? null;
    $grado_instruccion  = $_POST['grado_instruccion'] ?? null;
    $oficio_ocupacion   = trim(strtoupper($_POST['oficio_ocupacion'] ?? ''));
    $telefono           = trim($_POST['telefono'] ?? '');
    
    $sql_productor = "INSERT INTO productores (
        parroquia_id, nombre, sector, apellido, cedula, rif, 
        fecha_nacimiento, grado_instruccion, oficio_ocupacion, telefono
    ) VALUES (
        :parroquia_id, :nombre, :sector, :apellido, :cedula, :rif, 
        :fecha_nacimiento, :grado_instruccion, :oficio_ocupacion, :telefono
    )";

    $stmt_productor = $pdo->prepare($sql_productor);
    $stmt_productor->execute([
        'parroquia_id'      => $parroquia_id,
        'nombre'            => $nombre,
        'sector'            => $sector,
        'apellido'          => $apellido,
        'cedula'            => $cedula,
        'rif'               => $rif,
        'fecha_nacimiento'  => $fecha_nacimiento ?: null,
        'grado_instruccion' => $grado_instruccion,
        'oficio_ocupacion'  => $oficio_ocupacion,
        'telefono'          => $telefono
    ]);
    
    // Obtener el ID del productor insertado
    $productor_id = $pdo->lastInsertId();

    // --------------------------------------------------
    // II. INSERTAR DATOS DEL PREDIO (Tabla: predios)
    // --------------------------------------------------
    
    // Obtener y sanitizar datos del predio
    $nombre_predio          = trim(strtoupper($_POST['nombre_predio'] ?? ''));
    $hectareas              = floatval($_POST['hectareas'] ?? 0.0);
    
    // Las checkboxes no enviadas son NULL, por lo que se convierten a 0
    $posee_casa             = isset($_POST['posee_casa']) ? 1 : 0;
    $posee_tanque           = isset($_POST['posee_tanque']) ? 1 : 0;
    $posee_pozos            = isset($_POST['posee_pozos']) ? 1 : 0;
    $posee_corral           = isset($_POST['posee_corral']) ? 1 : 0;
    $posee_perimetral       = isset($_POST['posee_perimetral']) ? 1 : 0;
    $posee_barbacoa         = isset($_POST['posee_barbacoa']) ? 1 : 0;
    $registro_inti          = isset($_POST['registro_inti']) ? 1 : 0;
    $registro_hierro        = isset($_POST['registro_hierro']) ? 1 : 0;
    $servicio_agua          = isset($_POST['servicio_agua']) ? 1 : 0;
    $servicio_gas           = isset($_POST['servicio_gas']) ? 1 : 0;
    $servicio_electricidad  = isset($_POST['servicio_electricidad']) ? 1 : 0;
    $servicio_internet      = isset($_POST['servicio_internet']) ? 1 : 0;
    $servicio_transporte    = isset($_POST['servicio_transporte']) ? 1 : 0;
    $servicio_maquinaria    = isset($_POST['servicio_maquinaria']) ? 1 : 0;

    $sql_predio = "INSERT INTO predios (
        productor_id, nombre_predio, hectareas, posee_casa, posee_tanque, 
        posee_pozos, posee_corral, posee_perimetral, posee_barbacoa, 
        registro_inti, registro_hierro, servicio_agua, servicio_gas, 
        servicio_electricidad, servicio_internet, servicio_transporte, servicio_maquinaria
    ) VALUES (
        :productor_id, :nombre_predio, :hectareas, :posee_casa, :posee_tanque, 
        :posee_pozos, :posee_corral, :posee_perimetral, :posee_barbacoa, 
        :registro_inti, :registro_hierro, :servicio_agua, :servicio_gas, 
        :servicio_electricidad, :servicio_internet, :servicio_transporte, :servicio_maquinaria
    )";

    $stmt_predio = $pdo->prepare($sql_predio);
    $stmt_predio->execute([
        'productor_id'          => $productor_id,
        'nombre_predio'         => $nombre_predio,
        'hectareas'             => $hectareas,
        'posee_casa'            => $posee_casa,
        'posee_tanque'          => $posee_tanque,
        'posee_pozos'           => $posee_pozos,
        'posee_corral'          => $posee_corral,
        'posee_perimetral'      => $posee_perimetral,
        'posee_barbacoa'        => $posee_barbacoa,
        'registro_inti'         => $registro_inti,
        'registro_hierro'       => $registro_hierro,
        'servicio_agua'         => $servicio_agua,
        'servicio_gas'          => $servicio_gas,
        'servicio_electricidad' => $servicio_electricidad,
        'servicio_internet'     => $servicio_internet,
        'servicio_transporte'   => $servicio_transporte,
        'servicio_maquinaria'   => $servicio_maquinaria,
    ]);
    
    // Obtener el ID del predio insertado
    $predio_id = $pdo->lastInsertId();

    // --------------------------------------------------------
    // III. INSERTAR ACTIVIDAD AGRÍCOLA (Tabla: agricultura)
    // --------------------------------------------------------
    
    $tipo_cultivo           = trim(strtoupper($_POST['tipo_cultivo'] ?? ''));
    $area_cultivada         = trim(strtoupper($_POST['area_cultivada'] ?? ''));
    $tiempo_sembrado        = trim(strtoupper($_POST['tiempo_sembrado'] ?? ''));
    $canal_comercializacion = trim(strtoupper($_POST['canal_comercializacion'] ?? ''));
    $cultivo_principal      = trim(strtoupper($_POST['cultivo_principal'] ?? ''));
    $cultivo_secundario     = trim(strtoupper($_POST['cultivo_secundario'] ?? ''));
    $venta_producto         = trim(strtoupper($_POST['venta_producto'] ?? ''));
    
    $sql_agricultura = "INSERT INTO agricultura (
        predio_id, tipo_cultivo, area_cultivada, tiempo_sembrado, 
        canal_comercializacion, cultivo_principal, cultivo_secundario, venta_producto
    ) VALUES (
        :predio_id, :tipo_cultivo, :area_cultivada, :tiempo_sembrado, 
        :canal_comercializacion, :cultivo_principal, :cultivo_secundario, :venta_producto
    )";
    
    $stmt_agricultura = $pdo->prepare($sql_agricultura);
    $stmt_agricultura->execute([
        'predio_id'              => $predio_id,
        'tipo_cultivo'           => $tipo_cultivo,
        'area_cultivada'         => $area_cultivada,
        'tiempo_sembrado'        => $tiempo_sembrado,
        'canal_comercializacion' => $canal_comercializacion,
        'cultivo_principal'      => $cultivo_principal,
        'cultivo_secundario'     => $cultivo_secundario,
        'venta_producto'         => $venta_producto
    ]);
    
    // -----------------------------------------------------
    // IV. INSERTAR GANADERÍA/ANIMALES (Tabla: ganaderia)
    // -----------------------------------------------------

    $cantidades_ganado = [
        'cant_vaca'              => intval($_POST['cant_vaca'] ?? 0),
        'cant_toro'              => intval($_POST['cant_toro'] ?? 0),
        'cant_novillo'           => intval($_POST['cant_novillo'] ?? 0),
        'cant_becerros'          => intval($_POST['cant_becerros'] ?? 0),
        'cant_bufalo'            => intval($_POST['cant_bufalo'] ?? 0),
        'cant_chivo'             => intval($_POST['cant_chivo'] ?? 0),
        'cant_cabra'             => intval($_POST['cant_cabra'] ?? 0),
        'cant_ovejo'             => intval($_POST['cant_ovejo'] ?? 0),
        'cant_verraco'           => intval($_POST['cant_verraco'] ?? 0),
        'cant_lechones'          => intval($_POST['cant_lechones'] ?? 0),
        'cant_pollo_engorde'     => intval($_POST['cant_pollo_engorde'] ?? 0),
        'cant_gallinas_ponedoras'=> intval($_POST['cant_gallinas_ponedoras'] ?? 0),
        'cant_gallinas_patio'    => intval($_POST['cant_gallinas_patio'] ?? 0),
        'cant_alevines'          => intval($_POST['cant_alevines'] ?? 0),
        'cant_peces'             => intval($_POST['cant_peces'] ?? 0),
        // Campos de la DB no presentes en el form, se setean a 0
        'cant_máticas'           => 0,
        'cant_mautes'            => 0,
        'cant_becerras'          => 0,
        'cant_bufala'            => 0,
        'cant_oveja'             => 0,
        'cant_cerda_madre'       => 0,
        'cant_levantes'          => 0,
        'cant_reproductores'     => 0,
    ];

    $sql_ganaderia = "INSERT INTO ganaderia (
        predio_id, cant_vaca, cant_toro, cant_novillo, cant_máticas, 
        cant_mautes, cant_becerros, cant_becerras, cant_bufalo, cant_bufala, 
        cant_chivo, cant_cabra, cant_ovejo, cant_oveja, cant_verraco, 
        cant_cerda_madre, cant_levantes, cant_lechones, cant_pollo_engorde, 
        cant_gallinas_ponedoras, cant_gallinas_patio, cant_alevines, cant_peces, 
        cant_reproductores
    ) VALUES (
        :predio_id, :cant_vaca, :cant_toro, :cant_novillo, :cant_máticas, 
        :cant_mautes, :cant_becerros, :cant_becerras, :cant_bufalo, :cant_bufala, 
        :cant_chivo, :cant_cabra, :cant_ovejo, :cant_oveja, :cant_verraco, 
        :cant_cerda_madre, :cant_levantes, :cant_lechones, :cant_pollo_engorde, 
        :cant_gallinas_ponedoras, :cant_gallinas_patio, :cant_alevines, :cant_peces, 
        :cant_reproductores
    )";
    
    $stmt_ganaderia = $pdo->prepare($sql_ganaderia);
    $stmt_ganaderia->execute(array_merge(['predio_id' => $predio_id], $cantidades_ganado));

    // ------------------------------------------------------------
    // V. INSERTAR RECOMENDACIONES (Tabla: recomendaciones)
    // ------------------------------------------------------------

    $recomendaciones = trim($_POST['recomendaciones'] ?? '');
    $fecha_registro = date('Y-m-d');
    
    $sql_recomendaciones = "INSERT INTO recomendaciones (
        predio_id, recomendaciones, fecha_registro
    ) VALUES (
        :predio_id, :recomendaciones, :fecha_registro
    )";

    $stmt_recomendaciones = $pdo->prepare($sql_recomendaciones);
    $stmt_recomendaciones->execute([
        'predio_id' => $predio_id,
        'recomendaciones' => $recomendaciones,
        'fecha_registro' => $fecha_registro
    ]);

    // -----------------------------------------------------------
    // VI. INSERTAR CARGA FAMILIAR (Tabla: carga_familiar)
    // -----------------------------------------------------------

    if (isset($_POST['fam_nombre']) && is_array($_POST['fam_nombre'])) {
        $nombres = $_POST['fam_nombre'];
        $edades = $_POST['fam_edad'];
        $cedulas = $_POST['fam_cedula'];
        $parentescos = $_POST['fam_parentesco'];
        $ocupaciones = $_POST['fam_ocupacion'];
        
        $sql_familiar = "INSERT INTO carga_familiar (
            productor_id, nombre, edad, cedula, parentesco, ocupacion
        ) VALUES (
            :productor_id, :nombre, :edad, :cedula, :parentesco, :ocupacion
        )";
        $stmt_familiar = $pdo->prepare($sql_familiar);

        foreach ($nombres as $index => $fam_nombre) {
            // Se asume que los arrays están sincronizados
            $fam_nombre_val    = trim(strtoupper($fam_nombre));
            $fam_edad_val      = intval($edades[$index] ?? 0);
            $fam_cedula_val    = trim($cedulas[$index] ?? '');
            $fam_parentesco_val= trim(strtoupper($parentescos[$index] ?? ''));
            $fam_ocupacion_val = trim(strtoupper($ocupaciones[$index] ?? ''));
            
            // Solo insertar si al menos el nombre no está vacío
            if (!empty($fam_nombre_val)) {
                $stmt_familiar->execute([
                    'productor_id' => $productor_id,
                    'nombre'       => $fam_nombre_val,
                    'edad'         => $fam_edad_val,
                    'cedula'       => $fam_cedula_val,
                    'parentesco'   => $fam_parentesco_val,
                    'ocupacion'    => $fam_ocupacion_val,
                ]);
            }
        }
    }

    // 7. Si todo sale bien, confirmar los cambios
    $pdo->commit();
    
    // Redirigir con mensaje de éxito
    header("Location: ../inscripcion_censo.php?exito=1");
    exit();

} catch (Exception $e) {
    // 8. Si algo falla, revertir los cambios
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Redirigir con mensaje de error
    // En un entorno de producción, es mejor no mostrar $e->getMessage() directamente
    error_log("Error al guardar el censo: " . $e->getMessage()); 
    header("Location: inscripcion_censo.php?error=1");
    exit();
}
?>