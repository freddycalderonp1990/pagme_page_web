<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/env_loader.php';




// Responder JSON
$response= json_encode([
    "client_id" => $_ENV["PAYPAL_CLIENT_ID"] ?? null,
    "mode"      => $_ENV["PAYPAL_MODE"] ?? "sandbox"
]);


echo $response;

