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
        let username = form.username.value;
        let password = form.password.value;
        let confirmPassword = form.confirm.value;

        let errorCounter = 0;

        //email
        if (email === ""){
            flash("[Client] An eamil must be provided.", "danger");
            errorCounter++;
        }
        if (email !== "" && !/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/.test(email)){
            flash("[Client] Invalid email address.", "danger");
            errorCounter++
        }
        //username
        if (username === ""){
            flash("[Client] An username must be provided.", "danger")
            errorCounter++
        }
        if (username !== "" && !/^[a-z0-9_-]{3,16}$/.test(username)){
            flash("[Client] Username must only contain 3-16 alphanumeric characters, underscores, or dashes.", "danger");
            errorCounter++;
        }
        //password
        if (password === ""){
            flash("[Client] A password must be provided.", "danger");
            errorCounter++;
        }
        if (confirmPassword === ""){
            flash("[Client] A confirm password must be provided.", "danger");
            errorCounter++;
        }
        if ((password !== "" && confirmPassword !== "") && (password.length < 8 || confirmPassword.length<8)){
        //tells the user if the password is to short if they attempt to change using any feild
            flash("[Client] Password too short.", "danger");
            errorCounter++;
        }
        if ((password !== "" && confirmPassword !== "") && confirmPassword != password){
        //only occurs if both password filled; if one feild is empty the message to fill them is already displayed so this is unnecessary
            flash("[Client] Passwords must match.", "danger");
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
        flash("Invalid email address.", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 alphanumeric characters, underscores, or dashes.", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("A password must be provided.", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("A confirm password must be provided.", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short.", "danger");
        $hasError = true;
    }
    if (strlen($password) > 0 && $password !== $confirm) {
        flash("Passwords must match.", "danger");
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