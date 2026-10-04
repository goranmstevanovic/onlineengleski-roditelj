<?php
/**
 * Pokretanje sesije — ukljucuje se umesto session_start().
 *
 * A) Svaka skola i aplikacija ima svoj kolacic sesije, da se sesije ne mesaju
 *    (npr. admin i roditeljski portal na istom domenu, ili dve skole na localhost-u).
 *    Ime je SESSION_NAME iz .env; ako ga nema, pravi se iz DB_NAME (npr. SMSsrbroditelj).
 * B) Sesija pamti kojoj skoli i aplikaciji pripada; tudja sesija se prazni.
 */
require_once __DIR__ . '/../vendor/autoload.php';
if (!isset($_ENV['DB_NAME'])) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
}

$sesija_tip   = 'roditelj';
$sesija_skola = (string) ($_ENV['DB_NAME'] ?? '');

if (session_status() === PHP_SESSION_NONE) {
    // Kolacic sesije: JavaScript ne moze da ga procita (HttpOnly), ne salje se uz zahteve
    // sa drugih sajtova (SameSite=Lax), a kad sajt radi na HTTPS-u ide samo preko HTTPS-a (Secure).
    $sesija_https = strpos((string) ($_ENV['APP_URL'] ?? ''), 'https://') === 0
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $sesija_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    // ne prihvata ID sesije koji server nije sam napravio (zastita od podmetanja sesije)
    ini_set('session.use_strict_mode', '1');
    $sesija_ime = !empty($_ENV['SESSION_NAME']) ? $_ENV['SESSION_NAME'] : 'SMS' . $sesija_skola . $sesija_tip;
    session_name(preg_replace('/[^A-Za-z0-9]/', '', (string) $sesija_ime));
    session_start();
}

$sesija_oznaka = $sesija_skola . '/' . $sesija_tip;
if (($_SESSION['_skola'] ?? null) !== $sesija_oznaka) {
    if (!empty($_SESSION)) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['_skola'] = $sesija_oznaka;
}
