<html>
<head>
    <title> Kartica djak </title>
   
    <link rel="stylesheet" type="text/css" href="tigrakal/tcal.css" />
    <script type="text/javascript" src="tigrakal/tcal.js"></script>
   <!-- <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script> 
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>  -->
    <script src="ajax/ajax321jquery.min.js"></script>
    <script type="ajax/jquery15.min.js"></script> 
	<style>
		a,a label {
		cursor: pointer;
	}
	</style>
</head>
<body>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 6.11.2019
 * Time: 13:13
 */
// $aktuelni_djak / $idd su vec postavljeni u kartica_djak.php (wrapper), PRE
// bilo kakvog HTML izlaza, preko config/resolve_djak.php - tamo se i proverava
// da izabrano dete (?djak_id=) stvarno pripada ulogovanom roditelju. Namerno
// se ovde NE cita $_GET['id']/$_POST['id'] ponovo (to bi zaobislo tu proveru).
include_once 'config/core.php';
include_once 'config/database.php';
include_once 'config/autoload.php';
//echo 'Current PHP version: ' . phpversion();
// core configuration
//include_once "config/core.php";

// set page title
$page_title = "";

// include login checker
// include_once "login_checker.php";

// // include classes
// include_once 'config/database.php';
 include_once 'config/funkcije.php';
;
if(isset($_POST['id'])){
   // include_once "layout_head1.php";
}else{
   // include_once "layout_head2.php";
}

$database = new Database();
$db = $database->getConnection();
$grupa = new grupa($db);
$djak = new djak($db);
$termin = new kalendar($db);
$profesor = new user($db);
$zaduzenje = new zaduzenje($db);
$povezivanje = new povezivanje($db);
$uplata = new uplata($db);
$nivo_znanja = new nivo_znanja($db);
$jezik = new jezik($db);
$porodica = new porodica($db);
$cenovnik = new cenovnik($db);
$evidencija = new evidencija($db);
$stmt = $djak->read_one_djak("djaci", $idd);
$row_category_djak = $stmt->fetch(PDO::FETCH_ASSOC);
extract($row_category_djak);



// registration form HTML
// code when form was submitted
// if form was posted

?>
<script>
$('textarea').each(function () {
  this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
}).on('input', function () {
  this.style.height = 'auto';
  this.style.height = (this.scrollHeight) + 'px';
});


</script>
    <div class="col-md-12" style='width:100%; margin-left:0%;'  >
        <div class='col-md-12' style = "width:100%; margin-left: 0%; margin-right: 0%;"  >
        <table class="table table-responsive">
        <tr> 
            <th><h4>Finansijska Kartica:</h4>  </th>
         
        </tr>
    </table>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">

        <!-- Gornji red: osnovni podaci + saldo -->
        <div class="row align-items-start g-3">

            <!-- Leva strana - podaci -->
            <div class="col-lg-8">

                <div class="d-flex flex-wrap gap-4">

                    <div>
                        <small class="text-muted">Ime i prezime</small>
                        <h5 class="mb-0">
                            <?= $firstname . " " . $lastname ?>
                            <?php if($status == TRUE): ?>
                                <span class="badge bg-success ms-2">AKTIVAN</span>
                            <?php else: ?>
                                <span class="badge bg-danger ms-2">NEAKTIVAN</span>
                            <?php endif; ?>
                        </h5>
                    </div>

                    <div>
                        <small class="text-muted">Telefon</small>
                        <div><?= $contact_number ?></div>
                    </div>

                    <div>
                        <small class="text-muted">Email</small>
                        <div><?= $email ?></div>
                    </div>

                </div>

            </div>

            <!-- Desna strana - Saldo -->
            <div class="col-lg-4">

                <div class="p-3 rounded-3 bg-light border text-end shadow-sm">
                    <div class="mb-2">
                        <small class="text-muted">Ukupan saldo</small>
                        <h4 class="mb-0 fw-bold">
                            <span id="saldo_gore"></span>
                        </h4>
                    </div>

                    <div>
                        <small class="text-muted">Današnji saldo</small>
                        <h6 class="mb-0 text-purple fw-bold">
                            <span id="danasnji_saldo_gore"></span>
                        </h6>
                    </div>
                </div>

            </div>

        </div>

        <!-- Komentari -->


    </div>
</div>
            <div class="row" >
           
            <?php
            $broj_grupa_u_kojima_je = $povezivanje->count_grupa($idd);
            $kartica = array();
            if($broj_grupa_u_kojima_je >= 0)
            {
                    //echo"<td> Grupa:</td> ";
               $ttss = $povezivanje->read_all_group_students_za_finasijsku($idd);
                // var_dump($ttss);
                //   $ttss = $evidencija->read_all_grupe_prisustvo_djak($idd);
                //  var_dump($ttss);
                $i = -1;
                while ($row_category_grupe = $ttss->fetch(PDO::FETCH_ASSOC)) 
                {
                   $ta_grupa = $row_category_grupe['fk_grupa'];
                    //  echo "daka:", $idd;
                    $sst = $grupa->read_one_grupa1($row_category_grupe['fk_grupa']);
                    $row_citanje_grupe = $sst->fetch(PDO::FETCH_ASSOC);
                    if($row_citanje_grupe['status'] == false){
                        $status_grupe = 'neaktivna';
                    }else{
                        $status_grupe = 'aktivna';
                    }
                    extract($row_citanje_grupe);

                    $stm_jezik = $jezik->read_one_jezik($fk_jezik,'jezik');
                    $row_jezik = $stm_jezik->fetch(PDO::FETCH_ASSOC);
                    $fk_jezik = $row_jezik['ime'];
                    
                    $stm_nivo = $nivo_znanja->read_one_nivo($nivo,'nivo_znanja');
                    $row_nivo_znanja = $stm_nivo->fetch(PDO::FETCH_ASSOC);
                    $nivo = $row_nivo_znanja['ime'];

                    if($fk_nacin_zaduzivanja == 1 or $fk_nacin_zaduzivanja == 2){
                        $vrsta_zaduzenja = "Po kursu";
                      
                        //  $smtp = $zaduzenje->read_all_zaduzenja_for_student_group($idd,$row_category_grupe['fk_grupa']);
                        $smtp = $zaduzenje->read_all_zaduzenja_for_student_group($idd,$row_category_grupe['fk_grupa']);
                        while ($row_zaduzenje = $smtp->fetch(PDO::FETCH_ASSOC)) {
                            $i++;
                            $kartica[$i]["id"]=$row_zaduzenje["id"];
                            $kartica[$i]["fk_grupa"]=$row_zaduzenje["fk_grupa"];
                            $kartica[$i]["iznos"]=$row_zaduzenje["iznos"];
                            $kartica[$i]["od_datuma"]=$row_zaduzenje["od_datuma"];
                            $kartica[$i]["do_datuma"]=$row_zaduzenje["do_datuma"];
                            $kartica[$i]["zaduzio"]=$row_zaduzenje["zaduzio"];
                            $kartica[$i]["created"]=$row_zaduzenje["valuta"] ?? null;
                            $kartica[$i]["vrsta"]="zaduzenje po kursu";
                            $kartica[$i]["komentar"]=$row_zaduzenje["komentar"];
                        }

                     //   $kartica = array_merge($kartica,$kartica1);

                    }
                    if( $fk_nacin_zaduzivanja == 2){
                        $vrsta_zaduzenja = "Po času";
                        $popust_djaka = floatval($row_category_grupe['popust'] ?? 0);
                        $smtp_zaduzenje = $evidencija->read_all_all_prisustvo_djak_kartica($idd, $row_category_grupe['fk_grupa']);
                        while ($row_zaduzenje = $smtp_zaduzenje->fetch(PDO::FETCH_ASSOC)) {
                            $i++;
                            $kartica[$i]["id"]=$row_zaduzenje["id"];
                            $kartica[$i]["fk_grupa"]=$row_zaduzenje["fk_grupa"];
                            if(!empty($fiksna_cena_za_djake)){
                                $bazna_cena = (float)$fiksna_cena_za_djake;
                                $kartica[$i]["popust"] = 0;
                            } else {
                                $bazna_cena = $cenovnik->odredi_zaduzenje($row_zaduzenje)["iznos"];
                                if($popust_djaka > 0){
                                    $bazna_cena = round($bazna_cena * (1 - $popust_djaka / 100), 2);
                                }
                                $kartica[$i]["popust"] = $popust_djaka;
                            }
                            // za individualce i poluindividualce
                           if($row_zaduzenje["velicina"] == 1 OR $row_zaduzenje["velicina"] == 2 ){
                                if($row_zaduzenje["prisutan"] == 1 OR $row_zaduzenje["opravdao_otsustvo"] == 0){
                                    $kartica[$i]["iznos"] = $bazna_cena;
                                }else{
                                    $kartica[$i]["iznos"] = 0;
                                }
                            }else{
                                if($row_zaduzenje["vrtic"] == 1 ){
                                    if($row_zaduzenje["prisutan"] == 1){
                                        $kartica[$i]["iznos"] = $bazna_cena;
                                    }else{
                                        $kartica[$i]["iznos"] = 0;
                                    }

                                }else{
                                    $kartica[$i]["iznos"] = $bazna_cena;
                                }

                            }

                            $kartica[$i]["od_datuma"]= date('d.m.Y H:i', strtotime($row_zaduzenje["start"]));
                            $kartica[$i]["do_datuma"]= date('H:i', strtotime($row_zaduzenje["end"]));
                            if($row_zaduzenje["fk_profesor"] != NULL ){
                                $kartica[$i]["zaduzio"]=$row_zaduzenje["fk_profesor"];
                            }else{
                                $kartica[$i]["zaduzio"]=$row_zaduzenje["fk_profesor_grupa"];
                            }

                            $kartica[$i]["created"]=substr($row_zaduzenje["start"],0,10);
                            $kartica[$i]["vrsta"]="zaduzenje po casu";
                            $kartica[$i]["komentar"]=$row_zaduzenje["komentar"];
                        }
                    }
           
                    // echo "nivo=", $nivo;
                    echo "<div class='flex card col-md-3'>",$fk_jezik, "&nbsp;", $nivo, "&nbsp;<b> [ ", $alias," ] / ",$vrsta_zaduzenja,"/",$status_grupe,"</b>",
                         ($fk_nacin_zaduzivanja == 2 && $popust_djaka > 0 ? " <span class='badge bg-warning text-dark'>-{$popust_djaka}%</span>" : ""),
                         "<br/>";
                    $row_category_grupe['created'] = vreme_u_nase_vreme($row_category_grupe['created']);
                    if($row_category_grupe['status'] == true ){
                        echo "od: ",$row_category_grupe['created'];
                    }else{
                        echo $row_category_grupe['created'],"-",date('d.m.Y H:m', strtotime($row_category_grupe['deleted']));
                    }
                   echo "</div>";

                }
            }
            ?>
         
                
</div>
        </div>
        <div class='col-md-12' id="karticatab" style = "width:100%; margin-left: -2%; margin-right: 0%;">
            <?php
            //   $uplata = array();
            // Citamo sve uplate i zaduzenja za zadatog studenta
            $ssdj = $uplata-> read_all_uplate_students1($idd);
            //   $smtp = $zaduzenje->read_all_zaduzenja_for_students($idd);
            //  $i=-1;
            while ($row_uplata = $ssdj->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                $kartica[$i]['id'] = $row_uplata['id'];
                $kartica[$i]["datum_generisanja"] = $row_uplata["datum_generisanja"];
                $kartica[$i]["iznos"]=$row_uplata["iznos"];
                $kartica[$i]["fk_grupa"]=$row_uplata["fk_grupa"];
                $kartica[$i]["upisao"]=$row_uplata["upisao"];
                $kartica[$i]["created"]=$row_uplata["datum_generisanja"];
                $kartica[$i]["vrsta"]="uplata";
                $kartica[$i]["racun"]=$row_uplata["racun"];
                $kartica[$i]["broj_priznanice"]=$row_uplata["broj_priznanice"];
                $kartica[$i]["komentar"]=$row_uplata["komentar"];
            }

          
            // Napunjen niz sa zajenickim podacima iz zaduzenja i uplata
        if( isset($i) AND $i>=0) {
                foreach ($kartica as $key => $part) {
                    $sort[$key] = strtotime($part['created']);
                }

                //  $created= array_column($kartica, 'created');
                //$city= array_column($data, 'city');
            //  array_multisort($created, SORT_ASC, $kartica);
            array_multisort( array_column($kartica, "created"), SORT_ASC, $kartica );
            // Sortiran niz
                ?>
            <form method="POST" action ="kartica_djak.php?id=<?php echo $idd; ?>"  >
                <table class="table table-hover table-bordered text-center">
                    <tr bgcolor="#f8efef" style="text-align:center;">
                        <th style="text-align:center;"></th>
                        <th style="text-align:center;">Datum</th>
                                            
                        
                        <th style="text-align:center;">upl/zad</th>
                        <th style="text-align:center;">Saldo</th>
                        
                    </tr>

                        <?php
                        $saldo = 0;
                        $danasnji_saldo = 0;
                        $suma_uplata = 0 ;
                        $suma_zaduzenja = 0;
                        for ($b = 0; $b <= $i; $b++) {
                            if ($kartica[$b]["vrsta"] == "uplata") 
                            { 
                                if($kartica[$b]["datum_generisanja"] <= date('Y-m-d')){
                                    $color = "#F2F2F2";
                                }else{
                                    $color = "#FFFFFF";
                                }
                                ?>
                                <tr style='background-color:<?=$color ?>;' ><td><i class="bi bi-plus-lg"></i> </td>
                                <?php
                                $karticab = $kartica[$b]["datum_generisanja"] ?? null;
                                $kartica[$b]["datum_generisanja"] = datum_u_nas_datum($kartica[$b]["datum_generisanja"]);
                                
                               
                              
                                $iisi = $profesor->read_name_user($kartica[$b]["upisao"],"users");
                                $row_upisao = $iisi->fetch(PDO::FETCH_ASSOC);
                                
                                $ssdk = $grupa->read_one_grupa1($kartica[$b]["fk_grupa"]);
                                $row_citanje_jedne_grupe = $ssdk->fetch(PDO::FETCH_ASSOC);
                                extract($row_citanje_jedne_grupe);
                                // echo "nivo=", $nivo;
                                $stm_jezik = $jezik->read_one_jezik($fk_jezik,'jezik');
                                $row_jezik = $stm_jezik->fetch(PDO::FETCH_ASSOC);
                                $fk_jezik = $row_jezik['alias'];


                                
                                $stm_nivo = $nivo_znanja->read_one_nivo($nivo,'nivo_znanja');
                                $row_nivo_znanja = $stm_nivo->fetch(PDO::FETCH_ASSOC);
                                $nivo = $row_nivo_znanja['ime'];

                                echo "<td>", $kartica[$b]["datum_generisanja"],"<br>",$fk_jezik, "&nbsp;", $nivo, "&nbsp; <br>  ", $alias," ]";
                                echo "<td>" , $kartica[$b]["iznos"]; 
                                if($kartica[$b]["racun"] == TRUE){echo "<b style='color: red;'> &nbsp; rač.</b>";}
                                if($kartica[$b]["broj_priznanice"] != null){echo "<br/><b style='color: purple;'> &nbsp; Br.priz.",$kartica[$b]["broj_priznanice"],"</b>";}
                                $suma_uplata = $suma_uplata + $kartica[$b]["iznos"];
                                echo "</td>";
                               
                                $saldo = $saldo + $kartica[$b]["iznos"];
                                if($karticab <= date("Y-m-d")){
                                    $danasnji_saldo = $danasnji_saldo + $kartica[$b]["iznos"];
                                //  echo $kartica[$b]["datum_generisanja"];
                                }
                            // $saldo1 = number_format((float)$saldo, 2, '.', '');
                                ?>
                                <td <?php if ($saldo < 0) {echo "style='color:red;'";} ?> > <?php if ($saldo >= 0){echo "&nbsp;", number_format((float)$saldo, 2, '.', '');} else {echo number_format((float)$saldo, 2, '.', '');} ?></td>
                                        <td style='display:none' ><a class="remove" style='color:red;' data-urowid="<?php echo $kartica[$b]['id']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
                                        <!-- <button class="btn btn-danger btn-sm remove" ><img style="border:0; outline: none;" src="kanta32.png" width=16 height=16>  </button></td> -->
                                    <?php
                                echo "</tr>";
                            } else {  
                                if($kartica[$b]["created"] <= date('Y-m-d')){
                                    $color = "#F2F2F2";
                                }else{
                                    $color = "#FFFFFF";
                                }
                                
                                ?>
                                <tr style='background-color:<?=$color ?>;' >
                                <td style="color:red;"> 
                                    <!-- <a> Zaduženje</a> -->
                                    <?php 
                                       //  echo $kartica[$b]["vrsta"]; 
                                    if($kartica[$b]["vrsta"] == "zaduzenje po kursu"){?>
                                        <i class="bi bi-dash-lg"></i></td>
                                        <?php
                                    }else{ ?>
                                        <i class="bi bi-dash-lg"></i>
                                       
                                    </td>
                                    <?php
                                    }
                                    ?>
                                </td>
                                    <?php
                                    if($kartica[$b]["created"] != null){
                                        $karticab = $kartica[$b]["created"];
                                        $kartica[$b]["created"] =  datum_u_nas_datum($kartica[$b]["created"]);
                                    }
                                
                                
                                // $kartica[$b]["od_datuma"] = datum_u_nas_datum($kartica[$b]["od_datuma"]);
                                // $kartica[$b]["do_datuma"] = datum_u_nas_datum($kartica[$b]["do_datuma"]);
                             
                                $iisi = $profesor->read_name_user($kartica[$b]["zaduzio"],"users");
                                $row_upisao = $iisi->fetch(PDO::FETCH_ASSOC);
                                
                                $ssdk = $grupa->read_one_grupa1($kartica[$b]["fk_grupa"]);
                                $row_citanje_jedne_grupe = $ssdk->fetch(PDO::FETCH_ASSOC);
                                extract($row_citanje_jedne_grupe);
                                // echo "nivo=", $nivo;
                                $stm_jezik = $jezik->read_one_jezik($fk_jezik,'jezik');
                                $row_jezik = $stm_jezik->fetch(PDO::FETCH_ASSOC);
                                $fk_jezik = $row_jezik['alias'];


                                
                                $stm_nivo = $nivo_znanja->read_one_nivo($nivo,'nivo_znanja');
                                $row_nivo_znanja = $stm_nivo->fetch(PDO::FETCH_ASSOC);
                                $nivo = $row_nivo_znanja['ime'];

                                echo "<td>", $kartica[$b]["created"],"<br>" ,$fk_jezik, "&nbsp;", $nivo, "&nbsp; <br> ", $alias," ]";


                                
                                $iznoszaduznja = $kartica[$b]["iznos"];
                                $iznoszaduznja = number_format((float)$iznoszaduznja, 2, '.', '');
                                $popust_prikaz = $kartica[$b]["popust"] ?? 0;
                                echo "<td style='color:red;'>", $iznoszaduznja;
                                if($popust_prikaz > 0){
                                    echo " <small class='text-warning fw-bold'>-{$popust_prikaz}%</small>";
                                }
                                echo "</td>";
                                $suma_zaduzenja = $suma_zaduzenja + $kartica[$b]["iznos"];
                                $saldo = $saldo - $kartica[$b]["iznos"];
                                if($karticab <= date("Y-m-d")){
                                    $danasnji_saldo = $danasnji_saldo - $kartica[$b]["iznos"];
                                    //echo date("Y-m-d"),"/",$kartica[$b]["created"],"<br/>";

                                }


                                $saldo1 = number_format((float)$saldo, 2, '.', '');
                                ?>
                                
                                <td <?php if ($saldo < 0) {
                                    echo "style='color:red;'";
                                } ?> > <?php if ($saldo >= 0) {
                                        echo "&nbsp;", $saldo1;
                                    } else {
                                        echo $saldo1;
                                    } ?></td>
                                <td style='display:none' >
                                <?php  
                                if($kartica[$b]["vrsta"] == "zaduzenje po kursu"){?>    
                                <a class="remove" style='color:red;' data-zrowid="<?php echo $kartica[$b]['id']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
                                <?php } ?>
                                <!-- <button class="btn btn-danger btn-sm remove" ><img style="border:0; outline: none;" src="kanta32.png" width=16 height=16>  </button></td> -->
                                <?php
                                echo "</tr>";

                            }
                        }
                        ?>
                    <tr style='display:none;'><td colspan="2">Ukupno</td><td><?=$suma_uplata ?></td><td style='color:red;'><?=round($suma_zaduzenja, 2 ) ?></td></tr>
                    <tr style='display:none;'><td colspan="6"></td><td id='danasnji_saldo_dole'><?=number_format((float)$danasnji_saldo, 2, '.', ' ') ?></td> <td id='saldo_dole'><?=number_format((float)$saldo, 2, '.', ' ') ?></td></tr>
                </table>
        </form>
    </div>
    </div>
    <script type="text/javascript">
    var firstDivContent = document.getElementById('saldo_dole');
    var secondDivContent = document.getElementById('saldo_gore');
    secondDivContent.innerHTML = firstDivContent.innerHTML;

    var firstDivContent1 = document.getElementById('danasnji_saldo_dole');
    var secondDivContent1 = document.getElementById('danasnji_saldo_gore');
    secondDivContent1.innerHTML = firstDivContent1.innerHTML;


    </script>
    <script>
        $(".zaduzenjeLink").click(function(){
             event.preventDefault();
            var home_url = "<?php echo $home_url; ?>";
            var kartica_id = this.getAttribute('data-id');
            var url = home_url + 'dogadjaj?id=' + kartica_id;
            window.open(url, '_blank');
            event.stopPropagation(); // Zaustavlja dalje širenje događaja na roditelje
        });
    </script>
            

            <script type="text/javascript">
			
                $(".remove").click(function(){
						var idiz = $(this).data("zrowid"); 
						var idiu = $(this).data("urowid");
                   /*  var idi = $(this).parents("tr").attr("name");
				    var zzi = $(this).parents("tr").attr("id"); */
                  /*  alert("Zdravo idiz" + idiz + "! Kako si danas?"); 
                    alert("Zdravo idiu" + idiu + "! Kako si danas?"); */
                    if (typeof idiu !== 'undefined')
                    {
						 var row = $(this).closest("tr");
                        if (confirm('Jeste li sigurni da želite da obrišete baš ovu uplatu ?')) 
						{
                            $.ajax({
								type: "POST",
                                url: "deleteuplata.php",
                                data: {
									operation: "remove",
									idiu: idiu
									},
								error: function () {
                                    alert('Nešto nije u redu sa brisanjem, kontaktiraj Goran-a');
                                },
								success: function (data) {
									row.remove();
								/*	alert("Uspešno obrisana uplata"); */
									location.reload(); 
                                }
                            });
                        }
                    }else
					{
						if (typeof idiz !== 'undefined')
						{
							 var row = $(this).closest("tr");
							if (confirm('Jeste li sigurni da želite da obrišete baš ovo zaduzenje ?')) 
							{
								$.ajax({
									type: "POST",
									url: "delete.php",
									data: {
										operation: "remove",
										idiz: idiz
										},
									error: function () {
										alert('Nešto nije u redu sa brisanjem, kontaktiraj Goran-a');
									},
									success: function (data) {
										 row.remove();
									/*	 alert("Uspešno obrisano zaduzenje"); */
										 location.reload(); 
									}
								});
							}
						}
						
					}
                });
			

            </script>
		<!--	<script type="text/javascript">
                $(".remove").click(function(){
                   var zidd = $(this).parents("tr").attr("id");
                 /*   alert("Zdravo id" + id + "! Kako si danas?"); */
                    alert("Zdravo zidd" + zidd + "! Kako si danas?"); 
                    if (typeof zidd !== 'undefined')
                    {
                        if (confirm('Jeste li sigurni da želite da obrišete baš ovo zaduzenje ?')) {
                            $.ajax({
                                url: 'delete.php',
                                type: 'GET',
                                data: {dzidd: zidd},
                                error: function () {
                                    alert('Nešto nije u redu sa brisanjem, kontaktiraj Goran-a');
                                },
                                success: function (data) {
                                    $("#"+zidd).remove();
                                    alert("Uspešno obrisano zaduzenje");
                                }
                            });
                        }
                    }
                });


            </script>
			-->


            <?php
        }else{
            echo "<div class='alert alert-info'>";
            echo "Nemamo evidentiranih uplata ni zaduzenja za ucenika";
            echo "</div>";
            
        } ?>


<?php

// include page footer HTML
include_once "layout_foot1.php";
?>



</body>
</html>
