<?php
class ArtistsFromPageModel extends BasePackage {

    const USERNAME_LIMIT = 1;
    private $domDocument;
    private $xPath;

    /**
     * @var int
     */
    private $insertedUsernamesCount = 0;


// version 2
    public function __construct($env, $dbh, private CurlFetcher $curlFetcher, private ApiClient $apiClient) {
        $this->env = $env;
        $this->db_handler = $dbh;
        $this->include_packages(array('file'));
        $this->domDocument = new DOMDocument();
        $this->xPath = new \DOMXPath($this->domDocument);
    }

    public function get_artist_names() {

        $artists = array();

        $usernamesLeftCount = $this->db_handler->run_db_call(
            "ArtistGatherer", "getUnprocessedUsernamesCount"
        );
        echo "\nUsers remaining to process: $usernamesLeftCount \n";
        $usernames = $this->db_handler->run_db_call(
            "ArtistGatherer", "fetchUnprocessedUsernames", self::USERNAME_LIMIT
        );
        $userNameCount = count($usernames);

        echo "\n" . $userNameCount . " usernames will be processed. \n";
        echo "1. Usernames to be processed: " . implode(", ", $usernames) . "\n";
        usleep(2000000);

        $cursor = 0;
        foreach ($usernames as $username) {
            $cursor++;
            echo "1.3 Processing user $cursor out of $userNameCount \n";
             $this->importPeers($username);
            echo "1.3 Importing artists: \n";
            $artists = array_merge($artists, $this->apiClient->getArtists($username));
            echo "1.3.1 " . count($artists) . " artists found for user $username. \n";
            echo "1.4 Setting username: $username as processed. \n";
            $this->db_handler->run_db_call(
                "ArtistGatherer", "markUsernameAsProcessed", $username
            );
        }

        return array_unique($artists);
    }
    
    private function get_artist_names_from_file($filename) {
        echo "filename: $filename \n";  
        $start_page = new File($filename, $this->curlFetcher);
        //$content = $start_page->get_contents();
        $content = file_get_contents($filename);
        var_dump($content);
        die;

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        $artists = array();
        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $artist_name) {
                $music_pos = strpos($artist_name, 'music/');
                if ( $music_pos > 0 ) {
                        $artist_name = substr($artist_name, $music_pos+6);
    
                    $qmark_pos = strpos($artist_name, '?');
                    if ( $qmark_pos > 1 )
                        $artist_name = substr($artist_name, 0, $qmark_pos);
                        $artist_name = str_replace('&amp;', '&', $artist_name);
                        $artist_name = str_replace('library/music/', '', $artist_name);
                        $artist_name = str_replace('+noredirect/', '', $artist_name);


                        if (strpos($artist_name, '&rangetype=')) continue;
                        if (strpos(strtolower($artist_name), '+feat.+')) continue;
                        if (strpos(strtolower($artist_name), '+ft.+')) continue;
                        //if (strpos(strtolower($artist_name), 'library/music/')) continue;
                        //if (strpos(strtolower($artist_name), '+noredirect/')) continue;
                        if (strpos(strtolower($artist_name), '/_/')) continue;

                    $artists[] = $artist_name;
                }
            }
        }
        return $artists;
    }

    private function get_user_names_from_file($filename) {
        echo "filename: $filename \n";
        $start_page = new File($this->env, $this->curlFetcher);
        $start_page->set_name($filename);
        $content = $start_page->get_contents();
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        $users = array();
        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $user_name) {
                $pos = strpos($user_name, 'user/');
                if ( $pos > 0 ) {
                    $user_name = substr($user_name, $pos+5);

                    $qmark_pos = strpos($user_name, '?');
                    if ( $qmark_pos > 1 )
                        $artist_name = substr($user_name, 0, $qmark_pos);
                    $user_name = str_replace('&amp;', '&', $user_name);
                    if (strpos($user_name, '&rangetype=')) continue;
                    if (strpos(strtolower($user_name), '+feat.+')) continue;
                    if (strpos(strtolower($user_name), '+ft.+')) continue;

                    $users[] = $user_name;
                }
            }
        }
        return $users;
    }
    
    public function artist_exists($artist) {
        return $this->db_handler->run_db_call("ArtistGatherer", "artist_exists", $artist);
    }
    
    public function insert_artist($artist) {
        $this->db_handler->run_db_call("ArtistGatherer", "insert_artist", $artist);
    }
    
    public function random_artist() {
    
        //do {
            $max_rating = $this->db_handler->run_db_call("ArtistGatherer", "max_rating");
            $minRating = rand(1, $max_rating);
            echo "minimum rating: $minRating <br/>";

            $random_artist = $this->db_handler->run_db_call("ArtistGatherer", "random_artist", $minRating);
            echo "artist rating: {$random_artist->rating} <br />";


            /*if ($this->env->CONFIG['random_details']) {
                echo "<p>Artist name: <strong>" . urldecode($random_artist->name). "</strong>";
                echo " <a href='?action=rate&factor=plus&id={$random_artist->id}'>[+]</a>";
                if($random_artist->rating <= 400) {
                    echo " <a href='?action=kickstart&id={$random_artist->id}'>[+++]</a>";
                }
                echo " <a href='?action=rate&factor=minus&id={$random_artist->id}'>[-]</a>";
                echo "<br />";
                echo "Artist rating: " .$random_artist->rating . "<br />";
                echo "Random roll: " . $random_roll . "</p>";
            }*/
        //}
        //while ($random_artist->rating < $random_roll);
        return $random_artist;
    }
    
    public function artist_count() {
        return $this->db_handler->run_db_call("ArtistGatherer", "artist_count");
    }

    public function get_group_max_memberspage($group) {
        $filename = "http://www.last.fm/group/$group/members";
        echo "filename: $filename \n";
        $start_page = new File($this->env, $this->curlFetcher);
        $start_page->set_name($filename);
        $content = $start_page->get_contents();
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        $users = array();
        $maxPage = 1;
        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $link) {
                $pos = strpos($link, 'memberspage');
                if ( $pos > 0 ) {
                    $url_parts = explode("=", $link);
                    $page = array_pop($url_parts);
                    if ($maxPage < $page ) $maxPage = $page;
                }
            }
        }
        return $maxPage;
    }

    public function getInsertedUsernamesCount(): int
    {
        return $this->insertedUsernamesCount;
    }

    private function getPeers($username) {
        echo "1.1.1 Reading $username's peers: \n";
        echo "1.1.1.1 Reading $username's followers. \n";
        $followers = $this->getPeersFromUserListPage("https://www.last.fm/user/$username/followers", 1, $username);

        echo "1.1.1.2 Reading $username's following. \n";
        $following = $this->getPeersFromUserListPage("https://www.last.fm/user/$username/following", 2, $username);

        $peers = array_unique(array_merge($followers, $following));
        echo "1.1.2 $username has ". count($peers) . " peers: ". implode(", ", $peers) . "\n";

        return $peers;
    }

    private function getPeersFromUserListPage($baseUrl, $step, $username): array
    {
        $usernames = [];

        $lastPage = $this->findLastPageNumber($baseUrl);
        echo "1.1.1.$step.1 This user list has $lastPage pages. \n";
        for ($page = 1; $page <= $lastPage; $page++) {
            echo "1.1.1.$step.1.$page Processing page $page. \n";
            $url = $baseUrl . "?page=$page";
            echo "1.1.1.$step.1.$page.1 Url: $url \n";
            $usernames = array_merge($usernames, $this->getUserNamesFromFile($url, $username));
        }

        return $usernames;
    }

    private function findLastPageNumber($baseUrl) {
        $lastPage = 0;

        $links = $this->getAllLinks($this->curlFetcher->readUrl($baseUrl));

        foreach ($links as $link) {
            $parts = explode("page=", $link);
            if (count($parts) <= 1) continue;

            $page = (int) $parts[1];
            if ($page > $lastPage) $lastPage = $page;
        }

        if (empty($lastPage)) $lastPage = 1;

        return $lastPage;
    }

    private function getAllLinks($content) {
        $links = [];
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $link) {
                $links[] = $link;
            }
        }

        return $links;
    }

    private function getUserNamesFromFile($url, $originalUser): ?array
    {
        $usernames = [];
        $links = $this->getAllLinks($this->curlFetcher->readUrl($url));

        foreach ($links as $link) {
            $parts = explode("user/", $link);
            if (count($parts) <= 1) continue;
            if (strpos($parts[1], $originalUser) === 0) continue;
            $usernames[] = $parts[1];

        }

        return $usernames;
    }

    private function importPeers($username) {
        echo "1.1 Processing username: $username \n";
        $peers = $this->getPeers($username);
        echo "1.2 Inserting new usernames: \n";
        $insertedCount = $this->db_handler->run_db_call(
            "ArtistGatherer", "insertUsernames", $peers
        );
        echo "1.2.1 Inserted $insertedCount new usernames. \n";
        $this->insertedUsernamesCount += $insertedCount;
    }
}

?>
