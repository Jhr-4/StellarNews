<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
?>

<?php
$user_id = -1;
try {
    $user_id = (int)se($_GET, "id", -1, false);
} catch (Exception $e) {
}
if ($user_id < 1) {
    $user_id = get_user_id();
}
$is_self = $user_id == get_user_id();
$can_edit = isset($_GET["edit"]);

?>

<?php
if ($is_self && $can_edit && isset($_POST["save"])) {
    error_log("hey");
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
            flash("Profile Email/Username Saved", "success");
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
$user = [];
if ($user_id > 0) {
    $db = getDB();
    $query = "SELECT email, username, Users.created, 
    (SELECT GROUP_CONCAT(name) from 
    UserRoles JOIN Roles on UserRoles.role_id = Roles.id WHERE UserRoles.user_id = Users.id AND UserRoles.is_active = 1) as roles
    FROM Users
    WHERE Users.id= :user_id";

    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":user_id" => $user_id]);
        $r = $stmt->fetch();
        if ($r) {
            $user = $r;
        } else {
            flash("Couldn't find user profile", "danger");
        }
    } catch (PDOException $error) {
        error_log("Error fetching user: " . $error);
        flash("An error occured fetcing the user", "danger");
    }
}
?>
<div class="container-fluid">
    <?php if ($is_self && $can_edit) : ?>
        <form onsubmit="return validate(this)" method="POST">
            <h2>Email/Username</h2>
            <?php render_input(["type" => "email", "id" => "email", "name" => "email", "value"=>se($user, "email", "", false), "label" => "Email", "rules" => ["required" => true]]); ?>
            <?php render_input(["type" => "text", "id" => "username", "name" => "username", "value"=>se($user, "username", "", false),"label" => "Username", "rules" => ["required" => true, "maxlength" => 30]]); ?>
            <hr>
            <h2>Password</h2>
            <?php render_input(["type" => "password", "id" => "cp", "name" => "currentPassword", "label" => "Current Password", "rules" => ["minlength" => 8]]); ?>
            <?php render_input(["type" => "password", "id" => "np", "name" => "newPassword", "label" => "New Password", "rules" => ["minlength" => 8]]); ?>
            <?php render_input(["type" => "password", "id" => "comp", "name" => "confirmPassword", "label" => "Confirm Password", "rules" => ["minlength" => 8]]); ?>
            <?php render_input(["type" => "hidden", "name" => "save"]);/*lazy value to check if form submitted, not ideal*/ ?>
            <?php render_button(["text" => "Update Profile", "type" => "submit"]); ?>
            <a class="btn btn-secondary" href="?">View</a>
        </form>
    <?php else : ?>
        <?php if (isset($user) && !empty($user)) : ?>
            <div class="card mb-3 col-auto col-sm-5 col-lg-4 col-xl-3 mx-auto">
                <div class="card-header p-0">
                    <?php $colors = array("danger", "info", "warning", "secondary", "success", "primary", "dark"); ?>
                    <h5 class="mx-auto text-center text-bg-<?php se($colors[rand(0, 6)]) ?> rounded-circle m-3" style="height:10em; width:10em; padding-top:4.25em;"> <?php se($user, "username", "N/A"); ?> </h5>
                </div>
                <div class="card-body mx-auto text-center">
                    <h5 class="card-title">User: <?php se($user, "username", "Not Found"); ?></h5>
                    <?php if (isset($user["roles"])) : ?>
                        <p class="card-text">Roles: <?php se($user, "roles"); ?></p>
                    <?php endif; ?>
                    <p class="card-text">Joined: <?php se($user, "created", "Not Found"); ?></p>
                    <?php if ($is_self) : ?>
                        <a class="btn btn-primary" href="?edit">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else :?>
            <h5 class="card mx-auto p-5 text-center text-danger">Invalid ID Passed</h5>
        <?php endif;?>
    <?php endif ?>
</div>

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
        if (email === "") {
            flash("[Client] An eamil must be provided.", "danger");
            isValid = false;
        }
        if (email !== "" && !/^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6})*$/.test(email)) {
            flash("[Client] Invalid email address.", "danger");
            isValid = false;
        }

        //username
        if (username === "") {
            flash("[Client] An username must be provided.", "danger");
            isValid = false;
        }
        if (username !== "" && !/^[a-z0-9_-]{3,16}$/.test(username)) {
            flash("[Client] Username must only contain 3-16 alphanumeric characters, underscores, or dashes.", "danger");
            isValid = false;
        }

        //password
        if (newPassword !== "" || confirmPassword !== "" || currentPassword !== "") { //if any password has text, user trying to change pass
            if (currentPassword === "") {
                flash("[Client] Current password must be provided.", "danger");
                isValid = false;
            }
            if (currentPassword.length < 8) {
                flash("[Client] Current password is invalid.", "danger"); //current password should be atleast 8.
                isValid = false;
            }
            if (newPassword === "") {
                flash("[Client] A new password must be provided.", "danger");
                isValid = false;
            }
            if (confirmPassword === "") {
                flash("[Client] A confirm password must be provided.", "danger");
                isValid = false;
            }
            if ((newPassword !== "" && confirmPassword !== "") && newPassword !== confirmPassword) {
                //only occurs if both password filled; if one feild is empty the message to fill them is already displayed so this is unnecessary
                flash("[Client] Password and Confrim password must match", "danger");
                isValid = false;
            }
            if ((newPassword !== "" || confirmPassword != "") && (newPassword.length < 8 || confirmPassword.length < 8)) {
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