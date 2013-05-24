<?php
class RandomArtistController extends BaseController {
// version 1

    public function run() {
        $this->include_packages(array('model/ArtistsFromPageModel'));
        $ArtistsFromPageModel = new ArtistsFromPageModel($this->env, $this->dbh);
        $url = 'http://last.fm/music/'.$ArtistsFromPageModel->random_artist();

        echo <<<EOT
<iframe src="{$url}" width="100%" height="100%">
</iframe>
EOT;
    }
}

?>
