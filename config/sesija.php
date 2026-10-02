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
