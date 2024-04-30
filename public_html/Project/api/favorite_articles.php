<?php


require(__DIR__ . "/../../../lib/functions.php");
session_start();
is_logged_in(true);


//(UN)FAVORITE INDIVIDUAL ARTICLES
if(isset($_GET["article_id"]) && is_logged_in()){ //IF NOT IN TABLE, ADD IT
    $db = getDB();
    $query = "INSERT INTO `UserArticles` (user_id, article_id) VALUES (:user_id, :article_id)
    ON DUPLICATE KEY UPDATE is_active = !is_active";
    try{
    $stmt = $db->prepare($query);
    $stmt->execute([":user_id"=>get_user_id(), ":article_id"=>$_GET["article_id"]]);
    flash("Successfully Updated Favorite for The Article", "success");
    }catch(PDOException $e) {
        flash("An Error Occured When Favorting", "danger");
        error_log("Error Favorting Article: " . var_export($e, true));
    }
}/* else if(isset($_GET["toggle_article_id"])){ //IF ALREADY IN TABLE, TOGGLE IT
        $db = getDB();
        $stmt = $db->prepare("UPDATE `UserArticles` SET is_active = !is_active WHERE user_id = :user_id AND article_id = :toggle_article_id");
        try {
            $stmt->execute([":user_id"=>get_user_id(), ":toggle_article_id"=>$_GET["toggle_article_id"]]);
            flash("Updated Article Favorite", "success");
        } catch (PDOException $e) {
            flash("An Error Occured Toggling Favorite", "danger");
            error_log(var_export($e->errorInfo, true));
        }
} */


//UNFAVORITE ALL BUTTON ACTION
if (isset($_GET["toggle_all"])){
    $db = getDB();
    $stmt = $db->prepare("UPDATE `UserArticles` SET is_active = !is_active WHERE user_id = :user_id AND is_active = 1");
    try {
        $stmt->execute([":user_id"=>get_user_id()]);
        flash("Unfavorited All Article", "success");
    } catch (PDOException $e) {
        flash("An Error Occured Toggling Favorite", "danger");
        error_log(var_export($e->errorInfo, true));
    }
    redirect("favorites.php");
}



redirect("favorites.php");

?>