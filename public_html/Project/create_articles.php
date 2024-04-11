<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}


if(isset($_POST["createForm"])){
    foreach($_POST as $k => $v){
        if(!in_array($k, ["title", "image_url", "news_text", "news_summary_long"])){
            unset($_POST[$k]);
        }
        $article = $_POST;
    }

    //getting data ready to insert into DB
    $db = getDB();
    $query = "INSERT INTO `ArticlesTable` ";
    $colomns = [];
    $params = [];
    foreach($article as $k => $v){
        if(!in_array("`$k`", $colomns)){
            array_push($colomns, "`$k`");
        }
            $params[":$k"] = $v;
    }
    $query .= "(" . join(",", $colomns) . ")";
    $query .= "VALUES (" . join(",", array_keys($params)) . ")";

    //actual insert into DB will result in error becauseeeeeee sql table has not null on EVERYTHING!!!
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Sucessfully inserted data", "success");
    } catch (PDOException $error) {
        error_log("Something went wrong with the query" . var_export($error, true));
        flash("An Error Occured", "danger");
    }

    error_log("QUERY: " . $query);
    error_log("Params: " . var_export($params, true));
}


 




?>



<!--create manual articles-->
<div class="container-fluid"> 
    <h3>Create Articles</h3>
    <form method="POST">
        <div>
            <?php render_input(["type"=>"textarea", "id"=>"title", "name"=>"title", "label"=>"Article Title", "placeholder"=>"Title", "rules"=>["required"=>true]]);?>
            <?php render_input(["type"=>"textarea", "id"=>"image_url", "name"=>"image_url", "label"=>"Article Image", "placeholder"=>"https://image.com", "rules"=>["required"=>true]]);?>
            <?php render_input(["type"=>"textarea", "id"=>"news_text", "name"=>"news_text", "label"=>"Main Article", "placeholder"=>"Description", "rules"=>["required"=>true]]);?>
            <?php render_input(["type"=>"textarea", "id"=>"news_summary_long", "name"=>"news_summary_long", "label"=>"Article Summary", "placeholder"=>"Description Summary", "rules"=>["required"=>true]]);?>
            <?php render_input(["type"=>"hidden", "name"=>"createForm", "value"=>"createForm"]) ?>
            <?php render_button(["text"=>"Fetch Articles", "type"=>"submit"]);?>
        </div>
    </form>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../partials/flash.php");
?>