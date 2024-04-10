<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}
?>

<?php
$id = se($_GET, "id", -1, false);

if(isset($_POST["title"])){
    foreach($_POST as $k => $v){
        if(!in_array($k, ["title", "site_url", "image_url", "news_text", "news_summary_long"])){
            unset($_POST[$k]);
        }
        $article = $_POST;
    }

    //getting data ready to insert into DB
    $db = getDB();
    $query = "UPDATE `ArticlesTable` SET ";
    $params = [];
    foreach($article as $k => $v){
        if($params){
            $query .= ",";
        }
        $query .= "$k=:$k";
        $params[":$k"] = $v;
    }
    $query .= " WHERE id = :id";
    $params[":id"] = $id;

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        flash("Updated data", "success");
    } catch (PDOException $error) {
        error_log("Error Updating: " . var_export($error, true));
        flash("An Error Occured", "danger");
    }

    error_log("QUERY: " . $query);
    error_log("Params: " . var_export($params, true));
}

?>

<?php
//getting the data
$article = [];
if ($id>-1){
    $db = getDB();
    $query = "SELECT api_id, api_timestamp, title, site_url, image_url, news_text, news_summary_long, created FROM  `ArticlesTable` WHERE id=:id";
    try{
        $stmt = $db->prepare($query);
        $stmt->execute([":id"=>$id]);
        $r = $stmt->fetch();
        if($r){
            $article= $r;
        }
    }catch(PDOException $error) {
        error_log("Error fetching record: " . var_export($error, true));
        flash ("Error fetching record", "danger");
    }
} else {
    flash("invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_articles.php ")));
}

if($article){
    $isAPI = true;
    if($article['api_timestamp'] === null){
        $isAPI = false;
        $article['api_timestamp'] = $article['created'];
    }
    $form = [
        ["type"=>"number", "id"=>"api_id", "name"=>"api_id", "label"=>"API ID", "placeholder"=>"Not API Made", "rules"=>["required"=>true, "disabled"=>true]],//never changed
        ["type"=>"textarea", "id"=>"title", "name"=>"title", "label"=>"Article Title", "placeholder"=>"Title", "rules"=>["required"=>true]],
        ["type"=>"textarea", "id"=>"site_url", "name"=>"site_url", "label"=>"Article Link", "placeholder"=>"Not API Made", "rules"=>["required"=>$isAPI, "disabled"=>!$isAPI]],//can be changed if api
        ["type"=>"textarea", "id"=>"image_url", "name"=>"image_url", "label"=>"Article Image", "placeholder"=>"https://image.com", "rules"=>["required"=>true]],
        ["type"=>"textarea", "id"=>"news_text", "name"=>"news_text", "label"=>"Main Article", "placeholder"=>"Description", "rules"=>["required"=>true]],
        ["type"=>"textarea", "id"=>"news_summary_long", "name"=>"news_summary_long", "label"=>"Article Summary", "placeholder"=>"Description Summary", "rules"=>["required"=>true]],
        ["type"=>"input", "id"=>"api_timestamp", "name"=>"api_timestamp", "label"=>"Original Article Upload", "rules"=>["required"=>true, "disabled"=>true]]//never changed
        //type datetime-local causing problem with css so its type input . either way it shouldn't be edited so doesn't matter
    ];
    $keys = array_keys($article);
    foreach($form as $k => $v){
        if(in_array($v["name"], $keys)){
            $form[$k]["value"] = $article[$v["name"]];
        }
    }
}

?>

<div class="container-fluid"> 
    <h3>Edit Articles</h3>
    <form method="POST">
        <?php foreach($form as $k => $v){
            render_input($v);
            }
        ?>
        <div>
            <?php render_button(["text"=>"Update Articles", "type"=>"submit", "color"=>"success"]);?>
            <a class="btn btn-primary" href="<?php echo get_url('admin/list_articles.php'); ?>">Return</a>
        </div>
    </form>
</div>



<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>