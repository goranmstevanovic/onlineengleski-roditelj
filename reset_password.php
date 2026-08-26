 <link rel="shortcut icon" href="images/kalen.png">
<?php
$page_title = "Login";
include_once "config/core.php";
include_once "config/database.php";

$db = (new Database())->getConnection();

$token = $_GET['token'] ?? '';

if(empty($token)){
    die("Nevalidan token.");
}

// validacija tokena
$stmt = $db->prepare("
    SELECT id, password_reset_expires
    FROM roditelji
    WHERE password_reset_token = :token
    LIMIT 1
");
$stmt->execute([':token' => $token]);

if($stmt->rowCount() !== 1){
    die("Token ne postoji.");
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// expiration
if(strtotime($user['password_reset_expires']) < time()){
    die("Token je istekao.");
}

// SUBMIT
if($_POST){

    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if($password !== $confirm){
        $error = "Lozinke se ne poklapaju.";
    }
    elseif(strlen($password) < 8){
        $error = "Lozinka mora imati minimum 8 karaktera.";
    }
    else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $upd = $db->prepare("
            UPDATE roditelji
            SET password = :password,
                password_reset_token = NULL,
                password_reset_expires = NULL,
                email_verified_at = COALESCE(email_verified_at, NOW())
            WHERE id = :id
        ");

        $upd->execute([
            ':password' => $hash,
            ':id' => $user['id']
        ]);

        header("Location: login.php?action=password_reset_success");
        exit;
    }
}

include_once "layout_head_login.php";

echo "<div class='col-sm-6 col-md-4 col-md-offset-4'>";

if(!empty($error)){
    echo "<div class='alert alert-danger'>$error</div>";
}

echo "<div class='account-wall'>";
echo "<img style='width:80%; margin-left:10%;' src='images/sms1.png'>";
echo "<form method='POST' class='form-signin'>";

echo "<input type='password' name='password' class='form-control' placeholder='Nova lozinka' required>";
echo "<input type='password' name='confirm_password' class='form-control' placeholder='Ponovi lozinku' required>";

echo "<input type='submit' class='btn btn-lg btn-success btn-block' value='Promeni lozinku'>";

echo "</form>";

echo "</div>";
echo "</div>";

include_once "layout_foot.php";
?>
