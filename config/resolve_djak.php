<?php
/**
 * Ocekuje da su vec ukljuceni: config/core.php, config/database.php,
 * config/autoload.php, login_checker.php (roditelj ulogovan).
 *
 * Cita ?djak_id=X iz URL-a i postavlja $aktuelni_djak / $idd / $moj_id na tog
 * djaka - ali SAMO ako je aktivnom vezom u roditelji_djaci stvarno povezan sa
 * ulogovanim roditeljem ($_SESSION['user_id']). Ako nije (tudje dete, obrisana
 * veza, nepostojeci id...), preusmerava na index (izbor deteta) - roditelj
 * nikad ne moze da vidi podatke djaka koji nije njegov, bez obzira sta upise
 * u URL.
 */

$djak_id_zahtev = isset($_GET['djak_id']) ? (int)$_GET['djak_id'] : 0;

if($djak_id_zahtev <= 0){
    header("Location: {$home_url}index?action=izaberi_dete");
    exit;
}

$database_rd = new Database();
$db_rd = $database_rd->getConnection();

$stmt_provera_rd = $db_rd->prepare("
    SELECT dj.id, dj.firstname, dj.lastname
    FROM roditelji_djaci rd
    INNER JOIN djaci dj ON dj.id = rd.fk_djak
    WHERE rd.fk_roditelj = :fk_roditelj AND rd.fk_djak = :fk_djak AND rd.active = 1 AND dj.status = 1
    LIMIT 1
");
$stmt_provera_rd->execute([
    ':fk_roditelj' => $_SESSION['user_id'],
    ':fk_djak' => $djak_id_zahtev,
]);

if($stmt_provera_rd->rowCount() !== 1){
    header("Location: {$home_url}index?action=nepoznato_dete");
    exit;
}

$aktuelno_dete = $stmt_provera_rd->fetch(PDO::FETCH_ASSOC);

// imena promenljivih poklapaju se sa onim sto vec ocekuju kopirane
// avalon-student stranice (nasledjeno od admin-verzije, gde je $idd dolazio
// iz $_GET['id']) - tako da ostatak stranice radi bez daljih izmena
$aktuelni_djak = $djak_id_zahtev;
$idd = $djak_id_zahtev;
$moj_id = $djak_id_zahtev;
