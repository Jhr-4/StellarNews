<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

//FOR DELETING/TOGGLING
//handle the toggle first so select pulls fresh data
    if (isset($_GET["id"])) {
        $article_id = se($_GET, "id", -1, false);
        if (!empty($article_id)) {
            $db = getDB();
            $stmt = $db->prepare("UPDATE `ArticlesTable` SET is_active = !is_active WHERE id = :aid");
            try {
                $stmt->execute([":aid" => $article_id]);
                flash("Updated Role", "success");
            } catch (PDOException $e) {
                flash("An error occured", "danger");
                error_log(var_export($e->errorInfo, true));
            }
        }
    }


//LOADING  TABLE/ARTICLES
$db = getDB();
$query = "SELECT id, api_id, title, site_url, image_url, news_text, news_summary_long, is_active FROM  `ArticlesTable` ORDER BY created DESC LIMIT 25";
$stmt = $db->prepare($query);
$results = [];
try{
    $stmt -> execute();
    $r = $stmt->fetchAll();
    if($r){
        $results = $r;
    }
} catch(PDOException $error) {
    error_log("Error fetching stocks: " . var_export($error, true));
    flash("An error occured", "danger");

}
for($i=0; $i<count($results); $i++){ //adds data for null values (that were added by manual create)
    foreach($results[$i] as $k => $v){
        if ($v === null){
            $results[$i][$k] = "N/A"; 
        }
        if ($k ==='is_active' && $v===1){
            $results[$i][$k] = "True"; 
        } else if ($k ==='is_active' && $v===0){
            $results[$i][$k] = "False";
        }
    }
}


$table = ["data" => $results, "extra_classes" => "listTable", "ignored_columns" => ["id", "news_text", "news_summary_long"], 
            "edit_url"=>get_url("admin/edit_articles.php"),
            "delete_url"=>get_url("admin/list_articles.php"),
            "view_url"=>get_url("admin/view_articles.php")];
?>


<div class="container-fluid">
    <h3>List Articles</h3>
    <div class="tableDiv">
    <?php render_table($table);?>
    </div>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>