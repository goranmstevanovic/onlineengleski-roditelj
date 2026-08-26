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
	<style>
.page-content .row {
    display: flex !important;
    flex-wrap: wrap !important;
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

$database = new Database();
$db = $database->getConnection();

// $moj_id je vec postavljen na vrhu (config/resolve_djak.php), na osnovu ?djak_id=

$query = "
SELECT 
    k.id,
    k.start,
    k.end,
    k.fk_grupa,
    g.alias AS alias,

    e.prisutan,
    e.opravdao_otsustvo

FROM kalendar k

JOIN povezivanje p 
    ON p.fk_grupa = k.fk_grupa

JOIN grupe g 
    ON g.id = k.fk_grupa

LEFT JOIN evidencija e
    ON e.termin_id = k.id
    AND e.djak_id = :djak_id

WHERE 
    p.fk_djak = :djak_id
    AND p.status = 1          -- 🔥 vrati ovo obavezno
    AND k.status = 2

    AND k.start >= DATE_SUB(NOW(), INTERVAL 30 DAY)

ORDER BY k.start DESC
";

$stmt = $db->prepare($query);
$stmt->bindParam(":djak_id", $moj_id);
$stmt->execute();

// grupisanje po danima
$casovi_po_danima = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dan = date('Y-m-d', strtotime($row['start']));
    $casovi_po_danima[$dan][] = $row;
}
?><div class="container mt-3">






<h5 class="mb-3"><u>Održani časovi (30 dana)</u></h5>

<?php if(empty($casovi_po_danima)){ ?>
    <div class="alert alert-info">Nema održanih časova.</div>
<?php } ?>

<?php foreach($casovi_po_danima as $dan => $casovi){ ?>

    <div class="mb-2">
        <h6 class="text-muted">
            <?= date('d.m.Y', strtotime($dan)) ?>
        </h6>
    </div>

    <div class="row">

    <?php foreach($casovi as $c){ ?>

        <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-3">
            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <!-- VREME -->
				<div class="d-flex justify-content-between align-items-center">

					<strong>
						🕒 <?= date('H:i', strtotime($c['start'])) ?> 
						- <?= date('H:i', strtotime($c['end'])) ?>
					</strong>
					<a href="#" 
					class="btn btn-outline-primary btn-sm btn-detalji"
					data-id="<?= $c['id'] ?>">
					Detalji
					</a>
					<!-- STATUS -->
					<?php if($c['prisutan'] === null){ ?>
						<span class="badge bg-secondary">N/A</span>

					<?php } elseif($c['prisutan'] == 1){ ?>
						<span style="color:green; font-size:18px;">✔️</span>

					<?php } else { ?>

						<?php if($c['opravdao_otsustvo'] == 1){ ?>
							<span style="color:orange; font-size:18px;">🟡</span>
						<?php } else { ?>
							<span style="color:red; font-size:18px;">❌</span>
						<?php } ?>

					<?php } ?>

				</div>

                    <!-- TRAJANJE -->
                    <div style="font-size:13px; color:#666;">
                        ⏱ 
                        <?php
                        $start = strtotime($c['start']);
                        $end = strtotime($c['end']);
                        echo round(($end - $start)/60) . " min";
                        ?>
                    </div>

                    <!-- GRUPA -->
                    <div style="font-size:13px;">
                        👥 Grupa : <?= $c['alias'] ?>
                    </div>

                </div>

            </div>
        </div>

    <?php } ?>

    </div>

<?php } ?>

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
	<div class="modal fade" id="casModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detalji časa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="modalContent">
        Učitavanje...
      </div>

    </div>
  </div>
</div>



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
	<script>
		document.querySelectorAll('.btn-detalji').forEach(btn => {
			btn.addEventListener('click', function(e){
				e.preventDefault();

				let id = this.dataset.id;

				fetch('get_cas_detalji.php?id=' + id)
					.then(res => res.text())
					.then(data => {

						document.getElementById('modalContent').innerHTML = data;

						let modal = new bootstrap.Modal(document.getElementById('casModal'));
						modal.show();
					});
			});
		});
		</script>
</body>

</html>