<?php
require_once(__DIR__ . "/../lib/functions.php");
//Note: this is to resolve cookie issues with port numbers
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
$localWorks = false; //some people have issues with localhost for the cookie params
//if you're one of those people make this false

//this is an extra condition added to "resolve" the localhost issue for the session cookie
if (($localWorks && $domain == "localhost") || $domain != "localhost") {
    session_set_cookie_params([
        "lifetime" => 60 * 60,
        "path" => "$BASE_PATH",
        //"domain" => $_SERVER["HTTP_HOST"] || "localhost",
        "domain" => $domain,
        "secure" => true,
        "httponly" => true,
        "samesite" => "lax"
    ]);
}
session_start();
//include functions here so we can have it on every page that uses the nav bar
//that way we don't need to include so many other files on each page
//nav will pull in functions and functions will pull in db
require_once(__DIR__ . "/../lib/functions.php");

?>

<!--Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- css/JS -->
<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="<?php echo get_url('helpers.js'); ?>"></script>


<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
    <?php if (is_logged_in()) : ?>
        <a class="navbar-brand mb-0 h1 border border-white" href="<?php echo get_url('home.php'); ?>">StellarNews</a>
    <?php endif; ?>
    <?php if (!is_logged_in()) : ?>
        <a class="navbar-brand mb-0 h1 border border-white" href="<?php echo get_url('login.php'); ?>">StellarNews</a>
    <?php endif; ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">

                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('home.php'); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('profile.php'); ?>">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('favorites.php'); ?>">My Favorites</a></li>
                <?php endif; ?>

                <?php if (!is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('login.php'); ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link"href="<?php echo get_url('register.php')?>">Register</a></li>
                <?php endif; ?>

                <?php if (has_role("Admin")) : ?>
                <!--ROLES-->
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Manage Roles
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/create_role.php'); ?>">Create Role</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/list_roles.php'); ?>">List Roles</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/assign_roles.php'); ?>">Assign Roles</a></li>
                </ul>
                </li>
                <!--ARTICLES-->
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Manage Articles
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/fetch_articles.php'); ?>">Fetch Articles</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/create_articles.php'); ?>">Creates Articles</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/list_articles.php'); ?>">List Articles</a></li>
                </ul>
                </li>
                <!--ASSOCIATIONS-->
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Manage Favorites
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                <li><a class="dropdown-item" href="<?php echo get_url('admin/assign_favorites.php'); ?>">Assign Favorites</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/all_favorites.php'); ?>">All Associations</a></li>
                    <li><a class="dropdown-item" href="<?php echo get_url('admin/not_favorited.php'); ?>">Not Associated</a></li>
                </ul>
                </li>

                <?php endif; ?>

                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('logout.php')?>">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>