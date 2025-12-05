<?php
    include_once("lib/service/CurlFetcher.php");
    include_once("lib/Env.php");
    include_once("lib/DbHandler.php");

    $curlFetcher = new CurlFetcher();

    $env = new Env('', $curlFetcher);
    $dbh = new DbHandler($env, $curlFetcher);

        // version 1
    set_time_limit(0);
    require('bootstrap.php');
    include_once("lib/base/controller.php");

    IF (isset($_GET['action']))
        $action = $_GET['action'];
    elseif (isset($argv))
        $action = $argv[1];
    else $action = 'random';

    switch ($action) {
        case 'rate':
            if ($dbh->run_db_call('ArtistGatherer', 'change_rating', $_GET['id'], $_GET['factor'])) {
                //echo $_GET['id'] . ' succesfully rated: ' . $_GET['factor'] . "<p></p>";
                header("Location: ?action=random");
            }
        break;
        case 'kickstart':
            if ($dbh->run_db_call('ArtistGatherer', 'kickstart', $_GET['id']))
                echo $_GET['id'] . ' succesfully kickstarted.';
            header("Location: ?action=random");
        break;
        
        case 'random':
            include_once(__DIR__ . "/lib/service/ApiClient.php");
            $apiClient = new ApiClient();
            random($env, $dbh, $curlFetcher, $apiClient);
        break;
        
        case 'gather':
            include_once("lib/app/ArtistGathererController.php");
            include_once("lib/service/ApiClient.php");
            $artist_gatherer_controller = new ArtistGathererController($env, $curlFetcher, new ApiClient(), $dbh);
            $artist_gatherer_controller->run();
        break;
    }
    
    function random($env, DbHandler $dbHandler, CurlFetcher $curlFetcher, ApiClient $apiClient) {
        include_once("lib/app/RandomArtistController.php");
        $random_artist_controller = new RandomArtistController($env, $dbHandler, $curlFetcher, $apiClient);
        $random_artist_controller->run();
    }
