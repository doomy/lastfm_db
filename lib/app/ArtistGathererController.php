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
        foreach ($this->ArtistsFromPageModel->get_artist_names() as $artist_name)
        {
            if ($this->ArtistsFromPageModel->artist_exists($artist_name)) {
                $this->log->log("$artist_name already exists in the DB.");
            }
            else {
                $this->log->log("$artist_name does not exist in the DB. Inserting...");
                $this->ArtistsFromPageModel->insert_artist($artist_name);
            }
        }
   }
}
?>
