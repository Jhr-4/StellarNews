<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
  <div class="card text-bg-dark mb-3 p-3">
    <h1 class="card-header">Dashboard Articles</h1>
    <div class="card-body">
      <h5>This is StellarNews a palce to easily access all space news in one place.</h5>
      <p>Here you can read and enjoy articles without any distracting ads or pop-ups.<br> Simply find a header that interests you and dive into the article! <br>For a quick summary read the TL;DR at the bottom!</p>
    </div>

  </div>
</div>

<?php
/*if(isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])){
 echo "Welcome, " . $_SESSION["user"]["email"]; 
}
else{
  echo "You're not logged in";
}*/

if (is_logged_in(true)) {
  //flash("Welcome, " . get_user_email());
  //flash("Welcome home, " . get_username());
  error_log("Session data: " . var_export($_SESSION, true));
}
?>




<?php

$form = [
  ["type" => "input", "id" => "title", "name" => "title", "label" => "Article Title", "placeholder" => "Title", "include_margin" => false],
  ["type" => "input", "id" => "summary", "name" => "summary", "label" => "Article Summary", "placeholder" => "Summary", "include_margin" => false],

  ["type" => "number", "id" => "MIN_api_id", "name" => "MIN_api_id", "label" => "MIN API ID", "placeholder" => "0", "include_margin" => false],
  ["type" => "number", "id" => "MAX_api_id", "name" => "MAX_api_id", "label" => "MAX API ID", "placeholder" => "0", "include_margin" => false],

  ["type" => "datetime-local", "id" => "MAX_timestamp", "name" => "MAX_timestamp", "label" => "Article Uploaded Before", "include_margin" => false],
  ["type" => "datetime-local", "id" => "MIN_timestamp", "name" => "MIN_timestamp", "label" => "Article Upload After", "include_margin" => false],

  ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["created" => "Date", "api_id" => "API ID"], "include_margin" => false],
  ["type" => "select", "class" => "test", "name" => "order", "label" => "Order", "options" => ["desc" => "Newest", "asc" => "Oldest"], "include_margin" => false],

  ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "placeholder" => "10", "include_margin" => false],
];
//LOADING  TABLE/ARTICLES

$query = "SELECT id, api_id, title, site_url, image_url, news_text, news_summary_long, is_active FROM  `ArticlesTable` WHERE 1=1";
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];

//RESET/CLEAR FILTERS BUTTON
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
  session_delete($session_key);
  unset($_GET["clear"]);
  die(header("Location: " . $session_key));
} else {
  $session_data = session_load($session_key);
}

//FILTERING/SORTING FROM _GET
if (count($_GET) == 0) { //if doenst exist
  if ($session_data) {
    $_GET = $session_data;
  }
}
if (count($_GET) > 0) { //if theres _GET  
  session_save($session_key, $_GET);
  $keys = array_keys($_GET);

  foreach ($form as $k => $v) {
    if (in_array($v["name"], $keys)) {
      $form[$k]["value"] = $_GET[$v["name"]];
    }
  }
  //title
  $title = se($_GET, "title", "", false);
  if (!empty($title)) {
    $query .= " AND title like :title";
    $params[":title"] = "%$title%";
  }

  //summary
  $summary = se($_GET, "summary", "", false);
  if (!empty($summary)) {
    $query .= " AND news_summary_long like :summary";
    $params[":summary"] = "%$summary%";
  }

  //api range
  $MAX_api = se($_GET, "MAX_api_id", "", false);
  if (!empty($MAX_api) && $MAX_api >= 0) {
    $query .= " AND api_id <= :MAX_api";
    $params[":MAX_api"] = $MAX_api;
  }
  $MIN_api = se($_GET, "MIN_api_id", "", false);
  if (!empty($MIN_api) && $MIN_api >= 0) {
    $query .= " AND api_id >= :MIN_api";
    $params["MIN_api"] = $MIN_api;
  }

  //date range
  $MAX_timestamp = se($_GET, "MAX_timestamp", "", false);
  if (!empty($MAX_timestamp) && $MAX_timestamp >= 0) {
    $query .= " AND created <= :MAX_timestamp";
    $params[":MAX_timestamp"] = $MAX_timestamp;
  }
  $MIN_timestamp = se($_GET, "MIN_timestamp", "", false);
  if (!empty($MIN_timestamp) && $MIN_timestamp >= 0) {
    $query .= " AND created <= :MIN_timestamp";
    $params[":MIN_timestamp"] = $MIN_timestamp;
  }

  //sort and order

  $sort = se($_GET, "sort", "created", false);
  if (!in_array($sort, ["api_id", "created"])) {
    $sort = "created";
  }
  $order = se($_GET, "order", "desc", false);
  if (!in_array($order, ["asc", "desc"])) {
    $order = "desc";
  }

  $query .= " ORDER BY $sort $order";

  //LIMIT
  try {
    $limit = (int)se($_GET, "limit", "10", false);
  } catch (PDOException $error) {
    $limit = 10;
  }
  if ($limit < 1 || $limit > 100) {
    $limit = 10;
  }
  $query .= " LIMIT $limit";
} else { //IF No Session data loaded && no filters -> user will see leatest articles first  
  $sort = "created";
  $order = "desc";
  $query .= " ORDER BY $sort $order";
}

$db = getDB();
$stmt = $db->prepare($query);
$results = [];
try {
  $stmt->execute($params);
  $r = $stmt->fetchAll();
  if ($r) {
    $results = $r;
  }
} catch (PDOException $error) {
  error_log("Error fetching stocks: " . var_export($error, true));
  flash("An error occured", "danger");
}
for ($i = 0; $i < count($results); $i++) { //adds data for null values (that were added by manual create)
  foreach ($results[$i] as $k => $v) {
    if ($v === null) {
      $results[$i][$k] = "N/A";
    }
    if ($k === 'is_active' && $v === 1) {
      $results[$i][$k] = "True";
    } else if ($k === 'is_active' && $v === 0) {
      $results[$i][$k] = "False";
    }
  }
}

?>

<div class="container-fluid">
  <!--SORTING-->
  <div class="card card-body border-0 bg-primary bg-opacity-10 mb-3">
    <form method="GET">
      <div class="row mb-3" style="align-items: baseline;">
        <?php foreach ($form as $k => $v) : ?>
          <div class="col">
            <?php render_input($v); ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php render_button(["text" => "Filter", "type" => "submit"]); ?>
      <a href="?clear" class="btn btn-secondary">Reset</a>
    </form>
  </div>


  <!--CARD ARTICLE DISPLAYING-->
  <div class="row row-cols-1 row-cols-lg-4 row-cols-sm-2 g-4 mx-auto">
    <?php foreach ($results as $article) : ?>
      <?php
        if($article['is_active'] === "False"){
          continue;//Will skip the card if it's not active.
        }
      ?>
      <div class="col d-flex">
        <div class="card text-bg-light border-dark flex-fill">
          <img class="card-img-top" style="height: 18em; object-fit: cover;" src="<?php se($article, "image_url", "Unknown", true) ?>" alt="Article image">
          <div class="card-body">

            <!--DISPLAY LINK/SITE-->
            <h6 class="card-subtitle mb-2 text-muted">From:
              <a href="<?php se($article, "site_url", ""); ?>" target="_blank">
                <?php
                //    /(https?:\/\/)?(www\.)?        [-a-zA-Z0-9@:%._\+~#=]{2,256}     \.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/ 
                //remove https://www.  &&    .top-domain/----
                if ($article['site_url']) {
                  if (preg_match('/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', $article['site_url'])) {
                    $article['site_url'] = preg_replace('/(https?:\/\/)?(www\.)?/', '', $article['site_url']);
                    $article['site_url'] = preg_replace('/\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', '', $article['site_url']);
                  }
                } else {
                  $article['site_url'] = "StellarNews";
                } //if theres no SITE URL it wasn't from a different website it's original
                ?>
                <?php se($article, "site_url", "Unknown"); ?>
              </a>
            </h6>


            <h5 class="card-title"><?php se($article, "title", "Unknown", true) ?></h5>
          </div>
          <div class="card-footer text-center">
            <a href="<?php se(get_url("view_articles.php/")); ?>?<?php se($article, "primary_key", "id"); ?>=<?php se($article, "id"); ?>" class="btn btn-success border-dark w-100">View More</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>




<?php
require(__DIR__ . "/../../partials/flash.php");

?>