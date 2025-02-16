<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
    <form onsubmit="return validate(this)" method="POST">
        <?php render_input(["type" => "text", "id" => "email", "name" => "email", "label" => "Email/Username", "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
        <?php render_button(["type" => "submit", "text" => "Login"]); ?>
    </form>
</div>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        let password = form.password.value;
        let email_user = form.email.value;

        let isValid = true;

        //email or username
        if (email_user === ""){ //if empty it must be provided
                flash("[Client] Email/username must be provided.", "danger");
                isValid = false;
        } else if (/@/.test(email_user)){ //not empty from earlier & if has @ -> check email regex conditions
            if ( !/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/.test(email_user)){
                flash("[Client] Invalid Email", "danger");
                isValid = false;
            }
        } else if (!/^[a-z0-9_-]{3,16}$/.test(email_user)){ //not empty from earlier & no @email -> check username regex conditions
                flash("[Client] Invalid Username", "danger"); //user should already know format...
                isValid = false;
        } 

        //password
        if (password === ""){
            flash("[Client] A password must be provided.", "danger");
            isValid = false;
        }
        if (password !=="" && password.length < 8){
            flash("[Client] Password too short.", "danger");//will potentially be changed to just "Invalid Password." User should know correct length.
            isValid = false;
        }

        return isValid;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
    //sanitize
    //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $email = sanitize_email($email);
    
    //validate
    /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash("Invalid email address");
        $hasError = true;
    }*/
        if (!is_valid_email($email)) {
            flash("Invalid email address");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username");
            $hasError = true;
        }
    }

    if (empty($password)) {
        flash("password must not be empty");
        $hasError = true;
    }
    if (strlen($password) < 8) {
        flash("Password too short");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        $_SESSION["user"] = $user;
                        try {
                            //lookup potential roles
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $user["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
                        //save roles or empty array
                        if (isset($roles)) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        flash("Welcome, ". get_username());
                        redirect("home.php");
                    } else {
                        flash("Invalid password");
                    }
                } else {
                    flash("Email not found");
                }
            }
        } catch (Exception $e) {
            //flash("<pre>" . var_export($e, true) . "</pre>");
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>