<?php
// api.php - QASO SYSTEM v3.0 (Con Panel Admin)
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

require_once 'db.php'; // Asegúrate de que db.php tenga tus credenciales correctas

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Helper de respuesta
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Helper de Seguridad Admin
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
        jsonResponse(['status' => 'error', 'message' => 'Acceso denegado. Requiere permisos de Administrador.'], 403);
    }
}

try {
    if ($method === 'OPTIONS') exit;

    // --- AUTH BÁSICA ---

    if ($action === 'check_session') {
        jsonResponse([
            'logged_in' => isset($_SESSION['user_id']),
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ]);
    }

    if ($action === 'login' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true);
        $u = trim($in['username'] ?? '');
        $p = $in['password'] ?? '';

        if (!$u || !$p) throw new Exception("Datos incompletos");

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$u]);
        $user = $stmt->fetch();

        if ($user && password_verify($p, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            jsonResponse(['status' => 'success', 'role' => $user['role']]);
        }
        jsonResponse(['status' => 'error', 'message' => 'Credenciales inválidas'], 401);
    }

    if ($action === 'register' && $method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true);
        $u = trim($in['username'] ?? '');
        $p = $in['password'] ?? '';
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

    // --- TIENDA PÚBLICA ---

    if ($action === 'get_catalog') {
        // Público solo ve lo que tiene stock > 0
        $sql = "SELECT codigo, nombre, stock_actual, precio_venta, unidad FROM products WHERE stock_actual > 0";
        // Si es admin pidiendo catálogo, mostramos TODO (incluso sin stock)
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN') {
            $sql = "SELECT * FROM products ORDER BY nombre ASC";
        }
        jsonResponse($pdo->query($sql)->fetchAll());
    }

    if ($action === 'registrar_movimiento' && $method === 'POST') {
        if (!isset($_SESSION['user_id'])) throw new Exception("Sesión requerida");
        $in = json_decode(file_get_contents('php://input'), true);
        
        $pdo->beginTransaction();
        // ... (Lógica de compra anterior mantenida igual por brevedad, funciona OK) ...
        // Bloqueo
        $stmt = $pdo->prepare("SELECT id, nombre, stock_actual, precio_venta FROM products WHERE codigo = ? FOR UPDATE");
        $stmt->execute([$in['codigo']]);
        $prod = $stmt->fetch();

        if (!$prod || $prod['stock_actual'] < $in['cantidad']) {
            $pdo->rollBack(); throw new Exception("Stock insuficiente");
        }
        
        $newStock = $prod['stock_actual'] - $in['cantidad'];
        $total = $in['cantidad'] * $prod['precio_venta'];
        
        $pdo->prepare("UPDATE products SET stock_actual = ? WHERE id = ?")->execute([$newStock, $prod['id']]);
        
        $contact = "Tel: " . ($in['telefono']??'-') . " | Dir: " . ($in['direccion']??'-');
        $pdo->prepare("INSERT INTO movements (product_id, user_id, tipo, cantidad, precio_unitario, valor_total, datos_contacto) VALUES (?, ?, 'SALIDA', ?, ?, ?, ?)")
            ->execute([$prod['id'], $_SESSION['user_id'], $in['cantidad'], $prod['precio_venta'], $total, $contact]);
        
        $pdo->commit();
        
        // Mail (Simplificado)
        @mail("acoop-com5@hotmail.com", "Venta QASO", "Venta: {$prod['nombre']} Total: $total User: {$_SESSION['username']}");
        
        jsonResponse(['status' => 'success']);
    }

    if ($action === 'get_my_purchases') {
        if (!isset($_SESSION['user_id'])) jsonResponse([], 401);
        $stmt = $pdo->prepare("SELECT m.fecha, p.nombre as producto, m.cantidad, m.valor_total FROM movements m JOIN products p ON m.product_id = p.id WHERE m.user_id = ? AND m.tipo='SALIDA' ORDER BY m.fecha DESC");
        $stmt->execute([$_SESSION['user_id']]);
        jsonResponse($stmt->fetchAll());
    }

    // --- PANEL ADMINISTRADOR (NUEVO) ---

    // 1. GUARDAR PRODUCTO (Crear o Editar)
    if ($action === 'admin_save_product' && $method === 'POST') {
        requireAdmin();
        $in = json_decode(file_get_contents('php://input'), true);
        
        // Validaciones
        if (empty($in['codigo']) || empty($in['nombre'])) throw new Exception("Código y Nombre obligatorios");

        $id = $in['id'] ?? null; // Si hay ID es editar, si no es crear
        
        if ($id) {
            // EDITAR
            $sql = "UPDATE products SET codigo=?, nombre=?, unidad=?, grupo=?, stock_actual=?, precio_venta=?, stock_min=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$in['codigo'], $in['nombre'], $in['unidad'], $in['grupo'], $in['stock_actual'], $in['precio_venta'], $in['stock_min'], $id]);
            $msg = "Producto actualizado";
        } else {
            // CREAR
            $sql = "INSERT INTO products (codigo, nombre, unidad, grupo, stock_actual, precio_venta, stock_min) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$in['codigo'], $in['nombre'], $in['unidad'], $in['grupo'], $in['stock_actual'], $in['precio_venta'], $in['stock_min']]);
            $msg = "Producto creado";
        }
        jsonResponse(['status' => 'success', 'message' => $msg]);
    }

    // 2. BORRAR PRODUCTO
    if ($action === 'admin_delete_product' && $method === 'POST') {
        requireAdmin();
        $in = json_decode(file_get_contents('php://input'), true);
        
        // Primero borramos movimientos para mantener integridad (CASCADE manual por seguridad)
        $pdo->prepare("DELETE FROM movements WHERE product_id = ?")->execute([$in['id']]);
        // Luego borramos producto
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$in['id']]);
        
        jsonResponse(['status' => 'success', 'message' => 'Producto eliminado correctamente']);
    }

    throw new Exception("Acción no encontrada");

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>