<?php
require(__DIR__ . "/../../partials/nav.php");
?>

<div class="container-fluid mb-3">
    <h1 class="card-header">Favorited Articles </h1>
</div>

<?php

$form = [
    ["type" => "input", "id" => "title", "name" => "title", "label" => "Article Title", "placeholder" => "Title", "include_margin" => false],
    ["type" => "input", "id" => "summary", "name" => "summary", "label" => "Article Summary", "placeholder" => "Summary", "include_margin" => false],

    ["type" => "datetime-local", "id" => "MAX_timestamp", "name" => "MAX_timestamp", "label" => "Article Uploaded Before", "include_margin" => false],
    ["type" => "datetime-local", "id" => "MIN_timestamp", "name" => "MIN_timestamp", "label" => "Article Upload After", "include_margin" => false],

    //["type" => "select", "name" => "sort", "label" => "Sort", "options" => ["created" => "Date"], "include_margin" => false],
    ["type" => "select", "class" => "test", "name" => "order", "label" => "Order", "options" => ["desc" => "Newest", "asc" => "Oldest"], "include_margin" => false],

    ["type" => "number", "name" => "limit", "label" => "Limit", "value" => "10", "placeholder" => "10", "include_margin" => false],
];
//LOADING  ARTICLES


$query = "SELECT ArticlesTable.id, title, site_url, image_url, news_text, news_summary_long, ArticlesTable.is_active, UserArticles.user_id, UserArticles.is_active AS userArticle_isActive FROM  `ArticlesTable` 
LEFT JOIN `UserArticles` on ArticlesTable.id = UserArticles.article_id WHERE UserArticles.user_id = :user_id AND UserArticles.is_active";
$params = [];
$params[":user_id"] = get_user_id();
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

    //sort and order SORT ISN"T THERE SO IT SHOULD JUST BE CREATED ALWAYS.
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
//var_dump($results);
?>

<div class="container-fluid">
    <?php if (empty($results)) { //For if query brings no articles (usally from sorting)
        flash("No Articles to Show", "warning");
    } ?>
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
    <div class="row row-cols-1 row-cols-lg-4 row-cols-sm-2 g-4 mx-auto my-3">
        <?php foreach ($results as $article) : ?>
            <?php
            if ($article['is_active'] === "False") {
                continue; //Will skip the card if it's not active.
            }
            ?>
            <div class="col d-flex">
                <div class="card text-bg-light border-dark flex-fill">
                    <img class="card-img-top" style="height: 18em; object-fit: cover;" src="<?php se($article, "image_url", "Unknown", true) ?>" alt="Article image">
                    <div class="card-body">

                        <h5 class="card-title"><?php se($article, "title", "Unknown", true) ?></h5>
                        <!--DISPLAY LINK/SITE-->
                        <h6 class="card-subtitle mb-2 text-muted">Credits:
                            <a class="text-decoration-none" href="<?php se($article, "site_url", ""); ?>" target="_blank">
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
                    </div>
                    <div class="card-footer text-center">

                        <!--CLICK TO FAVORITE (White Heart becomes Red); IF NOT EXIST IN TABLE, LINK = ?ARTICLE_ID-->
                        <?php if (($article["user_id"]) === "N/A") /*"N/A" b/c values being set to N/A if it's null earlier*/ : ?>
                            <a class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Favorite" href="<?php echo get_url('api/favorite_articles.php?article_id=' . $article["id"]); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-suit-heart-fill" viewBox="0 0 16 16">
                                    <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1" />
                                </svg>
                            </a>
                        <?php else : ?>
                            <!--Else IT EXSITS => TOGGLE, LINK = ?TOGGEL_ARTICLE_ID-->
                            <a class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Unfavorite" href="<?php echo get_url('api/favorite_articles.php?toggle_article_id=' . $article["id"]); ?>">
                                <!--1 = ACTIVE FAVORITE = CLICK TO UNFAVORITE (RED Heart Becomes White)-->
                                <?php if ($article["userArticle_isActive"] === 1) : ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-heart" viewBox="0 0 16 16">
                                        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                                    </svg>
                                <?php else : ?>
                                    <!--ELSE IT'S 0 = NOT ACTIVE FAVORITE = CLICK TO FAVORITE (White Heart Becomes Red)-->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-suit-heart-fill" viewBox="0 0 16 16">
                                        <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1" />
                                    </svg>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

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
        <?php endforeach; ?>
    </div>
</div>




<?php
require(__DIR__ . "/../../partials/flash.php");

?>