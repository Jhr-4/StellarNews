<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    //die(header("Location: $BASE_PATH" . "/home.php"));
    redirect("$BASE_PATH" . "/home.php");
}

//attempt to apply associations
if (isset($_POST["users"]) && isset($_POST["articles"])) {
    $user_ids = $_POST["users"];
    $article_ids = $_POST["articles"];
    if (empty($user_ids) || empty($article_ids)) {
        flash("Users and articles should not be empty", "warning");
    } else {
        $db = getDB();
        $query = "INSERT INTO UserArticles (user_id, article_id, is_active) VALUES ";
        $params = [];
        $counterUSERS = 0;
        foreach ($user_ids as $userID) {
            $counterUSERS++;
            $counterARTICLES = 0;
            foreach ($article_ids as $articleID) {
                $counterARTICLES++;
                $query .= "(:userID$counterUSERS";
                $params[":userID$counterUSERS"] = $userID;
                $query .= ", :articleID$counterARTICLES";
                $params[":articleID$counterARTICLES"] = $articleID;
                $query .= ", 1),";
            }
        }
        $query = rtrim($query, ","); //removes the last extra comma
        $query .= " ON DUPLICATE KEY UPDATE is_active = !is_active";
        /*echo "<pre>";
        var_dump($query);
        var_dump($params);
        echo "</pre>";*/
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Updated Article Favorites", "success");
        } catch (PDOException $e) {
            error_log("Error Applying Association: " . var_export($e, true));
            flash("An Error Occured While Toggling Favorites", "danger");
        }
    }
} else if (isset($_POST["users"]) || isset($_POST["articles"])) { //if just one is set
    flash("BOTH Users and articles must be selected", "warning");
}

//get active articles
$active_articles = [];
$db = getDB();
$query = "SELECT DISTINCT ArticlesTable.id, title FROM  `ArticlesTable` 
LEFT JOIN `UserArticles` on ArticlesTable.id = UserArticles.article_id WHERE ArticlesTable.is_active = true";
$params = [];
$title = "";
if (isset($_POST["title"])) {
    //title
    $title = se($_POST, "title", "", false);
    if (!empty($title)) {
        $query .= " AND title like :title LIMIT 25";
        $params[":title"] = "%$title%";
    }
}
$stmt = $db->prepare($query);
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll();
    if ($r) {
        $active_articles = $r;
    }
} catch (PDOException $e) {
    error_log("Error Fetching Articles: " . var_export($e, true));
    flash("An Error Occured while Fetching Articles", "danger");
}

//search for user by username
$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $db = getDB();
        $query = "SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(' ', ArticlesTable.id, ' (' , IF(ua.is_active = 1,'active','inactive') ,')') from 
        UserArticles ua JOIN ArticlesTable on ua.article_id = ArticlesTable.id WHERE ua.user_id = Users.id";
        $params = [];

        if (isset($_POST["title"])) {
            //title
            $title = se($_POST, "title", "", false);
            if (!empty($title)) {
                $query .= " AND title like :title";
                $params[":title"] = "%$title%";
            }
        }
        $query .= " LIMIT 25) as articles from Users WHERE username like :username LIMIT 25";
        $params[":username"] = "%$username%";
        $stmt = $db->prepare($query);
        try {
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            error_log("Error Fetching Users: " . var_export($e, true));
            flash("An Error Occured while Fetching Users", "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}

?>
<div class="container-fluid">
    <h1>Assign Favorites</h1>


    <div class="card card-body border-0 bg-primary bg-opacity-10 mb-3">
        <form method="POST">
            <div class="row mb-3" style="align-items: baseline;">
                <div class="col-6">
                    <strong>FILTER USERS: </strong>
                    <?php render_input(["type" => "search", "name" => "username", "id" => "username", "label" => "Username", "placeholder" => "Username Search", "value" => $username, "include_margin" => false]);/*lazy value to check if form submitted, not ideal*/ ?>
                </div>
                <div class="col-6">
                    <strong>FILTER ARTICLES: </strong>
                    <?php render_input(["type" => "search", "id" => "title", "name" => "title", "label" => "Article Title", "placeholder" => "Title", "value" => $title, "include_margin" => false]); ?>
                </div>
            </div>
            <!--SHOWING BUTTONS-->
            <div class="row justify-content-between">
                <div class="col-lg-auto col-md-auto col-sm-auto col-auto mb-0">
                    <?php render_button(["text" => "Filter", "type" => "submit"]); ?>
                    <a href="?" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <form method="POST">
        <?php if (isset($username) && !empty($username)) : ?>
            <input type="hidden" name="username" value="<?php se($username, false); ?>" />
        <?php endif; ?>
        <?php if (isset($title) && !empty($title)) : ?>
            <input type="hidden" name="title" value="<?php se($title, false); ?>" />
        <?php endif; ?>
        <button type="submit" class="btn btn-danger mb-3">Toggle Favorites</button>

        <table class="table">
            <thead>
                <th style="width:50dvw;">Users [Article ID]</th>
                <th>Articles to Assign [(ID) Title]</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php if (isset($_POST["username"]) && empty($users)) : ?>
                                <p class="text-center mt-3">No Users Found.</p>
                            <?php elseif (!isset($_POST["username"]) && empty($users)) : ?>
                                <p class="text-center mt-3">No Filter on Users Set Yet.</p>
                            <?php endif; ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td>
                                        <label for="user_<?php se($user, 'id'); ?>"><?php se($user, "username"); ?>
                                            <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" />
                                        </label>
                                    </td>
                                    <td><?php se($user, "articles", "No Articles"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php $counter = 0; ?>
                        <?php if (empty($active_articles)) : ?>
                            <p class="text-center mt-3">No Active Articles Found.</p>
                        <?php endif; ?>
                        <?php foreach ($active_articles as $article) : ?>
                            <?php $counter++; ?>
                            <div class="<?php if ($counter % 2 === 0) {
                                            se("bg-secondary bg-opacity-25");
                                        } else {
                                            se("bg-secondary bg-opacity-50");
                                        } ?>">
                                <label for="article_<?php se($article, 'id'); ?>">(<?php se($article, 'id'); ?>) <?php se($article, "title"); ?></label>
                                <input id="article_<?php se($article, 'id'); ?>" type="checkbox" name="articles[]" value="<?php se($article, 'id'); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>