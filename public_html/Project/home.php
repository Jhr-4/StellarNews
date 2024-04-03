<?php
require(__DIR__."/../../partials/nav.php");
?>
<div class="container-fluid">
  <h1>Home</h1>
</div>
<?php
/*if(isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])){
 echo "Welcome, " . $_SESSION["user"]["email"]; 
}
else{
  echo "You're not logged in";
}*/

if(is_logged_in(true)){
  //flash("Welcome, " . get_user_email());
  //flash("Welcome home, " . get_username());
  error_log("Session data: " . var_export($_SESSION, true));
}
?>
<?php
require(__DIR__."/../../partials/flash.php");

?>