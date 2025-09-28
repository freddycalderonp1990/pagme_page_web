<?php
// Habilitar errores (solo mientras pruebas)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../assets/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/../assets/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../assets/vendor/phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? 'Nuevo mensaje';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP (Gmail en este ejemplo)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'freddycalderon1990@gmail.com'; // tu correo
        $mail->Password   = 'fcqy eziv tanb feno'; // contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom($email, $name);
        $mail->addAddress('freddycalderon1990@gmail.com'); // destino (tu mismo)

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <h3>Nuevo mensaje desde la web de PagMe</h3>
            <p><strong>Nombre:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Mensaje:</strong><br>{$message}</p>
        ";

        $mail->send();
        echo 'OK'; // esto activa el mensaje de éxito en el frontend
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Error: no se enviaron datos.";
}
