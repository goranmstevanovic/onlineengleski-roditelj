<?php
$id = $_SESSION['user_id'];
?>
<h5><u>Promena lozinke:</u></h5>

<div id="passwordMessage"></div>

<form id="changePasswordForm">

    <div class="row">

        <div class="col-md-4 mb-3">
            <label>Trenutna lozinka</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Nova lozinka</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="col-md-4 mb-3">
            <label>Ponovi lozinku</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="col-12 text-end">
            <button type="submit" class="btn btn-warning">
                Promeni lozinku
            </button>
        </div>

    </div>

</form>

<script>
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
