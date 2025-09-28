<?php
namespace App\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../assets/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../assets/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/vendor/phpmailer/src/SMTP.php';
//require_once __DIR__ . '/../config/env_loader.php'; agregar este al utilizar la clase

class Mailer
{
    public static function send(
        string $to,
        string $toName,
        string $subject,
        string $body,
        ?string $replyTo = null,
        ?string $replyName = null,
        array $attachments = []   // 🔹 nuevo parámetro
    ): bool|string {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP desde .env
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'ssl')
                                    ? PHPMailer::ENCRYPTION_SMTPS
                                    : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $_ENV['SMTP_PORT'];

            // Remitente
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);

            // Destinatario
            $mail->addAddress($to, $toName);

            // Responder a
            if (!empty($replyTo)) {
                $mail->addReplyTo($replyTo, $replyName ?? $replyTo);
            }

            // Adjuntos
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    $mail->addAttachment($file);
                }
            }

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;

        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
}
