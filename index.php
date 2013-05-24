<?php
    // version 1

    include_once("lib/env.php");
    include_once("lib/base/controller.php");
    
    $env = new Env("");
    
    switch (@$_GET['action']) {
        case 'random':
            include_once("lib/app/RandomArtistController.php");
            $random_artist_controller = new RandomArtistController($env);
            $random_artist_controller->run();
        break;
        
        default:
            include_once("lib/app/ArtistGathererController.php");
            $artist_gatherer_controller = new ArtistGathererController($env);
            $artist_gatherer_controller->run();
        break;
    }
?>
