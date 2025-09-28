<?php
require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../email/Mailer.php';

use App\Email\Mailer;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? 'Nuevo mensaje';
    $message = $_POST['message'] ?? '';

    // Cuerpo del correo en HTML
    $body = "
        <h3>Nuevo mensaje desde la web de PagMe</h3>
        <p><strong>Nombre:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Mensaje:</strong><br>{$message}</p>
    ";

    // Usar clase genérica Mailer
    $result = Mailer::send(
        $_ENV['MAIL_TO'],          // destinatario principal
        "Soporte PagMe",           // nombre destinatario
        $subject,                  // asunto
        $body,                     // contenido HTML
        $email,                    // reply-to (para responder al usuario)
        $name                      // nombre del usuario
    );

    echo $result === true ? 'OK' : "Error: $result";
} else {
    echo "Error: no se enviaron datos.";
}
