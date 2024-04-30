<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("$BASE_PATH" . "/home.php");
}

?>

<?php

$form = [
    ["type" => "input", "id" => "title", "name" => "title", "label" => "Article Title", "placeholder" => "Title", "include_margin" => false],
    ["type" => "input", "id" => "summary", "name" => "summary", "label" => "Article Summary", "placeholder" => "Summary", "include_margin" => false],

    ["type" => "datetime-local", "id" => "MAX_timestamp", "name" => "MAX_timestamp", "label" => "Article Uploaded Before", "include_margin" => false],
    ["type" => "datetime-local", "id" => "MIN_timestamp", "name" => "MIN_timestamp", "label" => "Article Upload After", "include_margin" => false],

    ["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["created" => "Date", "api_id" => "API ID"], "include_margin" => false],
    ["type" => "select", "class" => "test", "name" => "order", "label" => "Order", "options" => ["desc" => "Newest", "asc" => "Oldest"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "placeholder" => "10", "include_margin" => false],
];
//LOADING  ARTICLES
$query = "SELECT DISTINCT title, ArticlesTable.id, site_url, image_url, ArticlesTable.is_active, UserArticles.is_active AS userArticle_isActive, ArticlesTable.created, ArticlesTable.api_id
FROM  `ArticlesTable` 
LEFT JOIN `UserArticles` on ArticlesTable.id = UserArticles.article_id AND UserArticles.is_active
WHERE UserArticles.is_active IS NULL";
//selects where article id is associated and is_active = 1 => when selecting is null it selects those with 0
$params = [];
$session_key = $_SERVER["SCRIPT_NAME"];

//RESET/CLEAR FILTERS BUTTON
$is_clear = isset($_GET["clear"]);
if ($is_clear) {
    session_delete($session_key);
    unset($_GET["clear"]);
    redirect($session_key);
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

    //date range
    $MAX_timestamp = se($_GET, "MAX_timestamp", "", false);
    if (!empty($MAX_timestamp) && $MAX_timestamp >= 0) {
        $query .= " AND ArticlesTable.created <= :MAX_timestamp";
        $params[":MAX_timestamp"] = $MAX_timestamp;
    }
    $MIN_timestamp = se($_GET, "MIN_timestamp", "", false);
    if (!empty($MIN_timestamp) && $MIN_timestamp >= 0) {
        $query .= " AND ArticlesTable.created <= :MIN_timestamp";
        $params[":MIN_timestamp"] = $MIN_timestamp;
    }

    //sort and order 
    $sort = se($_GET, "sort", "created", false);
    if (!in_array($sort, ["api_id", "created"])) {
        $sort = "created";
    }
    if ($sort === "created" || $sort === "api_id") {
        $sort = "ArticlesTable." . $sort;
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
    $sort = "ArticlesTable.created";
    $order = "desc";
    $query .= " ORDER BY $sort $order";
    $limit = 16; 
    $query .= " LIMIT $limit";
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
    error_log("Error fetching articles: " . var_export($error, true));
    flash("An error occured", "danger");
}

$article_titles = []; //to find duplicate articles that are of multiple users
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
foreach ($results as $k => $v) {
    if (!isset($v['title'])) {
        unset($results[$k]);

    }
}

//COUNTING TOTAL FAVORITED
$totalFavorited = get_total_count("`ArticlesTable` 
                                    LEFT JOIN `UserArticles` on ArticlesTable.id = UserArticles.article_id AND UserArticles.is_active
                                    WHERE UserArticles.is_active IS NULL");
$totalShown = count($results); //counts total shown

?>
<div class="container-fluid">
    <?php if (empty($results)) { //For if query brings no articles (usally from sorting)
        flash("No Articles to Show", "warning");
    } ?>

    <div class="container-fluid mb-3">
        <h1 class="col-lg-auto col-md-auto col-sm-auto">Not Favorited Articles </h1>
    </div>

    <!--SORTING-->
    <div class="card card-body border-0 bg-primary bg-opacity-10">
        <form method="GET">
            <div class="row mb-3" style="align-items: baseline;">
                <?php foreach ($form as $k => $v) : ?>
                    <div class="col">
                        <?php render_input($v); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row justify-content-between">
                <!--SHOWING BUTTONS-->
                <div class="col-lg-auto col-md-auto col-sm-auto col-auto mb-0">
                    <?php render_button(["text" => "Filter", "type" => "submit"]); ?>
                    <a href="?clear" class="btn btn-secondary">Reset</a>
                </div>
                <!--SHOWING INFORMATION-->
                <header class="mx-3 col-xl-auto col-lg-auto col-md-auto col-sm-auto col-auto text-center  p-2">
                    <h5 class="mb-0">Results: <?php render_result_counts($totalShown, $totalFavorited); ?></h5>
                </header>
            </div>
        </form>
    </div>
</div>
<?php if (empty($results)) : ?>
                                <p class="text-center mt-3">No Articles Found.</p>
                            <?php endif; ?>
<!--CARD ARTICLE DISPLAYING-->
<div class="row row-cols-1 row-cols-lg-4 row-cols-sm-2 g-4 mx-auto">
    <?php foreach ($results as $article) : ?>
        <?php
        ?>
        <div class="col d-flex">
            <div class="card bg-dark text-white border-white flex-fill mt-3">
                <img class="card-img" style="height: 18em; object-fit: cover;" src="<?php se($article, "image_url", "Unknown", true) ?>" alt="Article image">
                <div class="card-img-overlay bg-dark opacity-75 ">
                </div>
                <div class="card-img-overlay d-flex flex-column">
                    <!--DISPLAY TITLE-->
                    <h5 class="card-title">
                        <?php if ($article['is_active'] === "False") : ?>
                            <span class="text-danger">[Disabled] </span><?php se($article, "title", "Unknown", true); ?>
                        <?php else : ?>
                            <?php se($article, "title", "Unknown", true); ?>
                        <?php endif; ?>
                    </h5>
                    <!--DISPLAY LINK/SITE-->
                    <h6 class="card-subtitle text-light">Credits:
                        <a class="text-decoration-none text-info" href="<?php se($article, "site_url", ""); ?>" target="_blank">
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
                            } //if theres no SITE URL its custom made aka made by StellarNews
                            ?>
                            <?php se($article, "site_url", "Unknown"); ?>
                        </a>
                    </h6>
                    <div class="text-center mt-auto">
                        <!--SINGLE VIEW-->
                        <a href="<?php se(get_url("view_articles.php/")); ?>?<?php se($article, "primary_key", "id"); ?>=<?php se($article, "id"); ?>" class="btn btn-success border-light w-75 text-center">
                            Read Article
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16">
                                <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0M4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php
require(__DIR__ . "/../../../partials/flash.php");

?>