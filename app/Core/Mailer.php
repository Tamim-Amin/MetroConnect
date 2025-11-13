<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function send(string $to, string $subject, string $body): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST') ?: 'smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('MAIL_USER') ?: '';
            $mail->Password = getenv('MAIL_PASS') ?: '';
            $mail->Port = (int)(getenv('MAIL_PORT') ?: 2525);

            $mail->setFrom(getenv('MAIL_FROM') ?: 'no-reply@example.test', getenv('MAIL_FROM_NAME') ?: 'AuthBoard');
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);

            return $mail->send();
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $e->getMessage());
            return false;
        }
    }
}
