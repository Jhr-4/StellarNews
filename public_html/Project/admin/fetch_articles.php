<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}
?>
<?php
if(isset($_POST["articleDays"])){
    $result = get('https://spacenews.p.rapidapi.com/datenews/1', "SPACE_API_KEY", $data = ["days" => $_POST["articleDays"]], true, 'spacenews.p.rapidapi.com');

    error_log("API Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }

    //data transformation
    $data= [];
    foreach ($result as $article){
        $article["api_id"] = $article["id"];
        unset($article["id"]);
        $article["api_timestamp"] = $article["timestamp"];
        unset($article["timestamp"]);
        $temp["api_timestamp"] = str_replace("T", " ", $article["api_timestamp"]);
        $temp["api_timestamp"] = str_replace("Z", "", $temp["api_timestamp"]);
        $article["api_timestamp"] = $temp["api_timestamp"]; 
        unset($article["news_summary_short"]);
        unset($article["hashtags"]);
        array_push($data, $article);
    }
    $result = $data;

    //getting data ready to insert into DB
    $db = getDB();
    $query = "INSERT INTO `ArticlesTable` ";

    //setting up colomns
    $colomns = [];
    foreach ($result as $articles){
        foreach($articles as $k => $v){
            if(!in_array("`$k`", $colomns)){
                array_push($colomns, "`$k`");
            }
        }
    }
    $query .= "(" . join(",", $colomns) . ")";

    //setting up the rows
    $query .= " VALUES ";
    $params = [];
    $counter = 0;
    foreach ($result as $articles){
        $tempParams = [];
        $counter++;
        $query .= "(";
        foreach($articles as $k => $v){
            $tempParams[":$k".$counter] = $v;
            $params[":$k".$counter] = $v;
        }
        $query .= join(",", array_keys($tempParams)) .",";
        $query = rtrim($query,",");//removes the last comma before the ending prenthesis for that row
        $query .= "),";
    }
    $query = rtrim($query,",");//removes the last comma after all the rows are added
    $query .= " ON DUPLICATE KEY UPDATE `api_id` = `api_id`"; //replace old value with old value

    //actual insert into DB
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
<div class="container-fluid"> 
    <h3>Fetch Articles</h3>
    <form method="POST">
        <div>
            <?php render_input(["type"=>"number", "id"=>"articleDays", "name"=>"articleDays", "label"=>"Article Days", "placeholder"=>"1", "rules"=>["required"=>true]]);?>
            <?php render_button(["text"=>"Fetch Articles", "type"=>"submit"]);?>
        </div>
    </form>
</div>



<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>