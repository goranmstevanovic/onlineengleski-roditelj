<?php
include_once "config/core.php";
include_once "login_checker.php";
include_once "config/database.php";
include_once "config/autoload.php";
include_once "config/funkcije.php";
include_once "config/finansijska_kartica.php";

$database = new Database();
$db = $database->getConnection();

$roditelj_djak = new roditelj_djak($db);
$moj_id = $_SESSION['user_id'];

$stmt_deca = $roditelj_djak->read_all_djaci_za_roditelja($moj_id, true);
$deca = $stmt_deca->fetchAll(PDO::FETCH_ASSOC);

$sve_stavke = [];
$sve_grupe = [];

foreach($deca as $dete){
    $ime_deteta = trim($dete['firstname'] . ' ' . $dete['lastname']);
    $rezultat = sakupi_stavke_kartice($db, $dete['fk_djak']);

    foreach($rezultat['kartica'] as $stavka){
        $stavka['djak_ime'] = $ime_deteta;
        $stavka['djak_ime_kratko'] = $dete['firstname'];
        $sve_stavke[] = $stavka;
    }

    foreach($rezultat['grupe_info'] as $grupa_info){
        $grupa_info['djak_ime'] = $ime_deteta;
        $sve_grupe[] = $grupa_info;
    }
}

usort($sve_stavke, function($a, $b){
    return strtotime($a['created']) <=> strtotime($b['created']);
});

$grupa_obj = new grupa($db);
$jezik_obj = new jezik($db);
$nivo_obj = new nivo_znanja($db);

$saldo = 0;
$danasnji_saldo = 0;
$suma_uplata = 0;
$suma_zaduzenja = 0;
$redovi = [];

foreach($sve_stavke as $stavka){
    $red = $stavka;

    if($stavka['vrsta'] === 'uplata'){
        $saldo += $stavka['iznos'];
        if($stavka['datum_generisanja'] <= date('Y-m-d')){
            $danasnji_saldo += $stavka['iznos'];
        }
        $suma_uplata += $stavka['iznos'];
        $red['datum_prikaz'] = datum_u_nas_datum($stavka['datum_generisanja']);
    } else {
        $saldo -= $stavka['iznos'];
        if($stavka['created'] <= date('Y-m-d')){
            $danasnji_saldo -= $stavka['iznos'];
        }
        $suma_zaduzenja += $stavka['iznos'];
        $red['datum_prikaz'] = datum_u_nas_datum($stavka['created']);

        $red['grupa_prikaz'] = '';
        $sgrupa = $grupa_obj->read_one_grupa1($stavka['fk_grupa']);
        $row_grupa = $sgrupa->fetch(PDO::FETCH_ASSOC);
        if($row_grupa){
            $sjezik = $jezik_obj->read_one_jezik($row_grupa['fk_jezik'], 'jezik');
            $row_jezik = $sjezik->fetch(PDO::FETCH_ASSOC);
            $snivo = $nivo_obj->read_one_nivo($row_grupa['nivo'], 'nivo_znanja');
            $row_nivo = $snivo->fetch(PDO::FETCH_ASSOC);
            $red['grupa_prikaz'] = trim(($row_jezik['alias'] ?? '') . ' ' . ($row_nivo['ime'] ?? '') . ' [' . ($row_grupa['alias'] ?? '') . ']');
        }
    }

    $red['saldo_tekuci'] = $saldo;
    $redovi[] = $red;
}
?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="images/kalen.png" type="image/png"/>
	<link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
	<link href="assets/css/pace.min.css" rel="stylesheet"/>
	<script src="assets/js/pace.min.js"></script>
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/dark-theme.css"/>
	<link rel="stylesheet" href="assets/css/semi-dark.css"/>
	<link rel="stylesheet" href="assets/css/header-colors.css"/>
	<title>Zbirna finansijska kartica</title>
	<style>
		.zbirna-tabela td, .zbirna-tabela th {
			vertical-align: middle;
		}
		.zbirna-tabela td:nth-child(2) {
			max-width: 120px;
			white-space: normal;
			word-break: break-word;
		}
		@media (max-width: 480px) {
			.page-content {
				padding: 0.75rem 0.5rem 0.5rem 0.5rem;
			}
			.card-body {
				padding: 0.75rem !important;
			}
			.zbirna-tabela td, .zbirna-tabela th {
				padding: 0.35rem;
				font-size: 12px;
			}
			.zbirna-tabela td:nth-child(2) {
				max-width: 90px;
			}
			.zbirna-tabela .bi {
				font-size: 1.1rem !important;
			}
		}
	</style>
</head>

<body>
	<div class="wrapper">
		<?php include __DIR__ . '/app/sidebar.php'; ?>
		<header>
		<?php include __DIR__ . '/app/header.php'; ?>
		</header>

		<div class="page-wrapper">
			<div class="page-content">
				<div class="row">
					<div class="col-12 d-flex">
						<div class="card radius-10 w-100 shadow-sm">
							<div class="card-body p-4">

								<h4 class="mb-3">Zbirna finansijska kartica</h4>

								<?php if(empty($deca)){ ?>
									<div class="alert alert-warning">
										Trenutno nemate nijedno dete povezano sa vašim nalogom. Obratite se administraciji škole.
									</div>
								<?php } else { ?>

									<div class="row align-items-start g-3 mb-3">
										<div class="col-lg-8">
											<div class="d-flex flex-wrap gap-4">
												<?php foreach($deca as $dete){ ?>
													<div>
														<small class="text-muted">Dete</small>
														<h6 class="mb-0">
															<?php echo htmlspecialchars(trim($dete['firstname'] . ' ' . $dete['lastname'])); ?>
															<?php if($dete['djak_status'] == 1){ ?>
																<span class="badge bg-success ms-1">AKTIVAN</span>
															<?php } else { ?>
																<span class="badge bg-danger ms-1">NEAKTIVAN</span>
															<?php } ?>
														</h6>
													</div>
												<?php } ?>
											</div>
										</div>
										<div class="col-lg-4">
											<div class="p-3 rounded-3 bg-light border text-end shadow-sm">
												<div class="mb-2">
													<small class="text-muted">Ukupan saldo (sva deca)</small>
													<h4 class="mb-0 fw-bold"><?php echo number_format((float)$saldo, 2, '.', ' '); ?> RSD</h4>
												</div>
												<div>
													<small class="text-muted">Današnji saldo</small>
													<h6 class="mb-0 fw-bold"><?php echo number_format((float)$danasnji_saldo, 2, '.', ' '); ?> RSD</h6>
												</div>
											</div>
										</div>
									</div>

									<?php if(!empty($sve_grupe)){ ?>
										<div class="row g-2 mb-3">
											<?php foreach($sve_grupe as $gi){ ?>
												<div class="col-md-4">
													<div class="card p-2">
														<strong><?php echo htmlspecialchars($gi['djak_ime']); ?></strong> —
														<?php echo htmlspecialchars($gi['jezik'] . ' ' . $gi['nivo']); ?>
														[<?php echo htmlspecialchars($gi['alias']); ?>] /
														<?php echo htmlspecialchars($gi['vrsta_zaduzenja']); ?> /
														<?php echo htmlspecialchars($gi['status_grupe']); ?>
														<?php if($gi['popust'] > 0){ ?>
															<span class="badge bg-warning text-dark">-<?php echo $gi['popust']; ?>%</span>
														<?php } ?>
														<br><small class="text-muted"><?php echo htmlspecialchars($gi['trajanje']); ?></small>
													</div>
												</div>
											<?php } ?>
										</div>
									<?php } ?>

									<?php if(empty($redovi)){ ?>
										<div class="alert alert-info">Nemamo evidentiranih uplata ni zaduženja za vašu decu.</div>
									<?php } else { ?>
										<div class="table-responsive">
											<table class="table table-hover table-bordered text-center zbirna-tabela">
												<tr bgcolor="#f8efef">
													<th></th>
													<th>Datum</th>
													<th>upl/zad</th>
													<th>Saldo</th>
												</tr>
												<?php foreach($redovi as $red){ ?>
													<tr>
														<?php if($red['vrsta'] === 'uplata'){ ?>
															<td>
																<i class="bi bi-plus-lg fs-4 fw-bold text-success"></i><br>
																<small class="text-muted"><?php echo htmlspecialchars($red['djak_ime_kratko']); ?></small>
															</td>
															<td><?php echo $red['datum_prikaz']; ?></td>
															<td>
																<?php echo number_format((float)$red['iznos'], 2, '.', ' '); ?> RSD
																<?php if($red['racun']){ ?><b style="color:red;"> &nbsp;rač.</b><?php } ?>
																<?php if(!empty($red['broj_priznanice'])){ ?><br><b style="color:purple;">&nbsp;Br.priz. <?php echo htmlspecialchars($red['broj_priznanice']); ?></b><?php } ?>
															</td>
														<?php } else { ?>
															<td>
																<i class="bi bi-dash-lg fs-4 fw-bold text-danger"></i><br>
																<small class="text-muted"><?php echo htmlspecialchars($red['djak_ime_kratko']); ?></small>
															</td>
															<td><?php echo $red['datum_prikaz']; ?><br><small class="text-muted"><?php echo htmlspecialchars($red['grupa_prikaz']); ?></small></td>
															<td style="color:red;">
																<?php echo number_format((float)$red['iznos'], 2, '.', ' '); ?> RSD
																<?php if(!empty($red['popust'])){ ?><small class="text-warning fw-bold"> -<?php echo $red['popust']; ?>%</small><?php } ?>
															</td>
														<?php } ?>
														<td<?php if($red['saldo_tekuci'] < 0){ echo ' style="color:red;"'; } ?>>
															<?php echo number_format((float)$red['saldo_tekuci'], 2, '.', ' '); ?> RSD
														</td>
													</tr>
												<?php } ?>
											</table>
										</div>
									<?php } ?>

								<?php } ?>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="overlay toggle-icon"></div>
		<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<footer class="page-footer">
			<?php include __DIR__ . '/app/footer.php'; ?>
		</footer>
	</div>

	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<script src="assets/js/app.js"></script>
	<script>
		new PerfectScrollbar(".app-container")
	</script>
</body>

</html>
