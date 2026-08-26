 <link rel="shortcut icon" href="images/kalen.png">
<?php
$page_title = "Login";
include_once "config/core.php";
include_once "layout_head_login.php";

echo "<div class='col-sm-6 col-md-4 col-md-offset-4'>";

$action = $_GET['sent'] ?? '';

if($action){
    echo "<div class='alert alert-success'>
        Ako email postoji u sistemu, poslat je link za reset lozinke.<br>
        Proverite i Spam folder.
    </div>";
}

echo "<div class='account-wall'>";
echo "<img style='width:80%; margin-left:10%;' src='images/sms1.png'>";

echo "<form method='POST' action='send_reset_link.php' class='form-signin'>";

echo "<input type='email' name='email' class='form-control' placeholder='Email' required autofocus>";

echo "<input type='submit' class='btn btn-lg btn-danger btn-block' value='Pošalji link za reset'>";

echo "</form>";

echo "<div style='margin-top:10px; text-align:center;'>
    <a href='login.php'>Nazad na login</a>
</div>";

echo "</div>";
echo "</div>";

include_once "layout_foot.php";
?>
