<?php
    // version 1

    include_once("lib/env.php");
    include_once("lib/base/package.php");
    include_once("lib/app/ArtistGathererController.php");
    $env = new Env("");
    
    $artist_gatherer_controller = new ArtistGathererController($env);
    $artist_gatherer_controller->run();
?>
