<?php
include_once "config/core.php";
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
	<title>Moja deca</title>
	<style>
		.dete-card {
			background: #fff;
			border: 1px solid #e2e8f0;
			border-radius: 12px;
			padding: 20px;
			height: 100%;
		}
		.dete-card .dete-header {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 16px;
		}
		.dete-card .dete-ime {
			font-size: 18px;
			font-weight: 600;
		}
		.dete-card .dete-vrsta {
			font-size: 13px;
			color: #64748b;
		}
		.dete-card .dete-foto {
			width: 56px;
			height: 56px;
			flex: 0 0 56px;
			object-fit: cover;
			border-radius: 8px;
			border: 2px solid #e2e8f0;
		}
		.dete-card .btn {
			margin: 3px 4px 3px 0;
		}
	</style>
</head>

<body>
	<div class="wrapper">

        <?php
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		include_once "login_checker.php";
        include_once  $site.'/app/sidebar.php';
        ?>

		<header>
		<?php include __DIR__ . '/app/header.php'; ?>
		</header>

		<div class="page-wrapper">
			<div class="page-content">
				<?php
				include_once "config/database.php";
				include_once "config/autoload.php";

				$database = new Database();
				$db = $database->getConnection();

				$roditelj_djak = new roditelj_djak($db);
				$moj_id = $_SESSION['user_id'];

				$stmt_deca = $roditelj_djak->read_all_djaci_za_roditelja($moj_id, true);
				$deca = $stmt_deca->fetchAll(PDO::FETCH_ASSOC);

				$action = $_GET['action'] ?? '';
				?>

				<div class="row">
					<div class="col-12">
						<h4 class="mb-3">Zdravo, <?php echo htmlspecialchars($_SESSION['firstname']); ?>!</h4>

						<?php if($action === 'nepoznato_dete'){ ?>
							<div class="alert alert-danger">To dete nije povezano sa vašim nalogom.</div>
						<?php } elseif($action === 'izaberi_dete'){ ?>
							<div class="alert alert-info">Izaberite dete ispod da vidite njegove podatke.</div>
						<?php } elseif($action === 'login_success'){ ?>
							<div class="alert alert-success">Uspešno ste se prijavili.</div>
						<?php } ?>

						<?php if(empty($deca)){ ?>
							<div class="alert alert-warning">
								Trenutno nemate nijedno dete povezano sa vašim nalogom. Obratite se administraciji škole.
							</div>
						<?php } else { ?>
							<p class="text-muted">Vaša deca u školi — izaberite dete za pregled njegovih podataka:</p>
							<p>
								<a class="btn btn-sm btn-outline-dark" target="_blank" href="<?php echo $home_url; ?>zbirna_kartica"><i class="bi bi-wallet2"></i> Zbirna finansijska kartica (sva deca)</a>
							</p>
							<div class="row g-3">
								<?php foreach($deca as $dete){ ?>
									<div class="col-md-4">
										<div class="dete-card">
											<?php
											$dete_avatar = (!empty($dete['photo']) && file_exists($student_photos_path . $dete['photo']))
												? $student_photos_url . $dete['photo']
												: 'images/avatar.jpg';
											?>
											<div class="dete-header">
												<img class="dete-foto" src="<?php echo $dete_avatar; ?>" alt="">
												<div>
													<div class="dete-ime"><?php echo htmlspecialchars(trim($dete['firstname'] . ' ' . $dete['lastname'])); ?></div>
													<div class="dete-vrsta"><?php echo $dete['djak_status'] == 1 ? 'Aktivan polaznik' : 'Neaktivan'; ?></div>
												</div>
											</div>
											<a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo $home_url; ?>dashboard?djak_id=<?php echo $dete['fk_djak']; ?>"><i class="bi bi-task"></i> Zadaci i dokumenti</a>
											<a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo $home_url; ?>kartica_djak?djak_id=<?php echo $dete['fk_djak']; ?>"><i class="bi bi-wallet2"></i> Finansijska kartica</a>
											<a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo $home_url; ?>odrzani_casovi?djak_id=<?php echo $dete['fk_djak']; ?>"><i class="bi bi-calendar-check"></i> Časovi</a>
											<a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo $home_url; ?>oceni_profesora?djak_id=<?php echo $dete['fk_djak']; ?>"><i class="bi bi-star"></i> Oceni profesora</a>
										</div>
									</div>
								<?php } ?>
							</div>
						<?php } ?>
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
