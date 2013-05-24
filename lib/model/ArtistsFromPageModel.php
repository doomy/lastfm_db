<?php
class ArtistsFromPageModel extends BasePackage {
// version 1
    public function __construct($env) {
        $this->env = $env;
        $this->include_packages(array('file'));
    }

    public function get_artist_names() {
        $start_page = new File($this->env);
        $start_page->set_name($this->env->ENV_VARS["gatherer_start_page"]);
        $content = $start_page->get_contents();
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        $artists = array();
        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $match) {
                $music_pos = strpos($match, 'music/');
                if( $music_pos > 0 ) {
                    $artists[] = substr($match, $music_pos+6);
                }
            }
        }
        return $artists;
    }
}

?>
