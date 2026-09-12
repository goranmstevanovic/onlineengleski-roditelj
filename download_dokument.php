<?php
// VAZNO: provera vlasnistva nad detetom (?djak_id=) MORA ovde, pre bilo kakvog
// izlaza - videti config/resolve_djak.php i belesku u dashboard.php (tab Dokumenti)
// 🔴 NEMA error_reporting OVDE, 🔴 NEMA echo/HTML pre header() poziva

while (ob_get_level()) {
    ob_end_clean();
}

include_once "config/core.php";
include_once "config/database.php";
include_once __DIR__ . '/config/require_login.php';
include_once "config/autoload.php";
include_once "config/resolve_djak.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    exit;
}

$db = (new Database())->getConnection();

// $moj_id je vec postavljen u config/resolve_djak.php, na osnovu ?djak_id=
// - isti uslov pristupa kao u dashboard.php (tab Dokumenti), da roditelj ne moze
// preuzeti dokument koji nije deljen sa njegovim detetom, bez obzira sta upise
// u URL.
$stmt = $db->prepare("
    SELECT d.putanja, d.originalni_naziv, d.ekstenzija, dp.pravo_pregled, dp.pravo_download
    FROM dokumenti d
    JOIN dokument_pristup dp ON dp.fk_dokument = d.id
    WHERE d.id = :id
      AND d.aktivan = 1
      AND dp.tip_pristupa = 'djak'
      AND dp.fk_entitet = :djak_id
    LIMIT 1
");
$stmt->execute([':id' => $id, ':djak_id' => $moj_id]);

$dokument = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dokument || (!$dokument['pravo_pregled'] && !$dokument['pravo_download'])) {
    http_response_code(404);
    exit;
}

$path = rtrim($_ENV['DOCUMENTS_ROOT'], '/\\') . '/' . $dokument['putanja'];

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

$ext = strtolower($dokument['ekstenzija']);

switch ($ext) {
    case 'jpg':
    case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    case 'png':
        $contentType = 'image/png';
        break;
    case 'pdf':
        $contentType = 'application/pdf';
        break;
    case 'doc':
        $contentType = 'application/msword';
        break;
    case 'docx':
        $contentType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;
    case 'xls':
        $contentType = 'application/vnd.ms-excel';
        break;
    case 'xlsx':
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        break;
    default:
        $contentType = 'application/octet-stream';
}

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($path));

if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
    header('Content-Disposition: inline; filename="' . basename($dokument['originalni_naziv']) . '"');
} else {
    header('Content-Disposition: attachment; filename="' . basename($dokument['originalni_naziv']) . '"');
}

readfile($path);
exit;
