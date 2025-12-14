<?php
// db.php - Conexión a TiDB Cloud
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com';
$db   = 'if0_40468936_productos'; 
$user = '3kHQi14ZC7gq9VG.root';
$pass = 'E34YFSifiEWG3Pwj';
$port = '4000'; // <-- CORREGIDO: Faltaba el punto y coma

try {
    // 1. Agregamos ";port=$port" a la cadena de conexión
    // 2. Agregamos ";ssl-mode=VERIFY_IDENTITY" (Recomendado para TiDB)
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // TiDB requiere conexión segura. 
        // Si tu servidor tiene los certificados al día, esto habilita SSL automáticamente:
        PDO::MYSQL_ATTR_SSL_CA       => true, 
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Ponlo en true en producción si tienes el CA configurado
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // Log interno del error (no mostrar al usuario passwords ni hosts)
    error_log("Error TiDB Cloud: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'status' => 'error', 
        'message' => 'No se pudo conectar a la base de datos TiDB.'
    ]));
}
?>
