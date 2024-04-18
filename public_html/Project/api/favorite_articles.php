<?php


require(__DIR__ . "/../../../lib/functions.php");
session_start();

//IF NOT IN TABLE, ADD IT
if(isset($_GET["article_id"]) && is_logged_in()){
    $db = getDB();
    $query = "INSERT INTO `UserArticles` (user_id, article_id) VALUES (:user_id, :article_id)";
    try{
    $stmt = $db->prepare($query);
    $stmt->execute([":user_id"=>get_user_id(), ":article_id"=>$_GET["article_id"]]);
    flash("You Successfully Favorited the Article", "success");
    }catch(PDOException $e) {
        flash("An Error Occured When Favorting", "danger");
        error_log("Error Favorting Article: " . var_export($e, true));
    }
//IF ALREADY IN TABLE, TOGGLE IT
} else if(isset($_GET["toggle_article_id"])){
        $db = getDB();
        $stmt = $db->prepare("UPDATE `UserArticles` SET is_active = !is_active WHERE user_id = :user_id AND article_id = :toggle_article_id");
        try {
            $stmt->execute([":user_id"=>get_user_id(), ":toggle_article_id"=>$_GET["toggle_article_id"]]);
            flash("Retoggled Article Favorite", "success");
        } catch (PDOException $e) {
            flash("An Error Occured Toggling Favorite", "danger");
            error_log(var_export($e->errorInfo, true));
        }
}

redirect("home.php");

?>