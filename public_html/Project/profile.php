<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
?>
<?php
if (isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);
    $hasError = false;
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 alphanumeric characters, underscores, or dashes.", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
        $db = getDB();
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");
        try {
            $stmt->execute($params);
            flash("Profile saved", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
        //select fresh data from table
        $stmt = $db->prepare("SELECT id, email, username from Users where id = :id LIMIT 1");
        try {
            $stmt->execute([":id" => get_user_id()]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                //$_SESSION["user"] = $user;
                $_SESSION["user"]["email"] = $user["email"];
                $_SESSION["user"]["username"] = $user["username"];
            } else {
                flash("User doesn't exist", "danger");
            }
        } catch (Exception $e) {
            flash("An unexpected error occurred, please try again", "danger");
            //echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
        }
    }


    //check/update password
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        $hasError = false;
        if (!is_valid_password($new_password)) {
            flash("Password too short", "danger");
            $hasError = true;
        }
        if (!$hasError) {
            if ($new_password === $confirm_password) {
                //TODO validate current
                $stmt = $db->prepare("SELECT password from Users where id = :id");
                try {
                    $stmt->execute([":id" => get_user_id()]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (isset($result["password"])) {
                        if (password_verify($current_password, $result["password"])) {
                            $query = "UPDATE Users set password = :password where id = :id";
                            $stmt = $db->prepare($query);
                            $stmt->execute([
                                ":id" => get_user_id(),
                                ":password" => password_hash($new_password, PASSWORD_BCRYPT)
                            ]);

                            flash("Password reset", "success");
                        } else {
                            flash("Current password is invalid", "warning");
                        }
                    }
                } catch (PDOException $e) {
                    echo "<pre>" . var_export($e->errorInfo, true) . "</pre>";
                }
            } else {
                flash("New passwords don't match", "warning");
            }
        }
    }
}
?>

<?php
$email = get_user_email();
$username = get_username();
?>
<form method="POST" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php se($email); ?>" required/>
    </div>
    <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?php se($username); ?>" required/> 
    </div>
    <!-- DO NOT PRELOAD PASSWORD -->
    <div>Password Reset</div>
    <div class="mb-3">
        <label for="cp">Current Password</label>
        <input type="password" name="currentPassword" minlength="8" id="cp" />
    </div>
    <div class="mb-3">
        <label for="np">New Password</label>
        <input type="password" name="newPassword" minlength="8" id="np" />
    </div>
    <div class="mb-3">
        <label for="conp">Confirm Password</label>
        <input type="password" name="confirmPassword" minlength="8" id="conp" />
    </div>
    <input type="submit" value="Update Profile" name="save" />
</form>

<script>
    function validate(form) {
        let email = form.email.value;
        let username = form.username.value;
        let newPassword = form.newPassword.value;
        let confirmPassword = form.confirmPassword.value;
        let currentPassword = form.currentPassword.value;

        let isValid = true;
        //TODO add other client side validation....

        //example of using flash via javascript
        //find the flash container, create a new element, appendChild
       
        //email
        if (email === ""){
            flash("[Client] An eamil must be provided.", "danger");
            isValid = false;
        }
        if (email !== "" && !/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/.test(email)){
            flash("[Client] Invalid email address.", "danger");
            isValid = false;
        }

        //username
        if (username === ""){
            flash("[Client] An username must be provided.", "danger");
            isValid = false;
        }
        if (username !== "" && !/^[a-z0-9_-]{3,16}$/.test(username)){
            flash("[Client] Username must only contain 3-16 alphanumeric characters, underscores, or dashes.", "danger");
            isValid = false;
        }

        //password
        if (newPassword !== "" || confirmPassword !=="" || currentPassword !==""){ //if any password has text, user trying to change pass
            if (currentPassword === ""){
                flash("[Client] Current password must be provided.", "danger");
                isValid = false;
            }
            if (currentPassword.length < 8){
                flash("[Client] Current password is invalid.", "danger"); //current password should be atleast 8.
                isValid = false;
            }
            if (newPassword === ""){
                flash("[Client] A new password must be provided.", "danger");
                isValid = false;
            }
            if (confirmPassword === ""){
                flash("[Client] A confirm password must be provided.", "danger");
                isValid = false;
            }
            if ((newPassword !== "" && confirmPassword !== "") && newPassword !== confirmPassword) { 
                //only occurs if both password filled; if one feild is empty the message to fill them is already displayed so this is unnecessary
                flash("[Client] Password and Confrim password must match", "danger");
                isValid = false;
            }
            if ((newPassword !== "" || confirmPassword !="") && (newPassword.length < 8 || confirmPassword.length < 8)){
                //tells the user if the password is to short if they attempt to change using any feild
                flash("[Client] New password too short.", "danger");
                isValid = false;
            }
        }

        return isValid;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>