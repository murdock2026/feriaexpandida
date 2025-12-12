<?php
// db.php - Conexión a InfinityFree
$host = 'gateway01.us-east-1.prod.aws.tidbcloud.com'; // CAMBIAR POR TU HOSTNAME
$db   = 'if0_40468936_productos';      // CAMBIAR POR TU DB NAME
$user = '3kHQi14ZC7gq9VG.root';              // CAMBIAR POR TU USERNAME
$pass = 'E34YFSifiEWG3Pwj';     // CAMBIAR POR TU PASS
$port = '4000' // DIRECCION DEL PUERTO

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En producción no mostramos el error real al usuario, solo un mensaje genérico
    // y registramos el error en un log (error_log)
    error_log("Error DB: " . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión al servidor']));
}
?>
