<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required/>
    </div>
    <div>
        <label for="username">Username</label>
        <input id="username" type="text" name="username" required/>
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" minlength="8" required/>
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" minlength="8" required/>
    </div>
    <input type="submit" value="Register" />
</form>

<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let email = form.email.value;
        let password = form.password.value;
        let confirmPassword = form.confirm.value;

        let errorCounter = 0;

        if (email == ""){
            flash("An eamil must be provided.", "danger");
            errorCounter++;
        }
        if (password == ""){
            flash("An password must be provided.", "danger");
            errorCounter++;
        }
        if (confirmPassword == ""){
            flash("An confirm password must be provided.", "danger");
            errorCounter++;
        }
        if (password.length < 8){
            flash("Password too short.", "danger");
            errorCounter++;
        }
        if (password != "" && confirmPassword != password){
            flash("Passwords must match.", "danger");
            errorCounter++;
        }
        if (errorCounter === 0){
            return true;
        }else {
            return false;
        }
    }
</script>

<?php
//TODO 2: add PHP Code
if(isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])){
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false); 
    $confirm = se($_POST,"confirm", "", false);
    $username = se($_POST,"username", "", false);

    //TODO 3: validate/use
 $hasError = false;
    if (empty($email)) {
        flash("An eamil must be provided.", "danger");
        $hasError = true;}

    //sanatize removes all illegal characters 
    //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = sanitize_email($email);
    
    //validate
    /*if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "Please type a valid email <br>";
        $hasError = true;
    }*/
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must be provided", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must be provided", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if(!$hasError){
        //echo "Welcome, $email";


        //TODO 4
        

        $hash = password_hash($password, PASSWORD_BCRYPT);          //always different output even if same password
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        //:password & :email is placeholder;  not $varaibles because user can put in drop table as password
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (PDOException $e) {
            flash("There was a problem registering");
            users_check_duplicate($e->errorInfo);;
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>