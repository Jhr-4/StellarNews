<?php 

function session_save($key, $value) {
    if(isset($_SESSION["user"])) {
        $_SESSION["user"][$key] = json_encode($value);
    }
}


function session_load($key) {
    if(isset($_SESSION["user"]) && isset($_SESSION["user"][$key])){
        try {
            $data = json_decode($_SESSION["user"][$key], true);
            if ($data) {
                return $data;
            }
        } catch (PDOException $error){
            error_log("An Error Occured with Loading Session Data: " . $_SESSION["user"][$key]);
            flash("An Error Occured with Loading Session Data", "danger");
        }
    }
    return null;
}


function session_delete($key) {
    if (isset($_SESSION["user"]) && isset($_SESSION["user"][$key])) {
        unset($_SESSION["user"][$key]);
    }
}