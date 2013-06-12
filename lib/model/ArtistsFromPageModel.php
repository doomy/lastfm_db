<?php
class ArtistsFromPageModel extends BasePackage {
// version 2
    public function __construct($env, $dbh) {
        $this->env = $env;
        $this->db_handler = $dbh;
        $this->include_packages(array('file'));
    }

    public function get_artist_names() {
        $start_page = new File($this->env);
        $start_page->set_name($this->env->ENV_VARS["gatherer_start_page"]);
        $content = $start_page->get_contents();
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
                        if (strpos($artist_name, '&rangetype=')) continue;
                        if (strpos(strtolower($artist_name), ' feat. ')) continue;

                    $artists[] = $artist_name;
                }
            }
        }
        return $artists;
    }
    
    public function artist_exists($artist) {
        return $this->db_handler->run_db_call("ArtistGatherer", "artist_exists", $artist);
    }
    
    public function insert_artist($artist) {
        $this->db_handler->run_db_call("ArtistGatherer", "insert_artist", $artist);
    }
    
    public function random_artist() {
        return $this->db_handler->run_db_call("ArtistGatherer", "random_artist");
    }
    
    public function artist_count() {
        return $this->db_handler->run_db_call("ArtistGatherer", "artist_count");
    }
}

?>
