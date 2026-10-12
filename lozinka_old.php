<?php require_once __DIR__ . '/config/require_login.php'; // samo prijavljeni korisnici ?>
<?php
$id = $_SESSION['user_id'];
?>
<h5><u>Promena lozinke:</u></h5>

<div id="passwordMessage"></div>

<form id="changePasswordForm">

    <div class="row">

        <div class="col-md-4 mb-3">
            <label>Trenutna lozinka</label>
            <div class="input-group">
                <input type="password" name="current_password" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-lozinka" tabindex="-1" title="Prikazi lozinku"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <label>Nova lozinka</label>
            <div class="input-group">
                <input type="password" name="new_password" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-lozinka" tabindex="-1" title="Prikazi lozinku"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <label>Ponovi lozinku</label>
            <div class="input-group">
                <input type="password" name="confirm_password" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-lozinka" tabindex="-1" title="Prikazi lozinku"><i class="bi bi-eye"></i></button>
            </div>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-warning">
                Promeni lozinku
            </button>
        </div>

    </div>

</form>

<script>
document.querySelectorAll(".toggle-lozinka").forEach(function(btn){
    btn.addEventListener("click", function(){
        const input = this.parentNode.querySelector("input");
        const icon = this.querySelector("i");
        const skriveno = input.type === "password";
        input.type = skriveno ? "text" : "password";
        icon.className = skriveno ? "bi bi-eye-slash" : "bi bi-eye";
    });
});

document.getElementById("changePasswordForm").addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData(this);

    fetch("change_password.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("passwordMessage").innerHTML = data;
    })
    .catch(err => {
        console.error(err);
    });
});
</script>
