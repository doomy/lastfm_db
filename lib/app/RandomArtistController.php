
<?php
class RandomArtistController extends BaseController {
// version 1

    public function run() {
        $this->include_packages(array('model/ArtistsFromPageModel'));
        $ArtistsFromPageModel = new ArtistsFromPageModel($this->env, $this->dbh);
        $random_artist = $ArtistsFromPageModel->random_artist();
        $url = 'http://last.fm/music/'.$random_artist->name;
        if($random_artist->rating < 10) $add_color = 'color: red;';
        else if($random_artist->rating > 10) $add_color = 'color: green;';
        else $add_color = "";
        $random_artist_appendix = $random_artist->note ? "({$random_artist->note}) <br />" : "";
        

        echo <<<EOT
<html>
<head>
    <title>Last.fm random artist generator</title>
</head>
<body>
        
<style>
body {
    background-color: black;
    color: #888;
    font-family: Consolas;
    font-size: 14px;
}

strong { color: #fff; }

h1 { margin-bottom: 0; }

.options a { text-decoration: none; }
</style>
<p>
<h1 style="{$add_color}">{$random_artist->name}</h1>
$random_artist_appendix
<a href="{$url}">{$url}</a> <br />
</p>
<div class="options" style="font-size:20px; font-weight: bold;">
    <a href="?action=rate&factor=plus&id={$random_artist->id}">[rate +]</a>
    <!--<?php if($random_artist->rating < 10) { ?>
        <a href="?action=kickstart&id={$random_artist->id}">[kickstart]</a>
    <?php } ?>-->
    <a href="?action=random">[next random]</a>
    <a href="?action=rate&factor=minus&id={$random_artist->id}">[rate -]</a>
</div>
</body>
</html>
EOT;
    }
}
?>
