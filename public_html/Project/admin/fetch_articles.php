<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}
?>
<?php
if(isset($_POST["articleDays"])){
    //validation
    $days = se($_POST, "articleDays", null, false);
    $hasError = false;

    if ($days === null || $days === ""){
        flash("ALL Fields Required.", "danger");
        $hasError = true;
    }
    if (!($days > 0)){
        flash("Days value must be greater than 0.", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        $result = get('https://spacenews.p.rapidapi.com/datenews/'.$_POST["articleDays"], "SPACE_API_KEY", $data = ["days" => $_POST["articleDays"]], true, 'spacenews.p.rapidapi.com');

        error_log("API Response: " . var_export($result, true));
        if (se($result, "status", 400, false) == 200 && isset($result["response"]) && $result["response"]!=="") {
            $result = json_decode($result["response"], true);
        } else {
            $result = [];
        }

        if ($result !== []){ //Avoids db and data transformation.. if theres no result..
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
            foreach ($article as $k => $v) { //extra unsetting just incase they add more unwanted api data someday...
                if (!in_array($k, ["title", "site_url", "image_url", "news_text", "news_summary_long"])) {
                    unset($article[$k]);
                }
            }
            array_push($data, $article); //Makes $data have the changes made in the local var $article
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
            if($params ===[]){
                flash("No Data From API...", "warning");
            }
        }
        error_log("QUERY: " . $query);
        error_log("Params: " . var_export($params, true));
        } else {
            flash("An Error Occured. There were no results to fetch.", "danger");
        }
    }
}

?>
<div class="container-fluid"> 
    <h3>Fetch Articles</h3>
    <form method="POST" onsubmit="return validate(this)">
        <div>
            <?php render_input(["type"=>"number", "id"=>"articleDays", "name"=>"articleDays", "label"=>"Article Days", "placeholder"=>"1", "rules"=>["required"=>true, "min"=>1,]]);?>
            <?php render_button(["text"=>"Fetch Articles", "type"=>"submit"]);?>
        </div>
    </form>
</div>
<script>
function validate(form) {
    let days = form.articleDays.value;
    let isValid = true;
   
    //EXISTANCE of Everything
    if (days === "" || articleDays === null){
        flash("[Client] Days feild must be provided.", "danger");
        isValid = false;
    }
    //validation of days
    if (!(days > 0)){
        flash("[Client] Days must be greater than 0.", "danger");
        isValid = false;
    }    
    return isValid;
}

</script>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>