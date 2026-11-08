<?php
/*
 * ARCHIVO DE INTEGRACIÓN — NO ES LA CONEXIÓN REAL DEL EQUIPO.
 * Sustituir este archivo por conexion.php de José antes de probar con MySQL.
 */
$host = 'localhost';
$dbname = 'tienda_ube';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("No fue posible conectar con la base de datos. Configure conexion.php del equipo.");
}
?>
