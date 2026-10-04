<?php
// Servira audio fajlove domaceg (uputstvo profesora i odgovor djaka) iz storage-a
// admin aplikacije, sa proverom da je dete roditeljevo i da fajl pripada domacem
// tog deteta.
// ?file=naziv.mp4&tip=profesor|djaci&djak_id=X
// 🔴 NEMA echo/HTML pre header() poziva

include_once "config/core.php";
include_once "config/database.php";
include_once __DIR__ . '/config/require_login.php';
include_once "config/autoload.php";
// postavlja $moj_id samo ako je ?djak_id= aktivno povezan sa ulogovanim roditeljem
include_once "config/resolve_djak.php";

$dozvoljeni_tipovi = ['profesor', 'djaci'];
$tip  = isset($_GET['tip'])  && in_array($_GET['tip'], $dozvoljeni_tipovi) ? $_GET['tip'] : '';
$file = isset($_GET['file']) ? basename($_GET['file']) : '';

if (!$tip || !$file) {
    http_response_code(400);
    exit('Neispravan zahtev.');
}

// Fajl mora biti iz domaceg koji dete vidi: za 'profesor' uputstvo zadatka,
// za 'djaci' odgovor bas tog deteta - isti spisak kao u dashboard.php (tab Domaci).
$db = (new Database())->getConnection();
$domaci_obj = new domaci_audio($db);
$kolona = $tip === 'profesor' ? 'audio_filename' : 'odgovor_filename';

$dozvoljen = false;
foreach ($domaci_obj->read_visible_for_djak($moj_id)->fetchAll(PDO::FETCH_ASSOC) as $da) {
    if (!empty($da[$kolona]) && $da[$kolona] === $file) {
        $dozvoljen = true;
        break;
    }
}

if (!$dozvoljen) {
    http_response_code(404);
    exit('Fajl nije pronađen.');
}

// DOCUMENTS_ROOT u .env pokazuje na root admin app-a (gde je storage)
$putanja = rtrim($_ENV['DOCUMENTS_ROOT'], '/\\') . '/storage/domaci/' . $tip . '/' . $file;

if (!file_exists($putanja)) {
    http_response_code(404);
    exit('Fajl nije pronađen.');
}

$ext_mapa = [
    'mp4'  => 'audio/mp4',
    'webm' => 'audio/webm',
    'ogg'  => 'audio/ogg',
    'mp3'  => 'audio/mpeg',
];

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$content_type = $ext_mapa[$ext] ?? 'application/octet-stream';

// Cisti sve output buffere da ne korumpira audio stream.
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($putanja));
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=3600');

readfile($putanja);
exit;
