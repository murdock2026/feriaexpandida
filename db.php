<?php
// db.php - Conexión corregida para TiDB / Remote SQL
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com';
$db   = 'if0_40468936_productos'; // Asegúrate que este nombre de DB exista en TiDB
$user = '3kHQi14ZC7gq9VG.root';
$pass = 'E34YFSifiEWG3Pwj';
$port = '4000'; // FALTABA EL PUNTO Y COMA AQUÍ

try {
    // AGREGAMOS ";port=$port" DENTRO DE LAS COMILLAS
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Loguear error real en el servidor
    error_log("Error de conexión SQL: " . $e->getMessage());
    
    // Devolver JSON de error para que el frontend lo entienda
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    die(json_encode(['status' => 'error', 'message' => 'Fallo la conexión a la Base de Datos']));
}
?>
