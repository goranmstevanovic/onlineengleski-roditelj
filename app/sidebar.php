<style>
/* =====================================
   LIGHT NAVY SIDEBAR SYSTEM
===================================== */

:root {
  --sidebar-bg: #eaf1f8;
  --sidebar-hover: #dbe7f3;
  --sidebar-text: #1e293b;
  --sidebar-icon: #334155;
  --sidebar-active: #1e3a8a;
  --sidebar-active-text: #ffffff;
  --sidebar-border: #d0d9e4;
}

[data-theme="dark"] {
  --sidebar-bg: #0f172a;
  --sidebar-hover: #1e293b;
  --sidebar-text: #e2e8f0;
  --sidebar-icon: #94a3b8;
  --sidebar-active: #3b82f6;
  --sidebar-active-text: #ffffff;
  --sidebar-border: rgba(255,255,255,0.08);
}

.sidebar-wrapper {
  background: var(--sidebar-bg) !important;
  border-right: 1px solid var(--sidebar-border);
}

.sidebar-wrapper a,
.sidebar-wrapper .menu-title {
  color: var(--sidebar-text) !important;
}

.sidebar-wrapper .parent-icon i {
  color: var(--sidebar-icon) !important;
}

.sidebar-wrapper .metismenu a:hover {
  background: var(--sidebar-hover);
  border-radius: 6px;
}

.sidebar-wrapper .mm-active > a,
.sidebar-wrapper .metismenu .active > a {
  background: var(--sidebar-active) !important;
  color: var(--sidebar-active-text) !important;
  border-radius: 6px;
}

.sidebar-wrapper .metismenu li.mm-active > a:not(.has-arrow) i,
.sidebar-wrapper .metismenu li.mm-active > a:not(.has-arrow) .menu-title {
  color: var(--sidebar-active-text) !important;
}

.sidebar-wrapper .metismenu li.mm-active > a.has-arrow i,
.sidebar-wrapper .metismenu a.has-arrow[aria-expanded="true"] i,
.sidebar-wrapper .metismenu li.mm-active > a.has-arrow .menu-title,
.sidebar-wrapper .metismenu a.has-arrow[aria-expanded="true"] .menu-title,
.sidebar-wrapper .metismenu li.mm-active > a.has-arrow::after,
.sidebar-wrapper .metismenu a.has-arrow[aria-expanded="true"]::after {
  color: var(--sidebar-active-text) !important;
}

.sidebar-wrapper .metismenu li.mm-active > a.has-arrow,
.sidebar-wrapper .metismenu a.has-arrow[aria-expanded="true"] {
  background: var(--sidebar-active) !important;
  color: #fff !important;
}

.dete-badge {
  display: block;
  padding: 10px 16px;
  margin: 8px 12px 4px;
  border-radius: 8px;
  background: var(--sidebar-hover);
  color: var(--sidebar-text) !important;
  font-size: 13px;
  font-weight: 600;
}
</style>
<div class="sidebar-wrapper" data-simplebar="true" >

			<div class="sidebar-header"  >
				<div>
					<img src="<?=$home_url?>assets/images/sms1.png" class="logo-icon" style="width:60%;" alt="logo icon">
				</div>
				<div>
					<h4 class="logo-text"></h4>
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu"  >
				<li>
					<a href="<?php echo $home_url; ?>index">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Moja deca</div>
					</a>
				</li>
				<li>
					<a href="<?php echo $home_url; ?>zbirna_kartica">
						<div class="parent-icon"><i class='bx bx-wallet'></i>
						</div>
						<div class="menu-title">Zbirna finansijska kartica</div>
					</a>
				</li>

<?php
// Ako je u URL-u prisutan djak_id, proveravamo da li stvarno pripada
// ulogovanom roditelju i, ako da, prikazujemo puni meni za TO dete.
// Sidebar se ucitava pre nego sto glavni sadrzaj stranice pozove
// config/resolve_djak.php, pa ovde radimo istu proveru samostalno
// (lagana, samo za potrebe prikaza menija).
$sidebar_djak_id = isset($_GET['djak_id']) ? (int)$_GET['djak_id'] : 0;

if($sidebar_djak_id > 0 && !empty($_SESSION['user_id'])){
    include_once 'config/database.php';
    $sidebar_db = (new Database())->getConnection();
    $sidebar_stmt = $sidebar_db->prepare("
        SELECT dj.firstname, dj.lastname
        FROM roditelji_djaci rd
        INNER JOIN djaci dj ON dj.id = rd.fk_djak
        WHERE rd.fk_roditelj = :fk_roditelj AND rd.fk_djak = :fk_djak AND rd.active = 1 AND dj.status = 1
        LIMIT 1
    ");
    $sidebar_stmt->execute([
        ':fk_roditelj' => $_SESSION['user_id'],
        ':fk_djak' => $sidebar_djak_id,
    ]);
    $sidebar_dete = $sidebar_stmt->fetch(PDO::FETCH_ASSOC);

    if($sidebar_dete){
        $dq = 'djak_id=' . $sidebar_djak_id;
        echo '<span class="dete-badge"><i class="bx bx-user"></i> ' . htmlspecialchars(trim($sidebar_dete['firstname'] . ' ' . $sidebar_dete['lastname'])) . '</span>';
        ?>
				<li>
					<a href="<?php echo $home_url; ?>dashboard?<?php echo $dq; ?>">
						<div class="parent-icon"><i class='bx bx-task'></i>
						</div>
						<div class="menu-title">Zadaci i dokumenti</div>
					</a>
				</li>
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class='bx bx-calendar'></i>
						</div>
						<div class="menu-title">Časovi</div>
					</a>
					<ul>
						<li> <a href="<?php echo $home_url; ?>odrzani_casovi?<?php echo $dq; ?>"><i class='bx bx-radio-circle'></i>Održani</a>
						</li>
						<li> <a href="<?php echo $home_url; ?>zakazani_casovi?<?php echo $dq; ?>"><i class='bx bx-radio-circle'></i>Zakazani</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="<?php echo $home_url; ?>oceni_profesora?<?php echo $dq; ?>">
						<div class="parent-icon"><i class='bx bx-star'></i>
						</div>
						<div class="menu-title">Oceni profesora</div>
					</a>
				</li>

				<li class="menu-label">Finansije</li>

					<ul>
						<li> <a href="<?php echo $home_url; ?>kartica_djak?<?php echo $dq; ?>"><i class='bx bx-radio-circle'></i>Finasijska kartica</a>
						</li>
					</ul>
					<ul>
						<li> <a href="<?php echo $home_url; ?>mesecna_zaduzenja?<?php echo $dq; ?>"><i class='bx bx-radio-circle'></i>Zaduženja</a>
						</li>
					</ul>
		<?php
    }
}
?>
			</ul>
			<!--end navigation-->
		</div>
