<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

$article_id = se($_GET, "id", -1, false);
if ($article_id < -1){
    flash("Invalid Article ID Passed to Delete", "danger");
    die(header("Location: " . get_url("admin/list_articles.php")));
}
//FOR DELETING/TOGGLING
if (isset($_GET["id"])) {
    if (!empty($article_id)) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE `ArticlesTable` SET is_active = !is_active WHERE id = :aid");
        try {
            $stmt->execute([":aid" => $article_id]);
            flash("Toggled Article", "success");
        } catch (PDOException $e) {
            flash("An error occured", "danger");
            error_log(var_export($e->errorInfo, true));
        }
    }
}
die(header("Location: " . get_url("admin/list_articles.php")));

?>
