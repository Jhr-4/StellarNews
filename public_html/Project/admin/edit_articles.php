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
//unsets values that shouldn't be changed
if(isset($_POST["title"])){
    foreach($_POST as $k => $v){
        if(!in_array($k, ["title", "site_url", "image_url", "news_text", "news_summary_long"])){
            unset($_POST[$k]);
        }
        $article = $_POST;
    }
    //VALIDATION
    $title = se($article, "title", null, false);
    $siteURL = se($article, "site_url", null, false);
    if ($siteURL === "" || $siteURL === null || empty($siteURL)) { //JUST INCASE
        $article["site_url"] = null;
    }
    $imageURL = se($article, "image_url", null, false);
    $newsTEXT = se($article, "news_text", null, false);
    $newsSUMMARY = se($article, "news_summary_long", null, false);
    $hasError = false;

    if (empty($title) || empty($imageURL) || empty($newsTEXT) || empty($newsSUMMARY)) {//checks for all inputs. siteURL is optional
        flash("ALL Fields Required.", "danger");
        $hasError = true;
    }
    if (strlen($title)>=100 || strlen($title)<=10) {
        flash("Title Must 10-100 Characters.", "danger");
        $hasError = true;
    }
    if (!empty($siteURL) && !preg_match('/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', $siteURL)) {
        flash("Invalid Source Link.", "danger");
        $hasError = true;
    }
    if (strlen($siteURL)>=2048) {
        flash("Source Link Must Be Shorter Than 2048 Characters.", "danger");
        $hasError = true;
    }
    if (!preg_match('/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', $imageURL)) {
        flash("Invalid Image Link.", "danger");
        $hasError = true;
    }
    if (strlen($imageURL)>=2048) {
        flash("Image Link Must Be Shorter Than 2048 Characters.", "danger");
        $hasError = true;
    }
    if (strlen($newsTEXT)<=500) {
        flash("News Body Must be 500 Characters or Greater.", "danger");
        $hasError = true;
    }
    if (strlen($newsSUMMARY)>=500 || strlen($newsSUMMARY)<=10) {
        flash("News Summary Must be 10-500 Characters.", "danger");
        $hasError = true;
    }

        
    if (!$hasError) {
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
            $errorInfo = $error->errorInfo;
            if ($errorInfo[1] === 1062) {
                preg_match("/ArticlesTable.(\w+)/", $errorInfo[2], $matches);
                if (isset($matches[1])) {
                flash("The chosen title is not available. Try again.", "warning");
                }
            } else {
                flash("An Error Occured", "danger");
            }
        }

        error_log("QUERY: " . $query);
        error_log("Params: " . var_export($params, true));
    }
}
?>

<?php
//getting the data
$article = [];
if ($id>-1){
    $db = getDB();
    $query = "SELECT api_id, api_timestamp, title, site_url, image_url, news_text, news_summary_long, created FROM `ArticlesTable` WHERE id=:id";
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
//displaying
if($article){
    $isAPI = true;
    if($article['api_timestamp'] === null){
        $isAPI = false;
        $article['api_timestamp'] = $article['created'];
    }

    $form = [
        ["type"=>"number", "id"=>"api_id", "name"=>"api_id", "label"=>"API ID", "placeholder"=>"Not API Made", "rules"=>["required"=>true, "disabled"=>true]],//never changed
        ["type"=>"textarea", "id"=>"title", "name"=>"title", "label"=>"Article Title", "placeholder"=>"Title", "rules"=>["required"=>true, "maxlength"=>"100", "minlength"=>"10"]],
        ["type"=>"textarea", "id"=>"site_url", "name"=>"site_url", "label"=>"Article Link", "placeholder"=>"[NOT REQUIRED] Article Link/Source", "rules"=>["maxlength"=>"2048"]],//not required
        ["type"=>"textarea", "id"=>"image_url", "name"=>"image_url", "label"=>"Article Image", "placeholder"=>"https://image.com", "rules"=>["required"=>true, "maxlength"=>"2048"]],
        ["type"=>"textarea", "id"=>"news_text", "name"=>"news_text", "label"=>"Main Article", "placeholder"=>"Description", "rules"=>["required"=>true, "minlength"=>"500"]],
        ["type"=>"textarea", "id"=>"news_summary_long", "name"=>"news_summary_long", "label"=>"Article Summary", "placeholder"=>"Description Summary", "rules"=>["required"=>true, "minlength"=>"10", "maxlength"=>"500"]],
        ["type"=>"input", "id"=>"api_timestamp", "name"=>"api_timestamp", "label"=>"Original Article Upload", "rules"=>["required"=>true, "disabled"=>true]]//never changed
        //type datetime-local causing problem with css so its type input . either way it shouldn't be edited so doesn't matter
    ];
    $keys = array_keys($article);
    foreach($form as $k => $v){
        if(in_array($v["name"], $keys)){
            $form[$k]["value"] = $article[$v["name"]];
        }
    }
} else {
    flash("Invalid id passed", "danger"); //invalid id because id is too big compared to results in the db that it formed no $article/result...
    die(header("Location:" . get_url("admin/list_articles.php")));
}

?>

<div class="container-fluid"> 
    <h3>Edit Articles</h3>
    <form onsubmit="return validate(this)" method="POST">
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

<script>
function validate(form) {
    let title = form.title.value;
    let siteURL = form.site_url.value;
    let imageURL = form.image_url.value;
    let newsTEXT = form.news_text.value;
    let newsSUMMARY = form.news_summary_long.value;

    let isValid = true;
   
    //EXISTANCE of Everything besides siteURL which isn't required
    if (title === "" || imageURL==="" || newsTEXT==="" || newsSUMMARY===""){
        flash("[Client] All feilds be provided.", "danger");
        isValid = false;
    }
    //TITLE
    if (title.length <= 10 || title.length >= 100){
            flash("[Client] Title Must Be 10-100 Characters.", "danger"); 
            isValid = false;
    }
    //siteURL
    if (siteURL !== "" && !/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/.test(siteURL)){
        flash("[Client] Invalid Source Link.", "danger");
        isValid = false;
    }
    if (siteURL.length >= 2048){
        flash("[Client] Source Link Must Be Shorter Than 2048 Characters.", "danger");
        isValid = false;
    }
    //imageURL
    if (imageURL !== "" && !/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/.test(imageURL)){
        flash("[Client] Invalid Image Link.", "danger");
        isValid = false;
    }
    if (imageURL.length >= 2048){
        flash("[Client] Image Link Must Be Shorter Than 2048 Characters.", "danger");
        isValid = false;
    }
    //newsText
    if(newsTEXT.length<=500){
        flash("[Client] News Body Must Be 500 Characters or Greater.", "danger");
        isValid = false;
    }
    //newsSIMMARY
    if(newsSUMMARY.length <= 10 || newsSUMMARY.length >= 500){
        flash("[Client] News Summary Must be 10-500 Characters.", "danger");
        isValid = false;
    }

    return isValid;
}
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>