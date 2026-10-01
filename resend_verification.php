<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "config/core.php";
include_once "config/database.php";

require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// učitaj mail config
$mailConfig = require "config/mail.php";

$db = (new Database())->getConnection();

$email = trim($_GET['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Nevalidan zahtev.");
}

// pronađi roditelja
$stmt = $db->prepare("
    SELECT id, firstname, email, verification_token, email_verified_at, active
    FROM roditelji
    WHERE email = :email
    LIMIT 1
");
$stmt->execute([':email' => $email]);

if ($stmt->rowCount() !== 1) {
    die("Korisnik nije pronađen.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// neaktivan nalog
if ((int)$user['active'] !== 1) {
    die("Nalog nije aktivan.");
}

// već verifikovan
if (!empty($user['email_verified_at'])) {
    header("Location: login.php?action=email_verified");
    exit;
}

// token
$token = $user['verification_token'];

if (empty($token)) {
    $token = bin2hex(random_bytes(32));

    $upd = $db->prepare("
        UPDATE roditelji
        SET verification_token = :token
        WHERE id = :id
    ");
    $upd->execute([
        ':token' => $token,
        ':id' => $user['id']
    ]);
}

// link
$link = $home_url . "verify_email.php?token=" . urlencode($token);

// slanje maila
$mail = new PHPMailer(true);
$mail->SMTPDebug = 2;
$mail->Debugoutput = function($str, $level) {
    error_log("SMTP DEBUG [$level]: $str");
};

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

    $mail->setFrom(
        $mailConfig['from_email'],
        $mailConfig['from_name']
    );

    $mail->addAddress($user['email'], $user['firstname']);

    $mail->isHTML(true);
    $mail->Subject = 'Verifikacija email adrese';

    $posiljalac_html = htmlspecialchars(POSILJALAC);
    $potpis_html     = mail_potpis_html();
    $logo_html       = mail_logo_html($mail);

    $mail->Body = "
        {$logo_html}
        <h2>{$posiljalac_html}</h2>

        <p>Pozdrav {$user['firstname']},</p>

        <p>Da biste aktivirali svoj roditeljski nalog, potrebno je da verifikujete email adresu.</p>

        <p>
            <a href='{$link}' style='
                background:#007bff;
                color:white;
                padding:10px 15px;
                text-decoration:none;
                border-radius:5px;
            '>
                Verifikuj email
            </a>
        </p>

        <p style='color:gray'>
            Ako niste vi poslali ovaj zahtev, slobodno ignorišite ovu poruku.
        </p>

        <hr>
        <small>{$potpis_html}</small>
    ";

    error_log("Pokusaj slanja maila na: " . $user['email']);
    error_log("Token: " . $token);
    error_log("Link: " . $link);

    $mail->send();

    header("Location: login.php?action=verification_sent");
    exit;

} catch (Exception $e) {
    echo "Greška pri slanju emaila: " . $mail->ErrorInfo;
    $errorMsg = "MAIL ERROR: " . $mail->ErrorInfo;

    error_log($errorMsg);

    echo "Došlo je do greške pri slanju emaila.";
}
