 <link rel="shortcut icon" href="images/kalen.png">
<?php
$page_title = "Login";
include_once "config/core.php";
include_once "config/database.php";

$db = (new Database())->getConnection();

// token dolazi iz linka (GET, prvi dolazak) ili iz skrivenog polja forme
// (POST, kad roditelj potvrdi novu lozinku) - namerno se NE brise iz baze
// (verification_token) dok lozinka stvarno nije postavljena, da link ostane
// upotrebljiv i ako roditelj samo zatvori formu i vrati se kasnije
$token = $_POST['token'] ?? $_GET['token'] ?? '';

if(empty($token)){
    die("Nevalidan link.");
}

$stmt = $db->prepare("SELECT id, firstname FROM roditelji WHERE verification_token = :token LIMIT 1");
$stmt->execute([':token' => $token]);

if($stmt->rowCount() !== 1){
    die("Link nije validan ili je već iskorišćen.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);
$error = "";

/* =====================================
   SUBMIT - postavljanje prve lozinke
===================================== */

if($_POST){

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if($password !== $confirm){
        $error = "Lozinke se ne poklapaju.";
    }
    elseif(strlen($password) < 8){
        $error = "Lozinka mora imati minimum 8 karaktera.";
    }
    else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $db->prepare("
            UPDATE roditelji
            SET password = :password,
                email_verified_at = NOW(),
                verification_token = NULL
            WHERE id = :id
        ")->execute([
            ':password' => $hash,
            ':id' => $user['id'],
        ]);

        header("Location: login.php?action=account_ready");
        exit;
    }
}

include_once "layout_head_login.php";

echo "<div class='col-sm-6 col-md-4 col-md-offset-4'>";

echo "<div class='alert alert-success'>Email adresa je potvrđena, {$user['firstname']}! Postavite lozinku da biste dovršili aktivaciju naloga.</div>";

if(!empty($error)){
    echo "<div class='alert alert-danger'>$error</div>";
}

echo "<div class='account-wall'>";
echo "<img style='width:80%; margin-left:10%;' src='images/sms1.png'>";
echo "<form method='POST' class='form-signin'>";

echo "<input type='hidden' name='token' value='" . htmlspecialchars($token, ENT_QUOTES) . "'>";
echo "<input type='password' name='password' class='form-control' placeholder='Nova lozinka' required>";
echo "<input type='password' name='confirm_password' class='form-control' placeholder='Ponovi lozinku' required>";

echo "<input type='submit' class='btn btn-lg btn-success btn-block' value='Postavi lozinku'>";

echo "</form>";

echo "</div>";
echo "</div>";

include_once "layout_foot.php";
?>
