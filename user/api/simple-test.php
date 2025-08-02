<?php
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Simple test working!',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>