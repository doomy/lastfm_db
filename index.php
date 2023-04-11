<?php

        // version 1
    set_time_limit(0);
    require('bootstrap.php');
    include_once("lib/base/controller.php");

    IF (isset($_GET['action']))
        $action = $_GET['action'];
    elseif (isset($argv))
        $action = $argv[1];
    else $action = 'random';
    
    
    $dbh = new DbHandler();
    $env = Environment::get_env();

    switch ($action) {
        case 'rate':
            if ($dbh->run_db_call('ArtistGatherer', 'change_rating', $_GET['id'], $_GET['factor']))
                echo $_GET['id'] . ' succesfully rated: ' . $_GET['factor'] . "<p></p>";
            header("Location: ?action=random");
        break;
        case 'kickstart':
            if ($dbh->run_db_call('ArtistGatherer', 'kickstart', $_GET['id']))
                echo $_GET['id'] . ' succesfully kickstarted.';
            header("Location: ?action=random");
        break;
        
        case 'random':
            random($env);
        break;
        
        case 'gather':
            include_once("lib/app/ArtistGathererController.php");
            $artist_gatherer_controller = new ArtistGathererController($env);
            $artist_gatherer_controller->run();
        break;
    }
    
    function random($env) {
        include_once("lib/app/RandomArtistController.php");
        $random_artist_controller = new RandomArtistController($env);
        $random_artist_controller->run();
    }
?>
