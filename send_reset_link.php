<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include_once "config/core.php";
include_once "config/database.php";

require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require "config/mail.php";

$db = (new Database())->getConnection();

$email = trim($_POST['email'] ?? '');

if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Nevalidan email.");
}

$stmt = $db->prepare("
    SELECT id, firstname, email
    FROM roditelji
    WHERE email = :email AND active = 1
    LIMIT 1
");
$stmt->execute([':email' => $email]);

// UVEK redirect (security)
if($stmt->rowCount() !== 1){
    header("Location: forgot_password.php?sent=1");
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// TOKEN
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// snimi
$upd = $db->prepare("
    UPDATE roditelji
    SET password_reset_token = :token,
        password_reset_expires = :expires
    WHERE id = :id
");
$upd->execute([
    ':token' => $token,
    ':expires' => $expires,
    ':id' => $user['id']
]);

$link = $home_url . "reset_password.php?token=" . urlencode($token);

// MAIL
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['encryption'];
    $mail->Port = $mailConfig['port'];
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($user['email'], $user['firstname']);

    $mail->isHTML(true);
    $mail->Subject = 'Reset lozinke';

    $posiljalac_html = htmlspecialchars(POSILJALAC);
    $potpis_html     = mail_potpis_html();

    $mail->Body = "
        <h2>{$posiljalac_html}</h2>
        <p>Zdravo {$user['firstname']},</p>
        <p>Kliknite ispod da resetujete lozinku:</p>
        <p>
            <a href='{$link}' style='background:#dc3545;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>
                Resetuj lozinku
            </a>
        </p>
        <p>Link važi 1 sat.</p>
        <hr>
        <small>{$potpis_html}</small>
    ";

    $mail->send();

} catch (Exception $e) {
    error_log("MAIL ERROR: " . $mail->ErrorInfo);
}

// uvek redirect
header("Location: forgot_password.php?sent=1");
exit;
