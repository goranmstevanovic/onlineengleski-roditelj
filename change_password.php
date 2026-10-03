<?php
include_once "config/core.php";
include_once "config/database.php";
include_once "config/autoload.php";

if(empty($_SESSION['logged_in']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'roditelj'){
    die("<div class='alert alert-danger'>Niste ulogovani.</div>");
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if(empty($current) || empty($new) || empty($confirm)){
    die("<div class='alert alert-danger'>Sva polja su obavezna.</div>");
}

if(strlen($new) < 8){
    die("<div class='alert alert-danger'>Lozinka mora imati minimum 8 karaktera.</div>");
}

if($new !== $confirm){
    die("<div class='alert alert-danger'>Lozinke se ne poklapaju.</div>");
}

$roditelj = new roditelj($db);
$stmt = $roditelj->read_one($user_id);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$row){
    die("<div class='alert alert-danger'>Korisnik ne postoji.</div>");
}

// hash u bazi moze imati razmak na kraju (kao i kod prijave)
$row['password'] = trim($row['password']);

if(!password_verify($current, $row['password'])){
    die("<div class='alert alert-danger'>Trenutna lozinka nije tačna.</div>");
}

if(password_verify($new, $row['password'])){
    die("<div class='alert alert-warning'>Nova lozinka mora biti drugačija.</div>");
}

if($roditelj->set_password($user_id, $new)){
    echo "<div class='alert alert-success'>Lozinka uspešno promenjena.</div>";
} else {
    echo "<div class='alert alert-danger'>Greška pri čuvanju.</div>";
}
