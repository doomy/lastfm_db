<?php
final class ArtistGathererController extends BasePackage {
// version 4
    public function __construct($env, CurlFetcher $curlFetcher, ApiClient $apiClient, private readonly DbHandler $dbh) {
        $this->env = $env;
        $this->include_packages(array('log', 'model/ArtistsFromPageModel'));
        $this->ArtistsFromPageModel = new ArtistsFromPageModel($this->env, $this->dbh, $curlFetcher, $apiClient);
        $this->log = new Log('artist_gatherer', $this->env, array('stdout', 'filesystem'), $curlFetcher);
    }

    public function run() {
        $count = 0;
        $artistNames = $this->ArtistsFromPageModel->get_artist_names();
        $this->log->log("Artists count found in total: " . count($artistNames));
        foreach ($artistNames as $artist_name)
        {

            if (!$this->ArtistsFromPageModel->artist_exists($artist_name)) {

                $this->log->log("Inserting $artist_name into the DB...");
                $this->ArtistsFromPageModel->insert_artist($artist_name);
                $count++;
            }
            else {
                // $this->log->log("$artist_name already exists");
            }
        }
        $this->log->log("Inserted $count new artists.");
        $this->log->log(
            sprintf("Inserted %d new usernames.", $this->ArtistsFromPageModel->getInsertedUsernamesCount())
        );
        $this->log->log("Currently there is an amount of " . $this->ArtistsFromPageModel->artist_count() . " artist records in the database.");
   }
}
?>
