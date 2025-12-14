<?php
// db.php - Versión Corregida y Universal
// Intenta leer variables de entorno (Koyeb), si no existen, usa las fijas.

$host = getenv('DB_HOST') ?: 'gateway01.us-east-1.prod.aws.tidbcloud.com';
$db   = getenv('DB_NAME') ?: 'if0_40468936_productos';
$user = getenv('DB_USER') ?: '3kHQi14ZC7gq9VG.root';
$pass = getenv('DB_PASS') ?: 'E34YFSifiEWG3Pwj';
$port = getenv('DB_PORT') ?: '4000'; // <--- ¡EL PUNTO Y COMA IMPORTANTE ESTÁ AQUÍ!

try {
    // Cadena de conexión con Puerto y SSL activado
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_SSL_CA       => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Si falla, muestra error 500
    error_log("Error de conexión: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']));
}
?>
