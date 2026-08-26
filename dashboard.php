<?php
// VAZNO: provera vlasnistva nad detetom (?djak_id=) MORA ovde, pre bilo kakvog
// HTML izlaza - videti config/resolve_djak.php i belesku u kartica_djak.php
include_once "config/core.php";
include_once "login_checker.php";
include_once "config/database.php";
include_once "config/autoload.php";
include_once "config/resolve_djak.php";
?>
<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="images/kalen.png" type="image/png"/>
	<!--plugins-->
	<link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet"/>
	<!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet"/>
	<script src="assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="assets/css/dark-theme.css"/>
	<link rel="stylesheet" href="assets/css/semi-dark.css"/>
	<link rel="stylesheet" href="assets/css/header-colors.css"/>
	<title>SMS</title>
	<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
	<style>
	/* Tabovi — uvijek jedan red, ne prelama na mobilnom */
	#materijaliTab {
		flex-wrap: nowrap;
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		scrollbar-width: none;
	}
	#materijaliTab::-webkit-scrollbar { display: none; }
	#materijaliTab .nav-link { white-space: nowrap; }
	@media (max-width: 575px) {
		#materijaliTab .nav-link {
			font-size: 0.72rem;
			padding: 7px 8px;
		}
		#materijaliTab .nav-link .bx {
			font-size: 0.85rem !important;
		}
	}

	/* Quill editor unutar kartica — spriječi prelijevanje */
	.esej-quill-editor, .video-quill-editor { width: 100%; min-width: 0; }
	.esej-quill-editor .ql-toolbar.ql-snow,
	.video-quill-editor .ql-toolbar.ql-snow {
		border-radius: 4px 4px 0 0;
		padding: 4px 6px;
		line-height: 1;
	}
	.esej-quill-editor .ql-toolbar.ql-snow button,
	.video-quill-editor .ql-toolbar.ql-snow button {
		height: 22px;
		width: 24px;
		padding: 1px 3px;
		float: none;
		display: inline-block;
	}
	.esej-quill-editor .ql-container.ql-snow,
	.video-quill-editor .ql-container.ql-snow {
		border-radius: 0 0 4px 4px;
		font-size: 13px;
	}
	.esej-quill-editor .ql-editor,
	.video-quill-editor .ql-editor {
		min-height: 80px;
	}
	</style>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper" >
		<!--sidebar wrapper -->
		
        <?php
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);  
		
 		include_once "login_checker.php"; 
		
			// 	$site = $_SERVER['DOCUMENT_ROOT'] . "/sms/";
			// include_once ($site."/config/core.php");
			// include_once ($site."/config/database.php");

			// include_once ($site."/config/autoload.php");
			// $slika_put = $home_url."/assets/images/sms1.png";
			// $database = new Database();
			// $db = $database->getConnection();
			// $lokacija = new lokacija($db);
		//	include_once ( "config/core.php");

        include_once  $site.'/app/sidebar.php'; 
          
        
        
        
        ?>
		<!--end sidebar wrapper -->
		<!--start header -->
      
		<header>
		<?php   include __DIR__ . '/app/header.php'; ?>
		</header>
				<!--end header -->
		
		
				<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<?php
					function formatSize($bytes) {
						if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
						if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
						if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
						return $bytes . ' B';
					}

					error_reporting(E_ALL);
					ini_set('display_errors', 1);

					include_once "config/core.php";
					include_once "login_checker.php";
					include_once 'config/database.php';
					include_once 'config/autoload.php'; // za kviz/pitanje/odgovor/kviz_pokusaj/kviz_odgovor objekte
					include_once 'config/funkcije.php'; // video_embed_url() - za iframe prikaz video materijala

					$database = new Database();
					$db = $database->getConnection();

					include_once 'config/app_settings.php'; // $domaci_zadaci_enabled

					// $moj_id je vec postavljen na vrhu (config/resolve_djak.php), na osnovu ?djak_id=

					// ===================== KVIZ - logika =====================
					$kviz_obj = new kviz($db);
					$pitanje_obj = new pitanje($db);
					$odgovor_obj = new odgovor($db);
					$kviz_pokusaj_obj = new kviz_pokusaj($db);
					$kviz_odgovor_obj = new kviz_odgovor($db);

					// Boduje poslate odgovore i zavrsava pokusaj (koristi se i kod predaje i kod automatske predaje po isteku vremena)
					function kviz_oceni_i_zavrsi($pitanje_obj, $odgovor_obj, $kviz_odgovor_obj, $kviz_pokusaj_obj, $pokusaj_id, $fk_kviz, $odgovori_post, $dopuna_post = [], $spajanje_post = [])
					{
						$pitanja_lista = $pitanje_obj->read_by_kviz($fk_kviz)->fetchAll(PDO::FETCH_ASSOC);

						$bodovi = 0;
						$max_bodovi = 0;

						foreach ($pitanja_lista as $p) {
							$max_bodovi += (int)$p['poeni'];

							if ($p['tip_pitanja'] === 'dopuna') {

								// dopuna - svaka praznina se ocenjuje posebno, poredjenje sa varijantama razdvojenim sa '|'
								foreach ($odgovor_obj->read_by_pitanje($p['id'])->fetchAll(PDO::FETCH_ASSOC) as $o) {
									$unet = isset($dopuna_post[$p['id']][$o['redosled']]) ? trim($dopuna_post[$p['id']][$o['redosled']]) : '';

									$tacno = 0;
									if ($unet !== '') {
										foreach (explode('|', $o['tekst_odgovora']) as $varijanta) {
											if (mb_strtolower(trim($varijanta)) === mb_strtolower($unet)) {
												$tacno = 1;
												break;
											}
										}
									}

									if ($tacno) {
										$bodovi += (int)$o['poeni'];
									}

									$kviz_odgovor_obj->fk_pokusaj = $pokusaj_id;
									$kviz_odgovor_obj->fk_pitanje = $p['id'];
									$kviz_odgovor_obj->fk_odgovor = $o['id'];
									$kviz_odgovor_obj->unet_tekst = ($unet !== '') ? $unet : null;
									$kviz_odgovor_obj->tacan = $tacno;
									$kviz_odgovor_obj->create();
								}

								continue;
							}

							if ($p['tip_pitanja'] === 'spajanje') {

								// spajanje - svaka stavka banke sa tacan_broj se ocenjuje posebno (poredi se sa brojem praznine koji je djak izabrao)
								foreach ($odgovor_obj->read_by_pitanje($p['id'])->fetchAll(PDO::FETCH_ASSOC) as $o) {
									$unet = isset($spajanje_post[$p['id']][$o['id']]) ? trim($spajanje_post[$p['id']][$o['id']]) : '';
									$unet_broj = ($unet !== '') ? (int)$unet : null;

									if ($o['tacan_broj'] !== null) {
										$tacno = ($unet_broj === (int)$o['tacan_broj']) ? 1 : 0;
										if ($tacno) {
											$bodovi += (int)$o['poeni'];
										}
									} else {
										// mamac - ne ocenjuje se
										$tacno = null;
									}

									$kviz_odgovor_obj->fk_pokusaj = $pokusaj_id;
									$kviz_odgovor_obj->fk_pitanje = $p['id'];
									$kviz_odgovor_obj->fk_odgovor = $o['id'];
									$kviz_odgovor_obj->unet_tekst = ($unet !== '') ? $unet : null;
									$kviz_odgovor_obj->tacan = $tacno;
									$kviz_odgovor_obj->create();
								}

								continue;
							}

							$odabrani_id = isset($odgovori_post[$p['id']]) ? (int)$odgovori_post[$p['id']] : null;

							$tacan_id = null;
							foreach ($odgovor_obj->read_by_pitanje($p['id'])->fetchAll(PDO::FETCH_ASSOC) as $o) {
								if ($o['tacan']) {
									$tacan_id = (int)$o['id'];
									break;
								}
							}

							$tacno = ($odabrani_id !== null && $odabrani_id === $tacan_id) ? 1 : 0;
							if ($tacno) {
								$bodovi += (int)$p['poeni'];
							}

							$kviz_odgovor_obj->fk_pokusaj = $pokusaj_id;
							$kviz_odgovor_obj->fk_pitanje = $p['id'];
							$kviz_odgovor_obj->fk_odgovor = $odabrani_id ?: null;
							$kviz_odgovor_obj->unet_tekst = null;
							$kviz_odgovor_obj->tacan = $tacno;
							$kviz_odgovor_obj->create();
						}

						$procenat = $max_bodovi > 0 ? round($bodovi / $max_bodovi * 100, 2) : 0;
						$kviz_pokusaj_obj->finish($pokusaj_id, $bodovi, $max_bodovi, $procenat);
					}

					// Provera da li tekst pitanja sadrzi oznake [1]-[5] za mesto praznine (dopuna i spajanje)
					function kviz_ima_oznake_praznina($tekst)
					{
						return (bool)preg_match('/\[[1-5]\]/', $tekst);
					}

					// Ubacuje HTML (npr. input polje ili prikaz odgovora) na mesto oznaka [1]-[5] u tekstu pitanja,
					// ostatak teksta escape-uje kao i obicno. $blanks je niz indeksiran po redosledu (0-4).
					function kviz_render_tekst_sa_oznakama($tekst, $blanks)
					{
						$delovi = preg_split('/\[([1-5])\]/', $tekst, -1, PREG_SPLIT_DELIM_CAPTURE);

						$html = '';
						foreach ($delovi as $i => $deo) {
							if ($i % 2 === 0) {
								$html .= nl2br(htmlspecialchars($deo));
							} else {
								$redosled = (int)$deo - 1;
								$html .= $blanks[$redosled] ?? '[' . $deo . ']';
							}
						}

						return $html;
					}

					// Kvizovi (pokretanje/predaja odgovora) namerno NISU deo avalon-roditelj -
					// to je akcija djaka. Obe POST akcije su uklonjene (ne samo sakrivene u UI)
					// da ne postoji write-putanja ni direktnim POST zahtevom.

					$aktivni_tab = (isset($_GET['tab']) && in_array($_GET['tab'], ['kviz', 'domaci'])) ? 'kviz' : 'dokumenti';
					$otvoreni_kviz_id = isset($_GET['kviz']) ? (int)$_GET['kviz'] : 0;

					// ===================== DOMAĆI AUDIO - logika =====================
					$domaci_audio_obj = new domaci_audio($db);
					$domaci_audio_lista = $domaci_audio_obj->read_visible_for_djak($moj_id)->fetchAll(PDO::FETCH_ASSOC);
					// ===================== KRAJ DOMAĆI AUDIO logike =====================

					// ===================== DOMAĆI ESEJ - logika =====================
					$domaci_esej_obj = new domaci_esej($db);
					$domaci_esej_lista = $domaci_esej_obj->read_visible_for_djak($moj_id)->fetchAll(PDO::FETCH_ASSOC);
					// ===================== KRAJ DOMAĆI ESEJ logike =====================

					// ===================== DOMAĆI VIDEO - logika =====================
					$domaci_video_obj = new domaci_video($db);
					$domaci_video_lista = $domaci_video_obj->read_visible_for_djak($moj_id)->fetchAll(PDO::FETCH_ASSOC);
					// ===================== KRAJ DOMAĆI VIDEO logike =====================

					// ===================== DOMAĆI WORDWALL - logika =====================
					$domaci_wordwall_obj = new domaci_wordwall($db);
					$domaci_wordwall_lista = $domaci_wordwall_obj->read_visible_for_djak($moj_id)->fetchAll(PDO::FETCH_ASSOC);
					// ===================== KRAJ DOMAĆI WORDWALL logike =====================

					// Kvizovi namerno van scope-a avalon-roditelj (videti belesku iznad kod
					// uklonjenih POST akcija) - prazna lista znaci da $otvoreni_kviz ispod
					// nikad ne pronalazi poklapanje, pa se "resavanje kviza" UI nikad ne
					// aktivira (vidi se dalje u fajlu, uslov ostaje netaknut ali nedostizan)
					$kvizovi_lista = [];

					// Detalji otvorenog kviza - forma za resavanje (ako je pokusaj u toku) ili rezultat poslednjeg zavrsenog pokusaja
					$otvoreni_kviz = null;
					$otvoreni_pitanja = [];
					$otvoreni_pokusaj = null;
					$otvoreni_rezultat = null;
					$otvoreni_rezultat_grupisano = null;

					if ($otvoreni_kviz_id > 0) {

						foreach ($kvizovi_lista as $kv) {
							if ((int)$kv['id'] === $otvoreni_kviz_id) {
								$otvoreni_kviz = $kv;
								break;
							}
						}

						if ($otvoreni_kviz) {

							if ($otvoreni_kviz['u_toku']) {

								$pokusaj = $otvoreni_kviz['u_toku'];

								// ako je vremensko ogranicenje isteklo, automatski predaj (neodgovoreno = netacno) i prikazi rezultat
								if (!empty($otvoreni_kviz['vreme_ogranicenje_min'])) {
									$istek = strtotime($pokusaj['started_at']) + ((int)$otvoreni_kviz['vreme_ogranicenje_min'] * 60);

									if (time() >= $istek) {
										kviz_oceni_i_zavrsi($pitanje_obj, $odgovor_obj, $kviz_odgovor_obj, $kviz_pokusaj_obj, $pokusaj['id'], $otvoreni_kviz_id, []);
										header("Location: index.php?tab=kviz&kviz=" . $otvoreni_kviz_id . "#tab-kviz");
										exit;
									}
								}

								$otvoreni_pokusaj = $pokusaj;
								$otvoreni_pitanja = $pitanje_obj->read_by_kviz($otvoreni_kviz_id)->fetchAll(PDO::FETCH_ASSOC);

								foreach ($otvoreni_pitanja as &$op) {
									$op['odgovori'] = $odgovor_obj->read_by_pitanje($op['id'])->fetchAll(PDO::FETCH_ASSOC);
								}
								unset($op);

							} elseif (!empty($otvoreni_kviz['zavrseni'])) {

								$otvoreni_pokusaj = $otvoreni_kviz['zavrseni'][0];
								$otvoreni_rezultat = $kviz_odgovor_obj->read_by_pokusaj($otvoreni_pokusaj['id'])->fetchAll(PDO::FETCH_ASSOC);

								if ($otvoreni_kviz['prikazi_odgovore']) {
									foreach ($otvoreni_rezultat as &$or) {
										$or['tacan_tekst'] = null;

										if (!in_array($or['tip_pitanja'], ['dopuna', 'spajanje'], true)) {
											foreach ($odgovor_obj->read_by_pitanje($or['fk_pitanje'])->fetchAll(PDO::FETCH_ASSOC) as $o) {
												if ($o['tacan']) {
													$or['tacan_tekst'] = $o['tekst_odgovora'];
													break;
												}
											}
										}
									}
									unset($or);

									// grupisanje po pitanju (po redosledu) - dopuna pitanja imaju do dve odvojene stavke, po jedna za svaku prazninu
									$otvoreni_rezultat_grupisano = [];
									$poslednji_pid = null;
									foreach ($otvoreni_rezultat as $or) {
										if ($or['fk_pitanje'] !== $poslednji_pid) {
											$otvoreni_rezultat_grupisano[] = [
												'tekst_pitanja' => $or['tekst_pitanja'],
												'tip_pitanja' => $or['tip_pitanja'],
												'poeni' => $or['poeni'],
												'stavke' => [],
											];
											$poslednji_pid = $or['fk_pitanje'];
										}
										$otvoreni_rezultat_grupisano[count($otvoreni_rezultat_grupisano) - 1]['stavke'][] = $or;
									}
								}
							}
						}
					}
					// ===================== KRAJ KVIZ logike =====================

					// Unified hronološka lista svih domaćih zadataka za prikaz u tabu
					$domaci_sve = [];
					foreach ($domaci_audio_lista as $_da) {
						$_ri  = !empty($_da['rok']) && strtotime($_da['rok']) < time();
						$_zak = !empty($_da['zakljucan']);
						$domaci_sve[] = ['tip' => 'audio',
						                 'aktivan_flag' => (!$_ri && !$_zak) ? 1 : 0,
						                 'sort_ts' => strtotime($_da['podeljeno_at'] ?? $_da['created_at'] ?? '1970-01-01')] + $_da;
					}
					foreach ($domaci_esej_lista as $_de) {
						$_ri  = !empty($_de['rok']) && strtotime($_de['rok']) < time();
						$_zak = !empty($_de['zakljucan']);
						$domaci_sve[] = ['tip' => 'esej',
						                 'aktivan_flag' => (!$_ri && !$_zak) ? 1 : 0,
						                 'sort_ts' => strtotime($_de['podeljeno_at'] ?? $_de['created_at'] ?? '1970-01-01')] + $_de;
					}
					foreach ($domaci_video_lista as $_dv) {
						$_ri  = !empty($_dv['rok']) && strtotime($_dv['rok']) < time();
						$_zak = !empty($_dv['zakljucan']);
						$domaci_sve[] = ['tip' => 'video',
						                 'aktivan_flag' => (!$_ri && !$_zak) ? 1 : 0,
						                 'sort_ts' => strtotime($_dv['podeljeno_at'] ?? $_dv['created_at'] ?? '1970-01-01')] + $_dv;
					}
					foreach ($domaci_wordwall_lista as $_dw) {
						$_ri  = !empty($_dw['rok']) && strtotime($_dw['rok']) < time();
						$_zak = !empty($_dw['zakljucan']);
						$domaci_sve[] = ['tip' => 'wordwall',
						                 'aktivan_flag' => (!$_ri && !$_zak) ? 1 : 0,
						                 'sort_ts' => strtotime($_dw['podeljeno_at'] ?? $_dw['created_at'] ?? '1970-01-01')] + $_dw;
					}
					foreach ($kvizovi_lista as $_kv) {
						$domaci_sve[] = ['tip' => 'kviz',
						                 'aktivan_flag' => ($_kv['moze_pokusaj'] || $_kv['u_toku']) ? 1 : 0,
						                 'sort_ts' => strtotime($_kv['podeljeno_at'] ?? $_kv['created_at'] ?? '1970-01-01')] + $_kv;
					}
					usort($domaci_sve, function ($a, $b) {
						if ($a['aktivan_flag'] !== $b['aktivan_flag']) {
							return $b['aktivan_flag'] - $a['aktivan_flag'];
						}
						return $b['sort_ts'] - $a['sort_ts'];
					});
					$domaci_aktivni  = array_values(array_filter($domaci_sve, fn($i) => $i['aktivan_flag'] === 1));
					$domaci_zavrseni = array_values(array_filter($domaci_sve, fn($i) => $i['aktivan_flag'] === 0));
					//echo $moj_id;
					//$moj_id = 3;
					// ✅ NOVI QUERY (ZA ĐAKA)
					$query = "
					SELECT
						d.id,
						d.naziv,
						d.created_at,
						d.datum_vazenja_do,
						d.velicina,
						d.ekstenzija,
						d.je_video,
						d.video_url,
						d.je_slobodni_link,
						d.link_url,
						dp.pravo_pregled,
						dp.pravo_download

					FROM dokumenti d

					JOIN dokument_pristup dp
						ON dp.fk_dokument = d.id

					WHERE
						d.aktivan = 1
						AND dp.tip_pristupa = 'djak'
						AND dp.fk_entitet = :djak_id

						AND (d.datum_pocetka_prikaza IS NULL OR d.datum_pocetka_prikaza <= NOW())
						AND (d.datum_kraja_prikaza IS NULL OR d.datum_kraja_prikaza >= NOW())

					ORDER BY d.created_at DESC
					";

					$stmt = $db->prepare($query);
					$stmt->bindParam(":djak_id", $moj_id);
					$stmt->execute();

					// grupisanje po mesecima - fajlovi idu u jedan tab, video i slobodni linkovi
					// (obe vrste "otvori umesto downloaduj") zajedno u drugi
					$dokumenti_po_mesecima = [];
					$linkovi_po_mesecima = [];

					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$mesec = date('Y-m', strtotime($row['created_at']));

						if (!empty($row['je_video']) || !empty($row['je_slobodni_link'])) {
							$linkovi_po_mesecima[$mesec][] = $row;
						} else {
							$dokumenti_po_mesecima[$mesec][] = $row;
						}
					}
					?>

					<div class="mt-3">

						<!-- <h5 class="mb-3"><u>Moji materijali</u></h5> -->

						<!-- TABOVI: Dokumenti / Video / Kviz - naslovi se vide na hover preko title atributa -->
						<ul class="nav nav-tabs mb-3" id="materijaliTab" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= $aktivni_tab === 'dokumenti' ? 'active' : '' ?>" id="tab-dokumenti-btn" data-bs-toggle="tab" data-bs-target="#tab-dokumenti" type="button" role="tab" title="Dokumenti">
									<i class='bx bx-file fs-5'></i> Dokumenti
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="tab-linkovi-btn" data-bs-toggle="tab" data-bs-target="#tab-linkovi" type="button" role="tab" title="Linkovi">
									<i class='bx bx-link fs-5'></i> Linkovi
								</button>
							</li>
							<?php if ($domaci_zadaci_enabled): ?>
							<li class="nav-item" role="presentation">
								<button class="nav-link <?= $aktivni_tab === 'kviz' ? 'active' : '' ?>" id="tab-kviz-btn" data-bs-toggle="tab" data-bs-target="#tab-kviz" type="button" role="tab" title="Domaći zadaci">
									<i class='bx bx-task fs-5'></i> Domaći zadaci
								</button>
							</li>
							<?php endif; ?>
						</ul>

						<div class="tab-content" id="materijaliTabContent">

							<!-- TAB: DOKUMENTI (postojeci prikaz, samo fajlovi - je_video = 0) -->
							<div class="tab-pane fade <?= $aktivni_tab === 'dokumenti' ? 'show active' : '' ?>" id="tab-dokumenti" role="tabpanel">

								<?php if (empty($dokumenti_po_mesecima)) { ?>
									<div class="alert alert-info">Nema dokumenata za prikaz.</div>
								<?php } ?>

								<?php foreach ($dokumenti_po_mesecima as $mesec => $dokumenti) {

									$timestamp = strtotime($mesec . "-01");
									$mesec_naziv = strftime('%B %Y', $timestamp);
									?>

									<div class="mb-2">
										<h6 class="text-muted"><?= ucfirst($mesec_naziv) ?></h6>
									</div>

									<div class="row">

										<?php foreach ($dokumenti as $d) { ?>

											<div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
												<div class="card shadow-sm border-0">
													<div class="card-body">

														<!-- NASLOV -->
														<div class="d-flex justify-content-between">
															<strong><?= htmlspecialchars($d['naziv']) ?></strong>
															<span><?= strtoupper($d['ekstenzija']) ?></span>
														</div>

														<!-- DATUM -->
														<div class="text-muted" style="font-size:13px;">
															📅 <?= date('d.m.Y', strtotime($d['created_at'])) ?>
														</div>

														<!-- VAŽENJE -->
														<?php if(!empty($d['datum_vazenja_do'])){ ?>
														<div style="font-size:13px; color:#dc3545;">
															⏳ Važi do: <?= date('d.m.Y', strtotime($d['datum_vazenja_do'])) ?>
														</div>
														<?php } ?>

														<!-- VELIČINA -->
														<div style="font-size:13px;">
															📄 <?= formatSize((int)$d['velicina']) ?>
														</div>

														<!-- AKCIJE -->
														<div class="mt-3 d-flex gap-2 flex-wrap">

															<?php if($d['pravo_pregled']){ ?>
																<a href="download_dokument.php?id=<?= $d['id'] ?>"
																class="btn btn-outline-primary btn-sm"><i class='bx bx-show'></i>
																	Vidi
																</a>
															<?php } ?>

															<?php if($d['pravo_download']){ ?>
																<a href="download_dokument.php?id=<?= $d['id'] ?>" style="display:none"
																class="btn btn-success btn-sm">
																	Download
																</a>
															<?php } ?>

														</div>

													</div>
												</div>

											</div>
										<?php } ?>

									</div>

									<?php
								} ?>

							</div>

							<!-- TAB: LINKOVI (je_video = 1 ili je_slobodni_link = 1) -->
							<div class="tab-pane fade" id="tab-linkovi" role="tabpanel">

								<?php if (empty($linkovi_po_mesecima)) { ?>
									<div class="alert alert-info">Nema linkova za prikaz.</div>
								<?php } ?>

								<?php foreach ($linkovi_po_mesecima as $mesec => $stavke) {

									$timestamp = strtotime($mesec . "-01");
									$mesec_naziv = strftime('%B %Y', $timestamp);
									?>

									<div class="mb-2">
										<h6 class="text-muted"><?= ucfirst($mesec_naziv) ?></h6>
									</div>

									<div class="row">

										<?php foreach ($stavke as $d) {
											$je_slobodni_link = !empty($d['je_slobodni_link']);
										?>

											<div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
												<div class="card shadow-sm border-0">
													<div class="card-body">

														<!-- NASLOV -->
														<div class="d-flex justify-content-between">
															<strong><?= htmlspecialchars($d['naziv']) ?></strong>
															<i class='bx <?= $je_slobodni_link ? 'bx-link text-primary' : 'bxs-video text-danger' ?>'></i>
														</div>

														<!-- DATUM -->
														<div class="text-muted" style="font-size:13px;">
															📅 <?= date('d.m.Y', strtotime($d['created_at'])) ?>
														</div>

														<!-- VAŽENJE -->
														<?php if(!empty($d['datum_vazenja_do'])){ ?>
														<div style="font-size:13px; color:#dc3545;">
															⏳ Važi do: <?= date('d.m.Y', strtotime($d['datum_vazenja_do'])) ?>
														</div>
														<?php } ?>

														<?php if ($je_slobodni_link): ?>

															<!-- SLOBODNI LINK - otvara se u novom tabu, nema iframe (link/Prezi/LiveWorksheets to i onako blokiraju) -->
															<?php if($d['pravo_pregled']){ ?>
																<div class="mt-3">
																	<a href="<?= htmlspecialchars($d['link_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
																		<i class='bx bx-link-external'></i> Otvori link
																	</a>
																</div>
															<?php } ?>

														<?php else: ?>

															<!-- VIDEO - prikazi/sakrij (lazy-load iframe) -->
															<?php if($d['pravo_pregled']){ ?>
																<div class="mt-3">
																	<button class="btn btn-outline-primary btn-sm toggle-video" data-id="<?= $d['id'] ?>">
																		<i class='bx bx-play-circle'></i> Pogledaj video
																	</button>
																</div>
															<?php } ?>

															<!-- skriveni iframe - src se postavlja na klik (lazy-load) -->
															<div class="ratio ratio-16x9 mt-2" id="video_wrap_<?= $d['id'] ?>" style="display:none;">
																<iframe src="" data-src="<?= htmlspecialchars(video_embed_url($d['video_url'])) ?>" allowfullscreen frameborder="0"></iframe>
															</div>

														<?php endif; ?>

													</div>
												</div>

											</div>
										<?php } ?>

									</div>

									<?php
								} ?>

							</div>

							<!-- TAB: DOMAĆI ZADACI (audio + kvizovi) -->
							<?php if ($domaci_zadaci_enabled): ?>
							<div class="tab-pane fade <?= $aktivni_tab === 'kviz' ? 'show active' : '' ?>" id="tab-kviz" role="tabpanel">

								<!-- (mrtav legacy kod iz avalon-student, if(false) blok od audio/esej/video/wordwall po-tipu prikaza - uklonjen, zamenjen unifikovanom listom nize u fajlu) -->

								<?php if ($otvoreni_kviz && $otvoreni_pokusaj && $otvoreni_kviz['u_toku']) { /* uzimanje/pregled kviza namerno uklonjeno iz avalon-roditelj - kvizovi_lista je uvek prazna pa ovaj uslov nikad nije tacan, ostavljeno radi balansa if/else strukture */ } else { ?>

									<?php if (empty($domaci_sve)): ?>
										<div class="alert alert-info">Nema domaćih zadataka.</div>
									<?php else: ?>
										<?php
										$n_aktivnih  = count($domaci_aktivni);
										$n_zavrsenih = count($domaci_zavrseni);
										$u_kolapsu   = false;
										?>
										<?php if ($n_aktivnih === 0): ?>
											<p class="text-muted small mb-3"><i class='bx bx-info-circle'></i> Nema aktivnih domaćih zadataka.</p>
										<?php else: ?>
										<div class="row">
										<?php endif; ?>
										<?php foreach ($domaci_sve as $item):
											$tip      = $item['tip'];
											$inactive = ($item['aktivan_flag'] === 0);
											if ($inactive && !$u_kolapsu):
												$u_kolapsu = true;
										?>
											<?php if ($n_aktivnih > 0): ?>
											</div><!-- /aktivni-row -->
											<?php endif; ?>
											<div class="mt-3">
												<button type="button"
														class="btn btn-outline-secondary btn-sm"
														data-bs-toggle="collapse"
														data-bs-target="#domaci-zavrseni-collapse"
														aria-expanded="false">
													<i class='bx bx-check-double'></i> Završeni
													<span class="badge bg-secondary ms-1"><?= $n_zavrsenih ?></span>
												</button>
											</div>
											<div class="collapse" id="domaci-zavrseni-collapse">
											<div class="row mt-3">
										<?php   endif; ?>

											<div class="<?= ($tip === 'esej' || $tip === 'video' || $tip === 'wordwall') ? 'col-12' : 'col-12 col-md-6 col-lg-4' ?> mb-3">

											<?php if ($tip === 'audio'):
												$da = $item;
												$ima_odgovor  = !empty($da['odgovor_filename']);
												$je_slusao    = !empty($da['slusano_at']);
												$rok_istekao  = !empty($da['rok']) && strtotime($da['rok']) < time();
												$zakljucan    = !empty($da['zakljucan']);
											?>
												<div class="card shadow-sm border-0 h-100">
													<div class="card-body d-flex flex-column">
														<div class="mb-1"><span class="badge bg-warning text-dark" style="font-size:10px;"><i class='bx bx-microphone'></i> Audio</span></div>
														<div class="d-flex justify-content-between align-items-start mb-1">
															<strong><?= htmlspecialchars($da['naziv']) ?></strong>
															<?php if ($ima_odgovor): ?>
																<span class="badge bg-success ms-2" title="Odgovor poslan"><i class='bx bx-check-circle'></i></span>
															<?php elseif ($zakljucan): ?>
																<span class="badge bg-warning text-dark ms-2" title="Zaključano"><i class='bx bx-lock-alt'></i></span>
															<?php elseif ($je_slusao): ?>
																<span class="badge bg-info text-dark ms-2" title="Poslušano"><i class='bx bx-headphone'></i></span>
															<?php else: ?>
																<span class="badge bg-secondary ms-2">Novo</span>
															<?php endif; ?>
														</div>
														<?php if (!empty($da['opis'])): ?>
															<div class="text-muted mb-2" style="font-size:13px;"><?= nl2br(htmlspecialchars($da['opis'])) ?></div>
														<?php endif; ?>
														<?php if (!empty($da['rok'])): ?>
															<div style="font-size:13px; <?= $rok_istekao ? 'color:#dc3545;' : '' ?>">
																⏳ Rok: <?= date('d.m.Y H:i', strtotime($da['rok'])) ?><?= $rok_istekao ? ' (istekao)' : '' ?>
															</div>
														<?php endif; ?>
														<?php if (!empty($da['audio_filename'])): ?>
															<div class="mt-2">
																<small class="text-muted d-block mb-1">Uputstvo profesora:</small>
																<audio controls class="w-100" style="height:36px;" data-domaci-id="<?= (int)$da['id'] ?>" onplay="oznaci_slusano(this)">
																	<source src="serve_domaci_audio.php?file=<?= urlencode($da['audio_filename']) ?>&tip=profesor" type="<?= htmlspecialchars($da['mime_type']) ?>">
																</audio>
															</div>
														<?php endif; ?>
														<?php if ($ima_odgovor): ?>
															<div class="mt-2">
																<small class="text-muted d-block mb-1">Vaš odgovor (poslan <?= date('d.m.Y', strtotime($da['poslato_at'])) ?>):</small>
																<audio controls class="w-100" style="height:36px;">
																	<source src="serve_domaci_audio.php?file=<?= urlencode($da['odgovor_filename']) ?>&tip=djaci" type="<?= htmlspecialchars($da['odgovor_mime']) ?>">
																</audio>
															</div>
														<?php endif; ?>
														<?php if ($ima_odgovor && $da['ocena_poeni'] !== null): ?>
															<div class="mt-2 p-2 rounded" style="background:#f0f9f0; border-left:3px solid #28a745;">
																<small class="text-muted d-block mb-1">Ocena profesora:</small>
																<strong style="font-size:15px; color:#28a745;"><?= (int)$da['ocena_poeni'] ?> / <?= (int)$da['ocena_max'] ?></strong>
																<?php if (!empty($da['ocena_komentar'])): ?>
																	<div style="font-size:13px; margin-top:4px;"><?= nl2br(htmlspecialchars($da['ocena_komentar'])) ?></div>
																<?php endif; ?>
															</div>
														<?php endif; ?>
														<?php if (!$rok_istekao && !$zakljucan): ?>
															<?php if (!$ima_odgovor): ?>
																<div class="text-muted mt-2" style="font-size:13px;"><i class='bx bx-info-circle'></i> Dete još nije poslalo odgovor.</div>
															<?php endif; ?>
														<?php else: ?>
															<?php if (!$ima_odgovor): ?>
																<div class="text-muted mt-2" style="font-size:13px;"><?php if ($zakljucan): ?>Zadatak je zaključan.<?php else: ?>Rok za predaju je istekao.<?php endif; ?></div>
															<?php endif; ?>
														<?php endif; ?>
														<!-- snimanje/slanje odgovora namerno uklonjeno u avalon-roditelj - to je akcija djaka -->
													</div>
												</div>

											<?php elseif ($tip === 'esej'):
												$de = $item;
												$ima_odgovor = !empty($de['odgovor_tekst']);
												$rok_istekao = !empty($de['rok']) && strtotime($de['rok']) < time();
												$zakljucan   = !empty($de['zakljucan']);
												$je_ocenjen  = $ima_odgovor && $de['ocena_poeni'] !== null;
												$moze_pisati = !$rok_istekao && !$zakljucan && !$je_ocenjen;
											?>
												<div class="card shadow-sm border-0 h-100">
													<div class="card-body" style="padding:14px 14px 12px;">
														<div class="mb-1"><span class="badge bg-primary" style="font-size:10px;"><i class='bx bx-edit-alt'></i> Esej</span></div>
														<div class="d-flex justify-content-between align-items-start">
															<strong style="font-size:0.93rem; line-height:1.3;"><?= htmlspecialchars($de['naziv']) ?></strong>
															<div class="d-flex gap-1 ms-2 flex-shrink-0">
																<?php if (!empty($de['komentar_html'])): ?>
																	<span class="badge bg-info text-dark" title="Profesor komentarisao"><i class='bx bx-comment-dots'></i></span>
																<?php endif; ?>
																<?php if ($ima_odgovor): ?>
																	<span class="badge bg-success" title="Odgovor poslan"><i class='bx bx-check-circle'></i></span>
																<?php elseif ($zakljucan): ?>
																	<span class="badge bg-warning text-dark" title="Zaključano"><i class='bx bx-lock-alt'></i></span>
																<?php else: ?>
																	<span class="badge bg-secondary">Novo</span>
																<?php endif; ?>
															</div>
														</div>
														<?php if (!empty($de['opis'])): ?>
															<div class="text-muted mt-1" style="font-size:12px; white-space:pre-wrap; line-height:1.4;"><?= htmlspecialchars($de['opis']) ?></div>
														<?php endif; ?>
														<?php if (!empty($de['rok'])): ?>
															<div class="mt-1" style="font-size:12px; <?= $rok_istekao ? 'color:#dc3545; font-weight:500;' : 'color:#666;' ?>">
																⏳ <?= date('d.m.Y H:i', strtotime($de['rok'])) ?><?= $rok_istekao ? ' (istekao)' : '' ?>
															</div>
														<?php endif; ?>
														<button type="button"
																class="btn btn-link btn-sm px-0 mt-2 esej-toggle-btn"
																data-bs-toggle="collapse"
																data-bs-target="#esej-more-<?= (int)$de['id'] ?>"
																style="font-size:12px; text-decoration:none; color:#0d6efd;">
															<i class='bx bx-chevron-down'></i> Vidi više
														</button>
														<div class="collapse" id="esej-more-<?= (int)$de['id'] ?>">
															<?php if ($ima_odgovor): ?>
																<div class="mt-2">
																	<small class="text-muted d-block mb-1">Poslan <?= date('d.m.Y', strtotime($de['poslato_at'])) ?>:</small>
																	<div class="ql-snow bg-light rounded" style="max-height:140px; overflow-y:auto;">
																		<div class="ql-editor" style="font-size:13px; padding:8px 12px;">
																			<?= $de['odgovor_tekst'] ?>
																		</div>
																	</div>
																</div>
															<?php endif; ?>
															<?php if ($ima_odgovor && $de['ocena_poeni'] !== null): ?>
																<div class="mt-2 p-2 rounded" style="background:#f0f9f0; border-left:3px solid #28a745;">
																	<small class="text-muted d-block mb-1">Ocena profesora:</small>
																	<strong style="font-size:15px; color:#28a745;"><?= (int)$de['ocena_poeni'] ?> / <?= (int)$de['ocena_max'] ?></strong>
																	<?php if (!empty($de['ocena_komentar'])): ?>
																		<div style="font-size:13px; margin-top:4px;"><?= nl2br(htmlspecialchars($de['ocena_komentar'])) ?></div>
																	<?php endif; ?>
																</div>
															<?php endif; ?>
															<?php if (!empty($de['komentar_html'])): ?>
																<div class="mt-2 rounded" style="border-left:3px solid #dc3545; background:#fff8f8;">
																	<small class="text-muted d-block mb-1 pt-2 px-2"><i class='bx bx-comment-edit'></i> Komentar profesora:</small>
																	<div class="ql-snow">
																		<div class="ql-editor" style="font-size:13px; padding:4px 12px 8px;">
																			<?= $de['komentar_html'] ?>
																		</div>
																	</div>
																</div>
															<?php endif; ?>
															<!-- pisanje/slanje odgovora namerno uklonjeno u avalon-roditelj - to je akcija djaka -->
															<?php if ($moze_pisati && !$ima_odgovor): ?>
																<div class="text-muted pt-2" style="font-size:13px;"><i class='bx bx-info-circle'></i> Dete još nije poslalo odgovor.</div>
															<?php elseif ($je_ocenjen): ?>
																<div class="text-muted pt-2" style="font-size:13px;">
																	<i class='bx bx-lock-alt'></i> Zadatak je ocenjen — izmena nije moguća.
																</div>
															<?php elseif (!$ima_odgovor): ?>
																<div class="text-muted pt-2" style="font-size:13px;">
																	<?= $zakljucan ? 'Zadatak je zaključan.' : 'Rok za predaju je istekao.' ?>
																</div>
															<?php endif; ?>
														</div><!-- /.collapse -->
													</div>
												</div>

											<?php elseif ($tip === 'video'):
												$dv = $item;
												$ima_odgovor = !empty($dv['odgovor_tekst']);
												$rok_istekao = !empty($dv['rok']) && strtotime($dv['rok']) < time();
												$zakljucan   = !empty($dv['zakljucan']);
												$je_ocenjen  = $ima_odgovor && $dv['ocena_poeni'] !== null;
												$moze_pisati = !$rok_istekao && !$zakljucan && !$je_ocenjen;
											?>
												<div class="card shadow-sm border-0 h-100">
													<div class="card-body" style="padding:14px 14px 12px;">
														<div class="mb-1"><span class="badge" style="font-size:10px; background:#7c3aed; color:#fff;"><i class='bx bx-play-circle'></i> Video</span></div>
														<div class="d-flex justify-content-between align-items-start">
															<strong style="font-size:0.93rem; line-height:1.3;"><?= htmlspecialchars($dv['naziv']) ?></strong>
															<div class="d-flex gap-1 ms-2 flex-shrink-0">
																<?php if (!empty($dv['komentar_html'])): ?>
																	<span class="badge bg-info text-dark" title="Profesor komentarisao"><i class='bx bx-comment-dots'></i></span>
																<?php endif; ?>
																<?php if ($ima_odgovor): ?>
																	<span class="badge bg-success" title="Odgovor poslan"><i class='bx bx-check-circle'></i></span>
																<?php elseif ($zakljucan): ?>
																	<span class="badge bg-warning text-dark" title="Zaključano"><i class='bx bx-lock-alt'></i></span>
																<?php else: ?>
																	<span class="badge bg-secondary">Novo</span>
																<?php endif; ?>
															</div>
														</div>
														<?php if (!empty($dv['rok'])): ?>
															<div class="mt-1" style="font-size:12px; <?= $rok_istekao ? 'color:#dc3545; font-weight:500;' : 'color:#666;' ?>">
																⏳ <?= date('d.m.Y H:i', strtotime($dv['rok'])) ?><?= $rok_istekao ? ' (istekao)' : '' ?>
															</div>
														<?php endif; ?>

														<!-- Video (lazy-load) -->
														<?php if (!empty($dv['video_url'])): ?>
															<div class="mt-2">
																<button class="btn btn-outline-secondary btn-sm toggle-video" data-id="domaci_<?= (int)$dv['id'] ?>">
																	<i class='bx bx-play-circle'></i> Pogledaj video
																</button>
																<div id="video_wrap_domaci_<?= (int)$dv['id'] ?>" style="display:none; margin-top:6px;">
																	<div style="position:relative; padding-bottom:56.25%; height:0; overflow:hidden;">
																		<iframe data-src="<?= htmlspecialchars($dv['video_url']) ?>"
																				style="position:absolute; top:0; left:0; width:100%; height:100%; border:0;"
																				allowfullscreen loading="lazy">
																		</iframe>
																	</div>
																</div>
															</div>
														<?php endif; ?>

														<?php if (!empty($dv['opis'])): ?>
															<div class="text-muted mt-2" style="font-size:12px; white-space:pre-wrap; line-height:1.4;"><?= htmlspecialchars($dv['opis']) ?></div>
														<?php endif; ?>

														<button type="button"
																class="btn btn-link btn-sm px-0 mt-2 video-toggle-btn"
																data-bs-toggle="collapse"
																data-bs-target="#video-more-<?= (int)$dv['id'] ?>"
																style="font-size:12px; text-decoration:none; color:#7c3aed;">
															<i class='bx bx-chevron-down'></i> <?= $ima_odgovor ? 'Vidi odgovor' : 'Odgovori' ?>
														</button>

														<div class="collapse" id="video-more-<?= (int)$dv['id'] ?>">
															<?php if ($ima_odgovor): ?>
																<div class="mt-2">
																	<small class="text-muted d-block mb-1">Poslan <?= date('d.m.Y', strtotime($dv['poslato_at'])) ?>:</small>
																	<div class="ql-snow bg-light rounded" style="max-height:140px; overflow-y:auto;">
																		<div class="ql-editor" style="font-size:13px; padding:8px 12px;">
																			<?= $dv['odgovor_tekst'] ?>
																		</div>
																	</div>
																</div>
															<?php endif; ?>
															<?php if ($ima_odgovor && $dv['ocena_poeni'] !== null): ?>
																<div class="mt-2 p-2 rounded" style="background:#f0f9f0; border-left:3px solid #28a745;">
																	<small class="text-muted d-block mb-1">Ocena profesora:</small>
																	<strong style="font-size:15px; color:#28a745;"><?= (int)$dv['ocena_poeni'] ?> / <?= (int)$dv['ocena_max'] ?></strong>
																	<?php if (!empty($dv['ocena_komentar'])): ?>
																		<div style="font-size:13px; margin-top:4px;"><?= nl2br(htmlspecialchars($dv['ocena_komentar'])) ?></div>
																	<?php endif; ?>
																</div>
															<?php endif; ?>
															<?php if (!empty($dv['komentar_html'])): ?>
																<div class="mt-2 rounded" style="border-left:3px solid #dc3545; background:#fff8f8;">
																	<small class="text-muted d-block mb-1 pt-2 px-2"><i class='bx bx-comment-edit'></i> Komentar profesora:</small>
																	<div class="ql-snow">
																		<div class="ql-editor" style="font-size:13px; padding:4px 12px 8px;">
																			<?= $dv['komentar_html'] ?>
																		</div>
																	</div>
																</div>
															<?php endif; ?>
															<!-- pisanje/slanje odgovora namerno uklonjeno u avalon-roditelj - to je akcija djaka -->
															<?php if ($moze_pisati && !$ima_odgovor): ?>
																<div class="text-muted pt-2" style="font-size:13px;"><i class='bx bx-info-circle'></i> Dete još nije poslalo odgovor.</div>
															<?php elseif ($je_ocenjen): ?>
																<div class="text-muted pt-2" style="font-size:13px;">
																	<i class='bx bx-lock-alt'></i> Zadatak je ocenjen — izmena nije moguća.
																</div>
															<?php elseif (!$ima_odgovor): ?>
																<div class="text-muted pt-2" style="font-size:13px;">
																	<?= $zakljucan ? 'Zadatak je zaključan.' : 'Rok za predaju je istekao.' ?>
																</div>
															<?php endif; ?>
														</div><!-- /.collapse -->
													</div>
												</div>

											<?php elseif ($tip === 'wordwall'):
												$dw = $item;
												$rok_istekao = !empty($dw['rok']) && strtotime($dw['rok']) < time();
												$zakljucan   = !empty($dw['zakljucan']);
												$moze_otvoriti = !$rok_istekao && !$zakljucan;
												$moje_ime = trim(($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''));
											?>
												<div class="card shadow-sm border-0 h-100">
													<div class="card-body" style="padding:14px 14px 12px;">
														<div class="mb-1"><span class="badge" style="font-size:10px; background:#0ea5e9; color:#fff;"><i class='bx bx-joystick'></i> Wordwall</span></div>
														<div class="d-flex justify-content-between align-items-start">
															<strong style="font-size:0.93rem; line-height:1.3;"><?= htmlspecialchars($dw['naziv']) ?></strong>
															<?php if ($zakljucan): ?>
																<span class="badge bg-warning text-dark ms-2" title="Zaključano"><i class='bx bx-lock-alt'></i></span>
															<?php elseif (!empty($dw['otvoreno_at'])): ?>
																<span class="badge bg-success" title="Otvoreno <?= htmlspecialchars($dw['otvoreno_at']) ?>"><i class='bx bx-check-circle'></i></span>
															<?php endif; ?>
														</div>
														<?php if (!empty($dw['rok'])): ?>
															<div class="mt-1" style="font-size:12px; <?= $rok_istekao ? 'color:#dc3545; font-weight:500;' : 'color:#666;' ?>">
																⏳ <?= date('d.m.Y H:i', strtotime($dw['rok'])) ?><?= $rok_istekao ? ' (istekao)' : '' ?>
															</div>
														<?php endif; ?>
														<?php if (!empty($dw['opis'])): ?>
															<div class="text-muted mt-2" style="font-size:12px; white-space:pre-wrap; line-height:1.4;"><?= htmlspecialchars($dw['opis']) ?></div>
														<?php endif; ?>

														<?php if ($dw['ocena_poeni'] !== null): ?>
															<div class="mt-2 p-2 rounded" style="background:#f0f9f0; border-left:3px solid #28a745;">
																<small class="text-muted d-block mb-1">Ocena profesora:</small>
																<strong style="font-size:15px; color:#28a745;"><?= (int)$dw['ocena_poeni'] ?> / <?= (int)$dw['ocena_max'] ?></strong>
																<?php if (!empty($dw['ocena_komentar'])): ?>
																	<div style="font-size:13px; margin-top:4px;"><?= nl2br(htmlspecialchars($dw['ocena_komentar'])) ?></div>
																<?php endif; ?>
															</div>
														<?php endif; ?>

														<!-- otvaranje Wordwall zadatka namerno uklonjeno u avalon-roditelj - to je akcija djaka -->
														<?php if ($moze_otvoriti && !empty($dw['wordwall_url']) && empty($dw['otvoreno_at'])): ?>
															<div class="text-muted mt-2" style="font-size:13px;"><i class='bx bx-info-circle'></i> Dete još nije otvorilo zadatak.</div>
														<?php elseif (!$moze_otvoriti): ?>
															<div class="text-muted mt-2" style="font-size:13px;">
																<?= $zakljucan ? 'Zadatak je zaključan.' : 'Rok za predaju je istekao.' ?>
															</div>
														<?php endif; ?>
													</div>
												</div>

									<?php elseif ($tip === 'kviz'):
												$kv = $item;
											?>
												<div class="card shadow-sm border-0 h-100">
													<div class="card-body d-flex flex-column">
														<div class="mb-1"><span class="badge bg-success" style="font-size:10px;"><i class='bx bx-help-circle'></i> Kviz</span></div>
														<strong><?= htmlspecialchars($kv['naziv']) ?></strong>
														<?php if (!empty($kv['opis'])) { ?>
															<div class="text-muted mt-1" style="font-size:13px;"><?= nl2br(htmlspecialchars($kv['opis'])) ?></div>
														<?php } ?>
														<div class="mt-2" style="font-size:13px;">
															📝 <?= (int)$kv['broj_pitanja'] ?> <?= $kv['broj_pitanja'] == 1 ? 'pitanje' : 'pitanja' ?>
														</div>
														<?php if (!empty($kv['vreme_ogranicenje_min'])) { ?>
															<div style="font-size:13px;">⏱️ Vremensko ograničenje: <?= (int)$kv['vreme_ogranicenje_min'] ?> min</div>
														<?php } ?>
														<?php if (!empty($kv['rok'])) { ?>
															<div style="font-size:13px; <?= $kv['rok_istekao'] ? 'color:#dc3545;' : '' ?>">
																⏳ Rok: <?= date('d.m.Y H:i', strtotime($kv['rok'])) ?>
																<?= $kv['rok_istekao'] ? ' (istekao)' : '' ?>
															</div>
														<?php } ?>
														<div style="font-size:13px;">
															🔁 Pokušaji: <?= count($kv['zavrseni']) ?> / <?= (int)$kv['broj_pokusaja'] ?>
														</div>
														<div class="mt-3">
															<?php if ($kv['u_toku']) { ?>
																<a href="index.php?tab=kviz&kviz=<?= (int)$kv['id'] ?>#tab-kviz" class="btn btn-warning btn-sm">
																	<i class='bx bx-play-circle'></i> Nastavi kviz
																</a>
															<?php } elseif ($kv['moze_pokusaj']) { ?>
																<form method="post">
																	<input type="hidden" name="kviz_action" value="pokreni">
																	<input type="hidden" name="kviz_id" value="<?= (int)$kv['id'] ?>">
																	<button type="submit" class="btn btn-primary btn-sm">
																		<i class='bx bx-help-circle'></i> Pokreni kviz
																	</button>
																</form>
															<?php } elseif (!empty($kv['zavrseni'])) { ?>
																<a href="index.php?tab=kviz&kviz=<?= (int)$kv['id'] ?>#tab-kviz" class="btn btn-outline-secondary btn-sm">
																	<i class='bx bx-bar-chart-alt-2'></i> Pregled rezultata
																</a>
															<?php } else { ?>
																<span class="badge bg-secondary">Rok istekao</span>
															<?php } ?>
														</div>
													</div>
												</div>

											<?php endif; ?>
											</div>
										<?php endforeach; ?>
										<?php if (!$u_kolapsu && $n_aktivnih > 0): ?>
										</div><!-- /aktivni-row -->
										<?php elseif ($u_kolapsu): ?>
										</div><!-- /zavrseni-row -->
										</div><!-- /collapse -->
										<?php endif; ?>
									<?php endif; ?>

								<?php } ?>

							</div>
							<?php endif; ?>

						</div>

			</div>
		</div>
			</div>
		<!--end page wrapper -->
		<!--start overlay-->
		 <div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
		  <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			
            <?php // include __DIR__ . '/app/footer.php'; ?>
		</footer>
	</div>
	<!--end wrapper-->


	<!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
		  <div class="modal-content">
			<div class="modal-header gap-2">
			  <div class="position-relative popup-search w-100">
				<input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search" placeholder="Search">
				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4"><i class='bx bx-search'></i></span>
			  </div>
			  <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="search-list">
				   <p class="mb-1">Html Templates</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action active align-items-center d-flex gap-2 py-1"><i class='bx bxl-angular fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Web Designe Company</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-windows fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-dropbox fs-4' ></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Software Development</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-zoom fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Online Shoping Portals</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-slack fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-skype fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>
				   </div>
				</div>
			</div>
		  </div>
		</div>
	  </div>
    <!-- end search modal -->




	<!--start switcher-->
	<div class="switcher-wrapper" style="display:none;">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<h6 class="mb-0">Theme Styles</h6>
			<hr/>
			<div class="d-flex align-items-center justify-content-between">
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
					<label class="form-check-label" for="lightmode">Light</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
					<label class="form-check-label" for="darkmode">Dark</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
					<label class="form-check-label" for="semidark">Semi Dark</label>
				</div>
			</div>
			<hr/>
			<div class="form-check">
				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>
			</div>
			<hr/>
			<h6 class="mb-0">Header Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator headercolor1" id="headercolor1"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor2" id="headercolor2"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor3" id="headercolor3"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor4" id="headercolor4"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor5" id="headercolor5"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor6" id="headercolor6"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor7" id="headercolor7"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor8" id="headercolor8"></div>
					</div>
				</div>
			</div>
			<hr/>
			<h6 class="mb-0">Sidebar Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
 
	<!--end switcher-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<script src="assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
	<script src="assets/plugins/chartjs/js/chart.js"></script>
	<!-- <script src="assets/js/index_prof.js"></script>  -->
	<!-- app JS -->
	<script src="assets/js/app.js"></script>
	<script>
		new PerfectScrollbar(".app-container")
	</script>
	<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
	<script>
	// Prikaz/sakrivanje iframe-a za video materijale (lazy-load src, da se ne ucitava unapred)
	$(document).on('click', '.toggle-video', function(){

		let id = $(this).data('id');
		let wrap = $('#video_wrap_' + id);
		let iframe = wrap.find('iframe');

		if (wrap.is(':visible')) {
			wrap.hide();
			iframe.attr('src', ''); // zaustavi reprodukciju kad se sakrije
			$(this).html("<i class='bx bx-play-circle'></i> Pogledaj video");
			return;
		}

		if (!iframe.attr('src')) {
			iframe.attr('src', iframe.data('src'));
		}

		wrap.show();
		$(this).html("<i class='bx bx-x-circle'></i> Sakrij video");
	});
	</script>
	<script>
	// Navigacija kroz pitanja kviza - jedno pitanje po ekranu, strelice levo/desno
	var kvizKartice = document.querySelectorAll('.kviz-pitanje-card');
	if (kvizKartice.length > 0) {
		var kvizIndeks = 0;
		var kvizUkupno = kvizKartice.length;
		var kvizPrevBtn = document.getElementById('kvizPrev');
		var kvizNextBtn = document.getElementById('kvizNext');
		var kvizPredajBtn = document.getElementById('kvizPredaj');
		var kvizBrojSpan = document.getElementById('kvizBrojTrenutni');

		var kvizPrikaziPitanje = function (i) {
			kvizKartice.forEach(function (kartica, ci) {
				kartica.style.display = (ci === i) ? '' : 'none';
			});

			kvizBrojSpan.textContent = (i + 1);
			kvizPrevBtn.disabled = (i === 0);

			if (i === kvizUkupno - 1) {
				kvizNextBtn.style.display = 'none';
				kvizPredajBtn.style.display = '';
			} else {
				kvizNextBtn.style.display = '';
				kvizPredajBtn.style.display = 'none';
			}
		};

		kvizPrevBtn.addEventListener('click', function () {
			if (kvizIndeks > 0) {
				kvizIndeks--;
				kvizPrikaziPitanje(kvizIndeks);
			}
		});

		kvizNextBtn.addEventListener('click', function () {
			if (kvizIndeks < kvizUkupno - 1) {
				kvizIndeks++;
				kvizPrikaziPitanje(kvizIndeks);
			}
		});

		kvizPrikaziPitanje(kvizIndeks);
	}
	</script>
	<script>
	// ===== DOMAĆI AUDIO: oznaci slušanje + snimanje odgovora =====
	(function () {

		var prioritetMime = [
			'audio/mp4',
			'audio/webm;codecs=opus',
			'audio/webm',
			'audio/ogg;codecs=opus',
			'audio/ogg'
		];

		function odabereMimeType() {
			if (typeof MediaRecorder === 'undefined') return '';
			for (var i = 0; i < prioritetMime.length; i++) {
				if (MediaRecorder.isTypeSupported(prioritetMime[i])) return prioritetMime[i];
			}
			return '';
		}

		function formatVreme(s) {
			var m = Math.floor(s / 60), sec = s % 60;
			return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
		}

		// State po zadatku
		var stanje = {};

		function getStanje(id) {
			if (!stanje[id]) stanje[id] = { recorder: null, chunks: [], mimeType: '', timer: null, sek: 0 };
			return stanje[id];
		}

		function resetStanje(id) {
			var s = getStanje(id);
			clearInterval(s.timer);
			s.recorder = null; s.chunks = []; s.mimeType = ''; s.timer = null; s.sek = 0;
			$('.djak-playback[data-id="' + id + '"]').hide();
			$('.djak-timer[data-id="' + id + '"]').text('');
			$('.djak-upload-status[data-id="' + id + '"]').html('');
			$('.djak-snimi-btn[data-id="' + id + '"]').show().prop('disabled', false);
			$('.djak-zaustavi-btn[data-id="' + id + '"]').hide().prop('disabled', true);
		}

		// Oznaci slusano - poziva se iz onplay atributa audio elementa
		window.oznaci_slusano = function (audioEl) {
			var id = $(audioEl).data('domaci-id');
			if (!id) return;
			$.post('ajax_oznaci_slusano.php', { domaci_id: id });
		};

		// Wordwall: evidentiraj klik na link, ne blokiraj otvaranje u novom tabu
		$(document).on('click', '.wordwall-otvori-link', function () {
			var id = $(this).data('id');
			if (!id) return;
			$.post('ajax_otvori_wordwall.php', { domaci_id: id });
		});

		// Dugme SNIMI
		$(document).on('click', '.djak-snimi-btn', function () {
			var id = parseInt($(this).data('id'));
			var s = getStanje(id);

			if (!window.isSecureContext) {
				alert('Za snimanje mikrofona potrebna je HTTPS veza.\nOva stranica je otvorena preko HTTP — obratite se administratoru ili koristite HTTPS.');
				return;
			}
			if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
				alert('Vaš browser ne podržava snimanje mikrofona. Koristite Chrome ili Safari.');
				return;
			}

			if (!window.isSecureContext) {
				alert('Za snimanje mikrofona potrebna je HTTPS veza.\nOva stranica je otvorena preko HTTP — obratite se administratoru ili koristite HTTPS.');
				return;
			}

			navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
				s.mimeType = odabereMimeType();
				var opcije = s.mimeType ? { mimeType: s.mimeType } : {};
				try {
					s.recorder = new MediaRecorder(stream, opcije);
				} catch (e) {
					s.recorder = new MediaRecorder(stream);
					s.mimeType = s.recorder.mimeType;
				}
				s.chunks = [];

				s.recorder.addEventListener('dataavailable', function (e) {
					if (e.data && e.data.size > 0) s.chunks.push(e.data);
				});

				s.recorder.addEventListener('stop', function () {
					stream.getTracks().forEach(function (t) { t.stop(); });
					clearInterval(s.timer);
					var blob = new Blob(s.chunks, { type: s.mimeType || 'audio/webm' });
					var url = URL.createObjectURL(blob);
					$('.djak-audio-preview[data-id="' + id + '"]').attr('src', url);
					$('.djak-playback[data-id="' + id + '"]').show();
					$('.djak-snimi-btn[data-id="' + id + '"]').show().prop('disabled', false);
					$('.djak-zaustavi-btn[data-id="' + id + '"]').hide().prop('disabled', true);
					$('.djak-timer[data-id="' + id + '"]').text('');
				});

				s.recorder.start(1000);
				s.sek = 0;
				$('.djak-timer[data-id="' + id + '"]').text(formatVreme(0));
				s.timer = setInterval(function () {
					s.sek++;
					$('.djak-timer[data-id="' + id + '"]').text(formatVreme(s.sek));
				}, 1000);

				$('.djak-snimi-btn[data-id="' + id + '"]').hide();
				$('.djak-zaustavi-btn[data-id="' + id + '"]').show().prop('disabled', false);
				$('.djak-playback[data-id="' + id + '"]').hide();

			}).catch(function (err) {
				alert('Ne mogu da pristupim mikrofonu: ' + err.message);
			});
		});

		// Dugme ZAUSTAVI
		$(document).on('click', '.djak-zaustavi-btn', function () {
			var id = parseInt($(this).data('id'));
			var s = getStanje(id);
			if (s.recorder && s.recorder.state !== 'inactive') s.recorder.stop();
		});

		// Dugme PONOVI
		$(document).on('click', '.djak-reset-btn', function () {
			var id = parseInt($(this).data('id'));
			resetStanje(id);
		});

		// Dugme POŠALJI
		$(document).on('click', '.djak-posalji-btn', function () {
			var id = parseInt($(this).data('id'));
			var s = getStanje(id);
			var $btn = $(this);
			var $status = $('.djak-upload-status[data-id="' + id + '"]');

			if (!s.chunks || s.chunks.length === 0) {
				alert('Nema snimka za slanje.');
				return;
			}

			$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
			$status.html('');

			var ext = 'webm';
			if (s.mimeType.indexOf('mp4') !== -1) ext = 'mp4';
			else if (s.mimeType.indexOf('ogg') !== -1) ext = 'ogg';

			var blob = new Blob(s.chunks, { type: s.mimeType || 'audio/webm' });
			var formData = new FormData();
			formData.append('audio', blob, 'odgovor.' + ext);
			formData.append('domaci_id', id);

			$.ajax({
				url: 'upload_djak_audio.php',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function (resp) {
					try {
						var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
						if (data.error) {
							$status.html('<div class="alert alert-danger p-1 mb-0 small">' + data.error + '</div>');
							$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji odgovor');
							return;
						}
						$status.html('<div class="alert alert-success p-1 mb-0 small"><i class="bx bx-check"></i> Odgovor je poslat!</div>');
						$btn.prop('disabled', true).html('<i class="bx bx-check-circle"></i> Poslato');
					} catch (e) {
						$status.html('<div class="alert alert-danger p-1 mb-0 small">Greška pri čitanju odgovora.</div>');
						$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji odgovor');
					}
				},
				error: function () {
					$status.html('<div class="alert alert-danger p-1 mb-0 small">Greška pri komunikaciji.</div>');
					$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji odgovor');
				}
			});
		});

	}());
	</script>
	<script>
	// ===== DOMAĆI ESEJ: Quill editori, nacrt (localStorage) i slanje =====
	var esejEditors = {};

	document.querySelectorAll('.esej-quill-editor').forEach(function (el) {
		var id       = parseInt(el.dataset.id);
		var draftKey = 'esej_nacrt_' + id;
		var q = new Quill(el, {
			theme: 'snow',
			placeholder: 'Napišite vaš esej ovde...',
			modules: {
				toolbar: [
					['bold', 'italic', 'underline'],
					[{ 'list': 'ordered' }, { 'list': 'bullet' }],
					['clean']
				]
			}
		});

		var draft = localStorage.getItem(draftKey);
		var init  = el.dataset.init || '';
		if (draft && draft.trim() !== '') {
			q.root.innerHTML = draft;
			var badge    = document.querySelector('.esej-nacrt-badge[data-id="' + id + '"]');
			if (badge) badge.style.display = 'inline';
			var brisiBtn = document.querySelector('.esej-brisi-nacrt-btn[data-id="' + id + '"]');
			if (brisiBtn) brisiBtn.style.display = 'inline-block';
		} else if (init.trim() !== '') {
			q.root.innerHTML = init;
		}

		esejEditors[id] = q;
	});

	// Collapse toggle — mijenjaj tekst dugmeta
	document.querySelectorAll('.esej-toggle-btn').forEach(function (btn) {
		var target = document.querySelector(btn.dataset.bsTarget);
		if (!target) return;
		target.addEventListener('show.bs.collapse', function () {
			btn.innerHTML = "<i class='bx bx-chevron-up'></i> Sakrij";
		});
		target.addEventListener('hide.bs.collapse', function () {
			btn.innerHTML = "<i class='bx bx-chevron-down'></i> Vidi više";
		});
	});

	// Snimi nacrt u localStorage
	$(document).on('click', '.esej-snimi-btn', function () {
		var id  = parseInt($(this).data('id'));
		var q   = esejEditors[id];
		if (!q) return;
		var draftKey = 'esej_nacrt_' + id;
		localStorage.setItem(draftKey, q.root.innerHTML);
		$('.esej-nacrt-ok[data-id="' + id + '"]').show();
		setTimeout(function () { $('.esej-nacrt-ok[data-id="' + id + '"]').hide(); }, 2500);
		var badge    = document.querySelector('.esej-nacrt-badge[data-id="' + id + '"]');
		if (badge) badge.style.display = 'inline';
		var brisiBtn = document.querySelector('.esej-brisi-nacrt-btn[data-id="' + id + '"]');
		if (brisiBtn) brisiBtn.style.display = 'inline-block';
	});

	// Briši nacrt iz localStorage i resetuj editor
	$(document).on('click', '.esej-brisi-nacrt-btn', function () {
		var id       = parseInt($(this).data('id'));
		var initHtml = $(this).data('init') || '';
		localStorage.removeItem('esej_nacrt_' + id);
		var q = esejEditors[id];
		if (q) q.root.innerHTML = initHtml;
		var badge = document.querySelector('.esej-nacrt-badge[data-id="' + id + '"]');
		if (badge) badge.style.display = 'none';
		$(this).hide();
	});

	// Pošalji profesoru
	$(document).on('click', '.esej-posalji-btn', function () {
		var id   = parseInt($(this).data('id'));
		var q    = esejEditors[id];
		var $btn = $(this);
		var $ok  = $('.esej-ok[data-id="' + id + '"]');
		var $err = $('.esej-error[data-id="' + id + '"]');

		$ok.hide();
		$err.hide();

		if (!q) { $err.text('Greška editora.').show(); return; }

		if (q.getText().trim() === '') {
			$err.text('Tekst ne sme biti prazan.').show();
			return;
		}

		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

		$.ajax({
			url: 'ajax_odgovori_esej.php',
			type: 'POST',
			data: { domaci_id: id, tekst: q.root.innerHTML },
			success: function (resp) {
				try {
					var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
					if (data.error) {
						$err.text(data.error).show();
						$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
						return;
					}
					// Obriši nacrt — odgovor je sačuvan na serveru
					localStorage.removeItem('esej_nacrt_' + id);
					var badge    = document.querySelector('.esej-nacrt-badge[data-id="' + id + '"]');
					var brisiBtn2 = document.querySelector('.esej-brisi-nacrt-btn[data-id="' + id + '"]');
					if (badge)     badge.style.display    = 'none';
					if (brisiBtn2) brisiBtn2.style.display = 'none';
					$ok.show();
					setTimeout(function () { $ok.hide(); }, 3000);
					$btn.prop('disabled', false).html('<i class="bx bx-refresh"></i> Ažurirati');
				} catch (e) {
					$err.text('Greška pri čitanju odgovora servera.').show();
					$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
				}
			},
			error: function () {
				$err.text('Greška pri komunikaciji sa serverom.').show();
				$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
			}
		});
	});
	</script>
	<script>
	// ===== DOMAĆI VIDEO: Quill editori, nacrt (localStorage) i slanje =====
	var videoEditors = {};

	document.querySelectorAll('.video-quill-editor').forEach(function (el) {
		var id       = parseInt(el.dataset.id);
		var draftKey = 'video_nacrt_' + id;
		var q = new Quill(el, {
			theme: 'snow',
			placeholder: 'Napišite vaš odgovor ovde...',
			modules: {
				toolbar: [
					['bold', 'italic', 'underline'],
					[{ 'list': 'ordered' }, { 'list': 'bullet' }],
					['clean']
				]
			}
		});

		var draft = localStorage.getItem(draftKey);
		var init  = el.dataset.init || '';
		if (draft && draft.trim() !== '') {
			q.root.innerHTML = draft;
			var badge    = document.querySelector('.video-nacrt-badge[data-id="' + id + '"]');
			if (badge) badge.style.display = 'inline';
			var brisiBtn = document.querySelector('.video-brisi-nacrt-btn[data-id="' + id + '"]');
			if (brisiBtn) brisiBtn.style.display = 'inline-block';
		} else if (init.trim() !== '') {
			q.root.innerHTML = init;
		}

		videoEditors[id] = q;
	});

	// Collapse toggle — mijenjaj tekst dugmeta
	document.querySelectorAll('.video-toggle-btn').forEach(function (btn) {
		var target = document.querySelector(btn.dataset.bsTarget);
		if (!target) return;
		var originalHtml = btn.innerHTML;
		target.addEventListener('show.bs.collapse', function () {
			btn.innerHTML = "<i class='bx bx-chevron-up'></i> Sakrij";
		});
		target.addEventListener('hide.bs.collapse', function () {
			btn.innerHTML = originalHtml;
		});
	});

	// Snimi nacrt u localStorage
	$(document).on('click', '.video-snimi-btn', function () {
		var id  = parseInt($(this).data('id'));
		var q   = videoEditors[id];
		if (!q) return;
		localStorage.setItem('video_nacrt_' + id, q.root.innerHTML);
		$('.video-nacrt-ok[data-id="' + id + '"]').show();
		setTimeout(function () { $('.video-nacrt-ok[data-id="' + id + '"]').hide(); }, 2500);
		var badge    = document.querySelector('.video-nacrt-badge[data-id="' + id + '"]');
		if (badge) badge.style.display = 'inline';
		var brisiBtn = document.querySelector('.video-brisi-nacrt-btn[data-id="' + id + '"]');
		if (brisiBtn) brisiBtn.style.display = 'inline-block';
	});

	// Briši nacrt
	$(document).on('click', '.video-brisi-nacrt-btn', function () {
		var id       = parseInt($(this).data('id'));
		var initHtml = $(this).data('init') || '';
		localStorage.removeItem('video_nacrt_' + id);
		var q = videoEditors[id];
		if (q) q.root.innerHTML = initHtml;
		var badge = document.querySelector('.video-nacrt-badge[data-id="' + id + '"]');
		if (badge) badge.style.display = 'none';
		$(this).hide();
	});

	// Pošalji profesoru
	$(document).on('click', '.video-posalji-btn', function () {
		var id   = parseInt($(this).data('id'));
		var q    = videoEditors[id];
		var $btn = $(this);
		var $ok  = $('.video-ok[data-id="' + id + '"]');
		var $err = $('.video-error[data-id="' + id + '"]');

		$ok.hide();
		$err.hide();

		if (!q) { $err.text('Greška editora.').show(); return; }

		if (q.getText().trim() === '') {
			$err.text('Tekst ne sme biti prazan.').show();
			return;
		}

		$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

		$.ajax({
			url: 'ajax_odgovori_video.php',
			type: 'POST',
			data: { domaci_id: id, tekst: q.root.innerHTML },
			success: function (resp) {
				try {
					var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
					if (data.error) {
						$err.text(data.error).show();
						$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
						return;
					}
					localStorage.removeItem('video_nacrt_' + id);
					var badge    = document.querySelector('.video-nacrt-badge[data-id="' + id + '"]');
					var brisiBtn = document.querySelector('.video-brisi-nacrt-btn[data-id="' + id + '"]');
					if (badge)    badge.style.display    = 'none';
					if (brisiBtn) brisiBtn.style.display = 'none';
					$ok.show();
					setTimeout(function () { $ok.hide(); }, 3000);
					$btn.prop('disabled', false).html('<i class="bx bx-refresh"></i> Ažurirati');
				} catch (e) {
					$err.text('Greška pri čitanju odgovora servera.').show();
					$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
				}
			},
			error: function () {
				$err.text('Greška pri komunikaciji sa serverom.').show();
				$btn.prop('disabled', false).html('<i class="bx bx-send"></i> Pošalji profesoru');
			}
		});
	});
	</script>
	<script>
	// Odbrojavanje za vremenski ogranicen kviz - automatska predaja kada vreme istekne
	var kvizOdbrojavanje = document.getElementById('kvizOdbrojavanje');
	if (kvizOdbrojavanje) {
		var kvizPreostalo = parseInt(kvizOdbrojavanje.dataset.preostalo, 10);

		var kvizPrikaziVreme = function () {
			var min = Math.floor(kvizPreostalo / 60);
			var sek = kvizPreostalo % 60;
			document.getElementById('kvizVreme').textContent = min + ':' + (sek < 10 ? '0' : '') + sek;
		};

		kvizPrikaziVreme();

		var kvizTajmer = setInterval(function () {
			kvizPreostalo--;

			if (kvizPreostalo <= 0) {
				clearInterval(kvizTajmer);
				document.getElementById('formaKviz').submit();
				return;
			}

			kvizPrikaziVreme();
		}, 1000);
	}
	</script>
</body>

</html>
