<?php
$id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();
$roditelj = new roditelj($db);

$stmt = $roditelj->read_one($id);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h5><u>Moj nalog:</u></h5><br/>";

if($_POST){
    $email = trim($_POST['email']);

    if(empty($email)){
        echo "<div class='alert alert-danger'>Email je obavezan.</div>";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<div class='alert alert-danger'>Email nije validan.</div>";
    } else {
        $check = $db->prepare("SELECT id FROM roditelji WHERE email = :email AND id != :id LIMIT 1");
        $check->bindParam(':email', $email);
        $check->bindParam(':id', $id);
        $check->execute();

        if($check->rowCount() > 0){
            echo "<div class='alert alert-danger'>Email već postoji.</div>";
        } else {
            $roditelj->firstname = $_POST['firstname'];
            $roditelj->lastname = $_POST['lastname'];
            $roditelj->contact_number = $_POST['contact_number'];
            $roditelj->email = $email;
            // fk_porodica i active se ne menjaju kroz samostalno uređivanje profila
            $roditelj->fk_porodica = $row['fk_porodica'];
            $roditelj->active = $row['active'];

            if($roditelj->update($id)){
                $email_changed = ($email !== $row['email']);

                $stmt = $roditelj->read_one($id);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                echo "<div class='alert alert-success'>Uspešno sačuvano.</div>";

                if($email_changed){
                    echo "<div class='alert alert-warning'>
                        Promenili ste email adresu - potrebno je da je ponovo verifikujete pre sledećeg logovanja.
                        <a href='resend_verification.php?email=" . urlencode($row['email']) . "' class='btn btn-warning btn-sm'>Pošalji verifikacioni email</a>
                    </div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Neuspešna promena podataka. Molimo Vas da pokušate ponovo.</div>";
            }
        }
    }
}
?>

<form method="post">

<div class="row">

    <!-- IME -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Ime</label>
        <input type="text" name="firstname" value="<?= htmlspecialchars($row['firstname']) ?>" class="form-control" required>
    </div>

    <!-- PREZIME -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Prezime</label>
        <input type="text" name="lastname" value="<?= htmlspecialchars($row['lastname']) ?>" class="form-control" required>
    </div>

    <!-- TELEFON -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Broj telefona</label>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($row['contact_number']) ?>" class="form-control" required>
    </div>

    <!-- EMAIL -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control" required>
    </div>

    <!-- BUTTON -->
    <div class="col-12 text-end">
        <button type="submit" class="btn btn-danger">
            <i class='bi bi-record'></i> Snimi izmene
        </button>
    </div>

</div>

</form>
