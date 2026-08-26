
<?php
// login checker for 'roditelj' access level (samo za login.php stranicu)

if(isset($_SESSION['access_level']) && ($_SESSION['access_level']=="Admin" || $_SESSION['access_level']=="Sekretar")){
    header("Location: {$home_url}index?action=logged_in_as_admin");
    exit;
}
// if $require_login was set and value is 'true'
else if(isset($require_login) && $require_login==true){
    // if user not yet logged in, redirect to login page
    if(!isset($_SESSION['access_level'])){
        header("Location: {$home_url}login?action=please_login");
        exit;
    }
}

// if it was the 'login' or 'register' or 'sign up' page but the roditelj was already logged in
else if(isset($page_title) && ($page_title=="Login" || $page_title=="Sign Up")){
    if(isset($_SESSION['role']) && $_SESSION['role']=="roditelj"){
        header("Location: {$home_url}index?action=already_logged_in");
        exit;
    }
}

else{
    // no problem, stay on current page
}
?>
