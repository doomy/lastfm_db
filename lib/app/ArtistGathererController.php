<?php
class ArtistGathererController extends BasePackage {
// version 4
    public function __construct($env) {
        $this->env = $env;
        $this->include_packages(array('log', 'model/ArtistsFromPageModel', 'db_handler'));
        $this->dbh = new dbHandler($env);
        $this->ArtistsFromPageModel = new ArtistsFromPageModel($this->env, $this->dbh);
        $this->log = new Log('artist_gatherer', $this->env, array('stdout', 'filesystem'));
    }

    public function run() {
        $count = 0;
        foreach ($this->ArtistsFromPageModel->get_artist_names() as $artist_name)
        {

            if (!$this->ArtistsFromPageModel->artist_exists($artist_name)) {

                $this->log->log("Inserting $artist_name into the DB...");
                $this->ArtistsFromPageModel->insert_artist($artist_name);
                $count++;
            }
        }
        $this->log->log("Inserted $count new artists.");
        $this->log->log("Currently there is an amount of " . $this->ArtistsFromPageModel->artist_count() . " artist records in the database.");
   }
}
?>
