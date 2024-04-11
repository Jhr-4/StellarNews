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
}
?>

<div class="container-fluid"> 
    <h3>Viewing Article</h3>

    <div class="card mx-auto w-75 mb-3 shadow p-3 mb-5 bg-body rounded">
        <div class="card-body">
            <!--TITLE-->
            <h3 class="card-title"><?php se($article, "title", "Unknown"); ?></h3>

            <!--DISPLAY LINK/SITE-->
            <h6 class="card-subtitle mb-2 text-muted">From: 
                <a href="<?php se($article, "site_url", "?id=$id"); ?>" target="_blank">
                    <?php
                    //    /(https?:\/\/)?(www\.)?        [-a-zA-Z0-9@:%._\+~#=]{2,256}     \.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/ 
                    //remove https://www.  &&    .top-domain/----
                    if ($article['site_url']){
                        if (preg_match('/(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', $article['site_url']) ) {
                            $article['site_url'] = preg_replace('/(https?:\/\/)?(www\.)?/', '', $article['site_url']);
                            $article['site_url'] = preg_replace('/\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)/', '', $article['site_url']);
                        }
                    }
                    ?>
                <?php se($article, "site_url", "Unknown"); ?>
                </a>
            </h6>

            <!--BUTTONS-->
            <div class="mb-1">
            <a class="btn btn-primary" href="<?php echo get_url('admin/list_articles.php'); ?>">Return</a>
            <?php if (has_role("Admin")) :?>
                <a class="btn btn-secondary" href="<?php echo get_url("admin/edit_articles.php?id=$id"); ?>">Edit</a>
                <a class="btn btn-danger" href="<?php echo get_url("admin/delete_articles.php?id=$id"); ?>">Toggle</a>
            <?php endif; ?>
            </div>

            <!--IMAGE-->
            <div class="text-center bg-secondary bg-opacity-50">
            <img src="<?php se($article, "image_url", "Unknown"); ?>" class="img-fluid rounded" alt="Image of Article">
            </div>

            <!--BODY-->
            <p class="card-text">
                    <small class="text-muted">Uploaded on <?php se($article, "created", "Unknown"); ?></small>
            </p>
            <div class="card-text">
            <p class="card-text"><h4>TLDR;</h4> <?php se($article, "news_summary_long", "Unknown"); ?></p>
            <p class="card-text"><h4>Main: </h4><pre style="white-space: pre-wrap; font: inherit;"><?php se($article, "news_text", "Unknown"); ?></pre>
            </div>
        </div>
    </div>
</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>