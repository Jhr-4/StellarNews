<?php
require(__DIR__ . "/../../lib/functions.php");
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email"  required/> <!--add back required-->
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" /> <!--add back required-->
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" /> <!--add back required-->
    </div>
    <input type="submit" value="Register" />
</form>
<p id="JSError"></p>

<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let email = form.email.value;
        let password = form.password.value;
        let confirmPassword = form.confirm.value;

        let displayError = document.getElementById("JSError");

        let errorCounter = 0;

        if (email == ""){
            displayError.insertAdjacentHTML("beforeend", "An eamil must be provided. <br>");
            errorCounter++;
        }
        if (password == ""){
            displayError.insertAdjacentHTML("beforeend", "An password must be provided. <br>");
            errorCounter++;
        }
        if (confirmPassword == ""){
            displayError.insertAdjacentHTML("beforeend", "An confirm password must be provided. <br>");
            errorCounter++;
        }
        if (password.length < 8){
            displayError.insertAdjacentHTML("beforeend", "Password must be at least 8 characters. <br>");
            errorCounter++;
        }
        if (password != "" && confirmPassword != password){
            displayError.insertAdjacentHTML("beforeend", "Passwords must match. <br>");
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
if(isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"])){
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST,"confirm", "", false);
 
    //TODO 3: validate/use
    $hasError = false;
    if(empty($email)){
        echo "Email must be provided <br>";
        $hasError = true;
    }

    //sanatize removes all illegal characters 
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    //validate
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "Please type a valid email <br>";
        $hasError = true;
    }
    
    if(empty($password)){
        echo "Password must be provided <br>";
        $hasError = true;
    }
    if (empty($confirm)) {
        echo "Confirm Password must be provided <br>";
        $hasError = true;
    }
    if(strlen($password) < 8){
        echo "Password must be at least 8 characters long <br>";
        $hasError = true;
    }
    if(strlen($password) > 0 && $password !== $confirm){
        echo "Password must be match <br>";
        $hasError = true;
    }
    if(!$hasError){
        //echo "Welcome, $email";


        //TODO 4
        

        $hash = password_hash($password, PASSWORD_BCRYPT);          //always different output even if same password
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password) VALUES(:email, :password)");
        //:password & :email is placeholder;  not $varaibles because user can put in drop table as password
        try {
            $stmt->execute([":email" => $email, ":password" => $hash]);
            echo "Successfully registered!";
        } catch (Exception $e) {
            echo "There was a problem registering";
            echo "<pre>" . var_export($e, true) . "</pre>";
        }
    }
}
?>
