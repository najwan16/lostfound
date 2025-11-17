<?php
// config/Mail.php (DI LUAR app/)

class Mail
{
    public static function send($to, $subject, $bodyHtml, $bodyText = '')
    {
        // PATH DARI config/ → ke root → vendor/
        require_once __DIR__ . '/../vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lostfoundfilkom@gmail.com';
            $mail->Password = 'qdgg rdey pdxk oevz';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('lostfoundfilkom@gmail.com', 'Lost & Found FILKOM UB');
            $mail->addReplyTo('lostfoundfilkom@gmail.com', 'Tim Lost & Found');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = $bodyText ?: strip_tags($bodyHtml);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email gagal: {$mail->ErrorInfo}");
            return false;
        }
    }
}
