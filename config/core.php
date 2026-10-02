<?php

ob_start();

require_once __DIR__ . '/sesija.php';

// Load .env
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// App settings
define("nastavak", $_ENV['APP_PATH']);
date_default_timezone_set($_ENV['APP_TIMEZONE']);

$site     = $_SERVER['DOCUMENT_ROOT'] . nastavak;
$home_url = $_ENV['APP_URL'];

// Posiljalac i potpis svih mejlova (menja se samo u .env)
define('POSILJALAC',     $_ENV['MAIL_POSILJALAC'] ?? '');
define('POTPISNIK',      $_ENV['MAIL_POTPISNIK'] ?? '');
define('POTPIS_TELEFON', $_ENV['MAIL_POTPIS_TELEFON'] ?? '');
define('POTPIS_SAJT',    $_ENV['MAIL_POTPIS_SAJT'] ?? '');
// putanja do loga za mejl, relativno od root foldera aplikacije (prazno = bez loga)
define('MAIL_LOGO',      $_ENV['MAIL_LOGO'] ?? 'images/mail_logo.png');

// Potpis za kraj mejla (tekst): potpisnik, pa telefon i sajt ako su popunjeni — svaki u svom redu
function mail_potpis(): string
{
    $redovi = array_filter([
        POTPISNIK,
        POTPIS_TELEFON !== '' ? 'tel: ' . POTPIS_TELEFON : '',
        POTPIS_SAJT,
    ], 'strlen');
    return implode("\n", $redovi);
}

// Isti potpis za HTML mejl; sajt je link
function mail_potpis_html(): string
{
    $redovi = array_filter([
        htmlspecialchars(POTPISNIK),
        POTPIS_TELEFON !== '' ? 'tel: ' . htmlspecialchars(POTPIS_TELEFON) : '',
        POTPIS_SAJT !== '' ? '<a href="' . htmlspecialchars(POTPIS_SAJT) . '">' . htmlspecialchars(POTPIS_SAJT) . '</a>' : '',
    ], 'strlen');
    return implode('<br>', $redovi);
}

// Logo na vrhu HTML mejla. MAIL_LOGO moze biti URL (http/https - slika se ucitava sa te
// adrese) ili putanja do fajla od root foldera (ugradjuje se u sam mejl (cid), pa ne zavisi
// od domena i prikazuje se bez "prikazi slike"). Prazno ako logo nije podesen ili fajl ne postoji.
function mail_logo_html($mailer): string
{
    if (MAIL_LOGO === '') {
        return '';
    }
    if (preg_match('#^https?://#i', MAIL_LOGO)) {
        $src = htmlspecialchars(MAIL_LOGO);
    } else {
        $fajl = dirname(__DIR__) . '/' . ltrim(MAIL_LOGO, '/');
        if (!is_file($fajl)) {
            return '';
        }
        $mailer->addEmbeddedImage($fajl, 'mail_logo');
        $src = 'cid:mail_logo';
    }
    return "<p align='center'><img width='180' src='" . $src . "' alt='" . htmlspecialchars(POSILJALAC) . "'></p>";
}

// fotografije polaznika — deljeni folder sa avalon-student aplikacijom
$student_photos_path = $_ENV['STUDENT_PHOTOS_PATH'];
$student_photos_url  = $_ENV['STUDENT_PHOTOS_URL'];

$super_admin = [1, 104, 132];

// Pagination
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$records_per_page = 9999;
$from_record_num = ($records_per_page * $page) - $records_per_page;
