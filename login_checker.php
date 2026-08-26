<?php
// login checker for 'roditelj' access level

// if the session value is empty, he is not yet logged in, redirect him to login page
if(empty($_SESSION['logged_in'])){
    header("Location: {$home_url}login?action=not_yet_logged_in");
    exit;
}

else if($_SESSION['role']!="roditelj" ){
    header("Location: {$home_url}login?action=not_admin");
    exit;
}

else{
    // no problem, stay on current page
}
?>
