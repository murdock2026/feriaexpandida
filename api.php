<?php
// api.php - QASO SYSTEM v7.0 (Google Bridge Integration)
// Configuración final para Koyeb + TiDB + Google Apps Script

session_start();
header('Content-Type: application/json; charset=utf-8');

// --- 1. SEGURIDAD Y CORS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Cache-Control: no-store, no-cache, must-revalidate");

// Responder OK a pre-flight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php'; // Conexión a TiDB

// --- 2. CONFIGURACIÓN DEL PUENTE (TU URL) ---
// Esta es la URL que generaste en Google Apps Script
define('GOOGLE_BRIDGE_URL', 'https://script.google.com/macros/s/AKfycbwIuQNgcPcc3Pbcg0nRRe3u0ouZv_UHLlwtT1eKL_HubtfNQYwqXuIJqN0LzEF955PZ/exec');

// --- 3. FUNCIONES AUXILIARES ---

function enviarNotificacion($mensaje) {
    // Si la URL está vacía, no enviamos nada
    if (!defined('GOOGLE_BRIDGE_URL') || empty(GOOGLE_BRIDGE_URL)) return;

    $data = json_encode(['mensaje' => $mensaje]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GOOGLE_BRIDGE_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita errores de certificado en CURL
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

// --- 4. LÓGICA PRINCIPAL ---

try {
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Leer JSON de entrada
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // --- AUTH ---
    if ($action === 'check_session') {
        jsonResponse([
            'logged_in' => isset($_SESSION['user_id']),
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ]);
    }

    if ($action === 'login' && $method === 'POST') {
        if (empty($input['username']) || empty($input['password'])) {
            throw new Exception("Datos incompletos");
        }
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($input['password'], $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            jsonResponse(['status' => 'success', 'role' => $user['role']]);
        }
        jsonResponse(['status' => 'error', 'message' => 'Credenciales inválidas'], 401);
    }

    if ($action === 'register' && $method === 'POST') {
        $u = trim($input['username'] ?? '');
        $p = $input['password'] ?? '';
        if (strlen($u) < 3 || strlen($p) < 4) throw new Exception("Mínimo 3 caracteres usuario, 4 pass");
        
        try {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'PUBLIC')");
            $stmt->execute([$u, $hash]);
            jsonResponse(['status' => 'success']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) throw new Exception("Usuario ya existe");
            throw $e;
        }
    }

    if ($action === 'logout') {
        session_destroy();
        jsonResponse(['status' => 'success']);
    }

    // --- TIENDA ---
    if ($action === 'get_catalog') {
        $sql = "SELECT codigo, nombre, stock_actual, precio_venta, unidad, grupo FROM products WHERE stock_actual > 0";
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
            $sql = "SELECT * FROM products ORDER BY nombre ASC";
        }
        jsonResponse($pdo->query($sql)->fetchAll());
    }

    // --- COMPRA CON TELEGRAM ---
    if ($action === 'registrar_movimiento' && $method === 'POST') {
        if (!isset($_SESSION['user_id'])) jsonResponse(['status' => 'error', 'message' => 'Sesión requerida'], 401);
        
        $codigo = $input['codigo'] ?? '';
        $cantidad = floatval($input['cantidad'] ?? 0);
        $telefono = $input['telefono'] ?? '-';
        $direccion = $input['direccion'] ?? '-';

        if (!$codigo || $cantidad <= 0) throw new Exception("Datos inválidos");

        $pdo->beginTransaction();

        // Verificar stock y bloquear fila
        $stmt = $pdo->prepare("SELECT id, nombre, stock_actual, precio_venta, unidad FROM products WHERE codigo = ? FOR UPDATE");
        $stmt->execute([$codigo]);
        $prod = $stmt->fetch();

        if (!$prod || $prod['stock_actual'] < $cantidad) {
            $pdo->rollBack();
            throw new Exception("Stock insuficiente");
        }

        // Actualizar DB
        $newStock = $prod['stock_actual'] - $cantidad;
        $total = $cantidad * $prod['precio_venta'];
        $pdo->prepare("UPDATE products SET stock_actual = ? WHERE id = ?")->execute([$newStock, $prod['id']]);
        
        $contacto = "Tel: $telefono | Dir: $direccion";
        $pdo->prepare("INSERT INTO movements (product_id, user_id, tipo, cantidad, precio_unitario, valor_total, datos_contacto, observaciones) VALUES (?, ?, 'SALIDA', ?, ?, ?, ?, 'Web')")
            ->execute([$prod['id'], $_SESSION['user_id'], $cantidad, $prod['precio_venta'], $total, $contacto]);

        $pdo->commit();

        // Enviar a Telegram
        $msg = "🚀 <b>¡NUEVA VENTA!</b>\n\n";
        $msg .= "👤 <b>Cliente:</b> " . $_SESSION['username'] . "\n";
        $msg .= "📦 <b>Producto:</b> " . $prod['nombre'] . "\n";
        $msg .= "🔢 <b>Cant:</b> " . $cantidad . " " . ($prod['unidad']??'u') . "\n";
        $msg .= "💰 <b>Total:</b> $" . number_format($total, 2) . "\n";
        $msg .= "📍 <b>Envío:</b> " . $direccion . "\n";
        $msg .= "📞 <b>Tel:</b> " . $telefono;
        
        enviarNotificacion($msg);

        jsonResponse(['status' => 'success', 'message' => 'Compra exitosa']);
    }

    if ($action === 'get_my_purchases') {
        if (!isset($_SESSION['user_id'])) jsonResponse([], 401);
        $stmt = $pdo->prepare("SELECT m.fecha, p.nombre as producto, m.cantidad, m.valor_total FROM movements m JOIN products p ON m.product_id = p.id WHERE m.user_id = ? AND m.tipo='SALIDA' ORDER BY m.fecha DESC");
        $stmt->execute([$_SESSION['user_id']]);
        jsonResponse($stmt->fetchAll());
    }

    // --- PANEL ADMIN ---
    $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN');

    if ($action === 'admin_save_product' && $method === 'POST') {
        if (!$isAdmin) jsonResponse(['status' => 'error', 'message' => 'No autorizado'], 403);
        
        $id = $input['id'] ?? null;
        if ($id) {
            $pdo->prepare("UPDATE products SET codigo=?, nombre=?, unidad=?, grupo=?, stock_actual=?, precio_venta=?, stock_min=? WHERE id=?")
                ->execute([$input['codigo'], $input['nombre'], $input['unidad'], $input['grupo'], $input['stock_actual'], $input['precio_venta'], $input['stock_min'], $id]);
        } else {
            $pdo->prepare("INSERT INTO products (codigo, nombre, unidad, grupo, stock_actual, precio_venta, stock_min) VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$input['codigo'], $input['nombre'], $input['unidad'], $input['grupo'], $input['stock_actual'], $input['precio_venta'], $input['stock_min']]);
        }
        jsonResponse(['status' => 'success', 'message' => 'Guardado']);
    }

    if ($action === 'admin_delete_product' && $method === 'POST') {
        if (!$isAdmin) jsonResponse(['status' => 'error', 'message' => 'No autorizado'], 403);
        $id = $input['id'];
        $pdo->prepare("DELETE FROM movements WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        jsonResponse(['status' => 'success', 'message' => 'Eliminado']);
    }

    throw new Exception("Acción desconocida");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
