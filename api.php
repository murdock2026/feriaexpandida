<?php
// api.php - QASO SYSTEM v7.0 (Google Bridge Integration)
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1. SEGURIDAD Y CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Cache-Control: no-store, no-cache, must-revalidate");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once 'db.php'; // Asegúrate de que este archivo tenga tus claves de BD

// --- 2. CONFIGURACIÓN DEL PUENTE (TU URL GENERADA) ---
define('GOOGLE_BRIDGE_URL', 'https://script.google.com/macros/s/AKfycbyIufDyIiPjXIRJdWsRVfVWK2NaRhYEQNoow0PTHUZbwMchN3EqUto9J582dyteYpVb/exec');

// --- 3. FUNCIÓN DE ENVÍO VÍA GOOGLE ---
function enviarNotificacion($mensaje) {
    // Preparamos los datos que Google Script espera recibir
    $data = json_encode(['mensaje' => $mensaje]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_BRIDGE_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones de Google
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Vital para InfinityFree
    
    // Google necesita saber que le enviamos JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

try {
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // --- AUTENTICACIÓN ---

    if ($action === 'check_session') {
        jsonResponse([
            'logged_in' => isset($_SESSION['user_id']),
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ]);
    }

    if ($action === 'login' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true);
        if (empty($in['username']) || empty($in['password'])) throw new Exception("Datos incompletos");

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$in['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($in['password'], $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            jsonResponse(['status' => 'success']);
        }
        jsonResponse(['status' => 'error', 'message' => 'Credenciales inválidas'], 401);
    }

    if ($action === 'register' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true);
        $u = trim($in['username']??''); $p = $in['password']??'';
        if (strlen($u)<3 || strlen($p)<4) throw new Exception("Usuario (min 3) o Pass (min 4) muy cortos");
        
        try {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'PUBLIC')");
            $stmt->execute([$u, $hash]);
            jsonResponse(['status' => 'success']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) throw new Exception("El usuario ya existe");
            throw $e;
        }
    }

    if ($action === 'logout') { session_destroy(); jsonResponse(['status' => 'success']); }

    // --- TIENDA ---

    if ($action === 'get_catalog') {
        // Admin ve todo, Cliente solo stock disponible
        $sql = "SELECT codigo, nombre, stock_actual, precio_venta, unidad FROM products WHERE stock_actual > 0";
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
            $sql = "SELECT * FROM products ORDER BY nombre ASC";
        }
        jsonResponse($pdo->query($sql)->fetchAll());
    }

    // --- PROCESAR COMPRA (CON NOTIFICACIÓN) ---
    if ($action === 'registrar_movimiento' && $method === 'POST') {
        if (!isset($_SESSION['user_id'])) jsonResponse(['status'=>'error', 'message'=>'Inicia sesión para comprar'], 401);
        
        $in = json_decode(file_get_contents('php://input'), true);
        $codigo = $in['codigo'] ?? '';
        $cantidad = floatval($in['cantidad'] ?? 0);
        
        // Datos de contacto
        $telefono = $in['telefono'] ?? 'No indicado';
        $direccion = $in['direccion'] ?? 'No indicada';
        $contactoStr = "Tel: $telefono | Dir: $direccion";

        if (!$codigo || $cantidad <= 0) throw new Exception("Error en datos del producto");

        $pdo->beginTransaction();

        // 1. Bloquear y Verificar Stock
        $stmt = $pdo->prepare("SELECT id, nombre, stock_actual, precio_venta, unidad FROM products WHERE codigo = ? FOR UPDATE");
        $stmt->execute([$codigo]);
        $prod = $stmt->fetch();

        if (!$prod || $prod['stock_actual'] < $cantidad) {
            $pdo->rollBack(); throw new Exception("Stock insuficiente o agotado");
        }

        // 2. Calcular
        $newStock = $prod['stock_actual'] - $cantidad;
        $total = $cantidad * $prod['precio_venta'];
        
        // 3. Actualizar Stock
        $pdo->prepare("UPDATE products SET stock_actual = ? WHERE id = ?")->execute([$newStock, $prod['id']]);
        
        // 4. Guardar Movimiento
        $pdo->prepare("INSERT INTO movements (product_id, user_id, tipo, cantidad, precio_unitario, valor_total, datos_contacto, observaciones) VALUES (?, ?, 'SALIDA', ?, ?, ?, ?, 'Venta Web')")
            ->execute([$prod['id'], $_SESSION['user_id'], $cantidad, $prod['precio_venta'], $total, $contactoStr]);

        $pdo->commit();

        // 5. ENVIAR A TELEGRAM (VÍA GOOGLE)
        $msg = "🚀 <b>¡NUEVO PEDIDO CONFIRMADO!</b>\n\n";
        $msg .= "👤 <b>Cliente:</b> " . $_SESSION['username'] . "\n";
        $msg .= "📦 <b>Producto:</b> " . $prod['nombre'] . "\n";
        $msg .= "🔢 <b>Cantidad:</b> " . $cantidad . " " . ($prod['unidad']??'u') . "\n";
        $msg .= "💰 <b>Total:</b> $" . number_format($total, 2) . "\n";
        $msg .= "--------------------------------\n";
        $msg .= "📍 <b>Dirección:</b> " . $direccion . "\n";
        $msg .= "📞 <b>Contacto:</b> " . $telefono;

        enviarNotificacion($msg);

        jsonResponse(['status' => 'success', 'message' => 'Pedido confirmado. Te contactaremos pronto.']);
    }

    // --- HISTORIAL ---
    if ($action === 'get_my_purchases') {
        if (!isset($_SESSION['user_id'])) jsonResponse([], 401);
        $stmt = $pdo->prepare("SELECT m.fecha, p.nombre as producto, m.cantidad, m.valor_total FROM movements m JOIN products p ON m.product_id = p.id WHERE m.user_id = ? AND m.tipo='SALIDA' ORDER BY m.fecha DESC");
        $stmt->execute([$_SESSION['user_id']]);
        jsonResponse($stmt->fetchAll());
    }

    // --- ADMIN ---
    if ($action === 'admin_save_product' && $method === 'POST') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') jsonResponse(['status'=>'error'], 403);
        $in = json_decode(file_get_contents('php://input'), true);
        $id = $in['id'] ?? null;
        if($id){
            $pdo->prepare("UPDATE products SET codigo=?, nombre=?, unidad=?, grupo=?, stock_actual=?, precio_venta=?, stock_min=? WHERE id=?")
                ->execute([$in['codigo'], $in['nombre'], $in['unidad'], $in['grupo'], $in['stock_actual'], $in['precio_venta'], $in['stock_min'], $id]);
        } else {
            $pdo->prepare("INSERT INTO products (codigo, nombre, unidad, grupo, stock_actual, precio_venta, stock_min) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$in['codigo'], $in['nombre'], $in['unidad'], $in['grupo'], $in['stock_actual'], $in['precio_venta'], $in['stock_min']]);
        }
        jsonResponse(['status' => 'success', 'message' => 'Guardado']);
    }

    if ($action === 'admin_delete_product' && $method === 'POST') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') jsonResponse(['status'=>'error'], 403);
        $in = json_decode(file_get_contents('php://input'), true);
        $pdo->prepare("DELETE FROM movements WHERE product_id = ?")->execute([$in['id']]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$in['id']]);
        jsonResponse(['status' => 'success', 'message' => 'Eliminado']);
    }

    throw new Exception("Acción desconocida");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
