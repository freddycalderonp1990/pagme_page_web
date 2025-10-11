<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../config/env_loader.php';



// Responder JSON
$response= json_encode([
    "client_id" => $_ENV["PAYPAL_CLIENT_ID"] ?? null,
    "ambiente"      => $_ENV["AMBIENTE"] ?? "dev",
    "email_test"      => $_ENV["MAIL_FROM_TEST"] ?? "freddycalderon1990@gmail.com",
]);


echo $response;

