<?php
/**
 * SUPER BASIC TEST - NO INCLUDES
 */
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo json_encode([
    'success' => true,
    'message' => 'Basic PHP works',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>