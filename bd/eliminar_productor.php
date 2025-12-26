<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'error' => 'ID de productor inválido']);
    exit;
}

try {
    $obj = new conexion();
    $pdo = $obj->conectar();
    if (!$pdo instanceof PDO) throw new Exception('DB no disponible');

    $pdo->beginTransaction();

    // 1. Eliminar CARGA FAMILIAR (depende de productor_id)
    $stmt = $pdo->prepare("DELETE FROM carga_familiar WHERE productor_id = ?");
    $stmt->execute([$id]);

    // 2. Obtener TODOS los predios del productor (no solo uno)
    $stmt = $pdo->prepare("SELECT id FROM predios WHERE productor_id = ?");
    $stmt->execute([$id]);
    $predios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Para cada predio, eliminar agricultura, ganadería y recomendaciones
    foreach ($predios as $predio) {
        $predio_id = $predio['id'];
        
        // Eliminar AGRICULTURA
        $stmt = $pdo->prepare("DELETE FROM agricultura WHERE predio_id = ?");
        $stmt->execute([$predio_id]);

        // Eliminar GANADERÍA
        $stmt = $pdo->prepare("DELETE FROM ganaderia WHERE predio_id = ?");
        $stmt->execute([$predio_id]);
        
        // Eliminar RECOMENDACIONES (que también depende de predio_id)
        $stmt = $pdo->prepare("DELETE FROM recomendaciones WHERE predio_id = ?");
        $stmt->execute([$predio_id]);

        // Eliminar PREDIO
        $stmt = $pdo->prepare("DELETE FROM predios WHERE id = ?");
        $stmt->execute([$predio_id]);
    }

    // 4. Finalmente, eliminar PRODUCTOR
    $stmt = $pdo->prepare("DELETE FROM productores WHERE id = ?");
    $stmt->execute([$id]);

    // Verificar si se eliminó algo
    if ($stmt->rowCount() === 0) {
        throw new Exception('Productor no encontrado o ya eliminado');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Productor eliminado correctamente']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('eliminar_productor error: ' . $e->getMessage());
    // Retornar el mensaje de error específico para depuración
    echo json_encode([
        'success' => false, 
        'error' => 'Error al eliminar el productor: ' . $e->getMessage(),
        'error_debug' => $e->getFile() . ':' . $e->getLine()
    ]);
    exit;
}
?> 