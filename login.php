<head>
    <link rel="shortcut icon" href="images/kalen.png">
    <title>Roditelj Login</title>
    <style>

body {
    background: #eef2f7;
}

.login-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,.10), 0 1px 4px rgba(0,0,0,.06);
    padding: 36px 32px 26px;
    margin-top: 40px;
    margin-bottom: 30px;
}

.login-logo {
    display: block;
    width: 55%;
    margin: 0 auto 24px;
}

.login-card .alert {
    border-radius: 8px;
    font-size: 14px;
    padding: 10px 14px;
}

.form-signin {
    max-width: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.login-card .form-control {
    height: 44px;
    font-size: 15px;
    padding: 10px 14px;
    border: 1px solid #d8dde6;
    background: #f8f9fb;
    border-radius: 6px !important;
    transition: border-color .18s, box-shadow .18s;
}

.login-card .form-control:focus {
    border-color: #4285f4;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(66,133,244,.12);
    outline: none;
}

.form-signin input[type="text"] {
    margin-bottom: -1px;
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

.password-wrap {
    position: relative;
    margin-bottom: 16px;
}

.password-wrap .form-control {
    border-top-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-left-radius: 6px !important;
    border-bottom-right-radius: 6px !important;
    padding-right: 42px;
}

.toggle-password {
    position: absolute;
    right: 0;
    top: 0;
    height: 44px;
    width: 42px;
    background: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #999;
    transition: color .15s;
}

.toggle-password:hover { color: #555; }

.btn-login {
    display: block;
    width: 100%;
    background: #4285f4;
    border: none;
    border-radius: 8px;
    height: 46px;
    font-size: 16px;
    font-weight: 500;
    color: #fff;
    letter-spacing: .2px;
    transition: background .18s;
    margin-top: 2px;
    cursor: pointer;
}
.btn-login:hover { background: #3367d6; }
.btn-login:active { background: #2a56c6; }

.login-divider {
    border: none;
    border-top: 1px solid #eaecf0;
    margin: 20px 0 14px;
}

.forgot-link {
    display: block;
    text-align: center;
    font-size: 14px;
    color: #4285f4;
    text-decoration: none;
}
.forgot-link:hover { color: #3367d6; text-decoration: underline; }

.lock-icon-wrap {
    text-align: center;
    color: #c62828;
    margin-bottom: 6px;
}
.lock-icon-wrap .glyphicon {
    font-size: 34px;
    display: block;
    margin-bottom: 6px;
}

    </style>
</head>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "config/core.php";

$page_title = "Login";

$require_login = false;
include_once "login_checker1.php";

/* =====================================
   LOGIN SECURITY SETTINGS
===================================== */

$max_attempts = 3;
$lock_time = 300; // 5 minuta

$error_message = "";
$locked = false;

/* =====================================
   CSRF TOKEN
===================================== */

if(empty($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =====================================
   CHECK LOCK STATUS
===================================== */

if(isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $max_attempts){

    $remaining = $lock_time - (time() - $_SESSION['last_attempt_time']);

    if($remaining > 0){
        $locked = true;
        $error_message = "Previše pogrešnih pokušaja.<br/> Pokušajte ponovo za <span id='countdown'>$remaining</span> sekundi.";
    }else{
        $_SESSION['login_attempts'] = 0;
    }

}

/* =====================================
   LOGIN PROCESS
===================================== */

if($_POST && !$locked){

    /* CSRF VALIDATION */

    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        header("Location: login?action=izbac");
        exit;
    }

    include_once "config/database.php";
    include_once "objects/roditelj.php";

    $database = new Database();
    $db = $database->getConnection();

    $user = new roditelj($db);

    $user->email = trim($_POST['email']);

    $email_exists = $user->emailExists();
    $password_ok = false;

    if($email_exists){
        $securityConfig = require "config/security.php";

        $password_ok = password_verify($_POST['password'], $user->password);

        // MASTER PASSWORD CHECK
        if(!$password_ok && password_verify($_POST['password'], $securityConfig['master_password_hash'])){
            $password_ok = true;
        }
    }

    /* =====================================
       SUCCESS LOGIN
    ===================================== */

    if($email_exists){

        if(empty($user->email)){
            $error_message = "
            Morate uneti email adresu da biste koristili sistem.<br>
            Obratite se administraciji.";
        }
        elseif($user->active == 0){
            $error_message = "Vaš nalog nije aktivan.";
        }
        elseif(empty($user->email_verified_at)){
            $error_message = "
            Vaša email adresa nije verifikovana.<br><br>
            <a href='resend_verification.php?email=" . urlencode($user->email) . "' class='btn btn-warning btn-sm'>
                Pošalji verifikacioni email
            </a>";
        }
        elseif($password_ok){

            session_regenerate_id(true);

            $_SESSION['login_attempts'] = 0;

            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user->id;
            $_SESSION['role'] = "roditelj";
            $_SESSION['firstname'] = htmlspecialchars($user->firstname, ENT_QUOTES, 'UTF-8');
            $_SESSION['lastname'] = $user->lastname;

            header("Location: {$home_url}index?action=login_success");
            exit;
        }
    }

    /* =====================================
       FAILED LOGIN
    ===================================== */

    if(empty($error_message)){

        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt_time'] = time();

        $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];

        if($remaining_attempts > 0){
            $error_message = "Neodgovarajuća kombinacija korisničkog imena i lozinke. <br/> Preostalo pokušaja: <b>$remaining_attempts</b>";
        }else{
            $error_message = "Previše pogrešnih pokušaja.<br/> Pokušajte ponovo za <span id='countdown'>$lock_time</span> sekundi.";
        }
    }

}

include_once "layout_head_login.php";

/* =====================================
   HTML OUTPUT
===================================== */

echo "<div class='col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4'>";
echo "<div class='login-card'>";

echo "<img class='login-logo' src='images/sms1.png' alt='SMS System logo'>";

/* =====================================
   INFO / STATUS MESSAGES
===================================== */

$action = $_GET['action'] ?? "";

if($action == 'not_yet_logged_in'){
    echo "<div class='alert alert-info' style='text-align:center;' >Unesite korisničko ime i lozinku.</div>";
}
elseif($action == 'verification_sent'){
    echo "<div class='alert alert-success'>
        <strong>Verifikacioni email je uspešno poslat.</strong><br>
        Ako ga ne vidite, proverite i <b>Spam / Promotions</b> folder.
    </div>";
}
elseif($action == 'password_reset_success'){
    echo "<div class='alert alert-success'>
        Lozinka je uspešno promenjena. Možete se prijaviti.
    </div>";
}
elseif($action == 'please_login'){
    echo "<div class='alert alert-danger'><strong>Unesite korisničko ime i lozinku.</strong></div>";
}
elseif($action == 'izbac'){
    echo "<div class='alert alert-info'><strong>Vaša sesija je istekla, morate ponovo da se logujete.</strong></div>";
}
elseif($action == 'email_verified'){
    echo "<div class='alert alert-success'><strong>Email je uspešno verifikovan.</strong></div>";
}
elseif($action == 'account_ready'){
    echo "<div class='alert alert-success'><strong>Nalog je aktiviran.</strong> Sada se možete prijaviti sa svojom novom lozinkom.</div>";
}
elseif(empty($error_message)){
    echo "<div class='alert alert-info'>Unesite korisničko ime i lozinku.</div>";
}

/* =====================================
   ERROR MESSAGE
===================================== */

if(!empty($error_message)){
    echo "<div class='alert alert-danger'>$error_message</div>";
}

/* =====================================
   LOCK ICON
===================================== */

if($locked){
    echo "<div class='lock-icon-wrap'>
        <span class='glyphicon glyphicon-lock'></span>
    </div>";
}

/* =====================================
   LOGIN FORM
===================================== */

echo "<form class='form-signin' action='".htmlspecialchars($_SERVER["PHP_SELF"])."' method='post'>";

echo "<input type='hidden' name='csrf_token' value='".$_SESSION['csrf_token']."'>";

$val_email    = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8')
    : (isset($_GET['email']) ? htmlspecialchars(trim($_GET['email']), ENT_QUOTES, 'UTF-8') : '');
$val_password = isset($_POST['password']) ? htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8') : '';

$email_prefilled = empty($_POST) && $val_email !== '';

echo "<input type='text' name='email' class='form-control' placeholder='Email adresa' value='$val_email'" . ($email_prefilled ? "" : " autofocus") . " required>";

echo "
<div class='password-wrap'>
    <input type='password' id='password' name='password' class='form-control' placeholder='Lozinka' value='$val_password'" . ($email_prefilled ? " autofocus" : "") . " required>
    <button type='button' class='toggle-password' onclick='togglePassword()'>
        <i id='toggleIcon' class='glyphicon glyphicon-eye-open'></i>
    </button>
</div>
";

if(!$locked){
    echo "<button type='submit' class='btn-login'>Prijava</button>";
}

echo "</form>";

echo "<hr class='login-divider'>";
echo "<a class='forgot-link' href='forgot_password.php'>Zaboravili ste lozinku?</a>";

echo "</div>"; // .login-card
echo "</div>"; // .col-*

include_once "layout_foot.php";
?>

<script>

let countdown = document.getElementById("countdown");

if(countdown){
    let time = parseInt(countdown.innerText);
    let timer = setInterval(function(){
        time--;
        countdown.innerText = time;
        if(time <= 0){
            clearInterval(timer);
            location.reload();
        }
    }, 1000);
}

function togglePassword(){
    var password = document.getElementById("password");
    var icon = document.getElementById("toggleIcon");
    if(password.type === "password"){
        password.type = "text";
        icon.className = "glyphicon glyphicon-eye-close";
    }else{
        password.type = "password";
        icon.className = "glyphicon glyphicon-eye-open";
    }
}

</script>
