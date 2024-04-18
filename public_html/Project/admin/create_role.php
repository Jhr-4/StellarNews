<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}

if (isset($_POST["name"]) && isset($_POST["description"])) {
    $name = se($_POST, "name", "", false);
    $desc = se($_POST, "description", "", false);
    if (empty($name)) {
        flash("Name is required", "warning");
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Roles (name, description, is_active) VALUES(:name, :desc, 1)");
        try {
            $stmt->execute([":name" => $name, ":desc" => $desc]);
            flash("Successfully created role $name!", "success");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                flash("A role with this name already exists, please try another", "warning");
            } else {
                flash ("An unexpected error occured and has been logged", "danger");
                error_log((var_export($e->errorInfo, true)));
            }
        }
    }
}
?>

<div class="container-fluid">
    <h1>Create Role</h1>
    <form method="POST">
        <?php render_input(["type"=>"input", "id"=>"name", "name"=>"name", "label"=>"Name", "rules"=>["required"=>true]]);?>
        <?php render_input(["type"=>"input", "id"=>"d", "name"=>"description", "label"=>"Description", "rules"=>["required"=>true]]);?>
        <?php render_button(["text"=>"Create Role", "type"=>"submit"]);?>
    </form>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>