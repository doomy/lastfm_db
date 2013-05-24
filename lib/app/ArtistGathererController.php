<?php
class ArtistGathererController extends BasePackage {
// version 4
    public function __construct($env) {
        $this->env = $env;
        $this->include_packages(array('log', 'file'));
        $this->log = new Log('artist_gatherer', $this->env, array('stdout', 'filesystem'));
    }

    public function run() {
        $start_page = new File($this->env);
        $start_page->set_name($this->env->ENV_VARS["gatherer_start_page"]);
        $content = $start_page->get_contents();
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if(preg_match_all("/$regexp/siU", $content, $matches)) {
            foreach ($matches[2] as $match) {
                $music_pos = strpos($match, 'music/');
                if( $music_pos > 0 )
                    $this->log->log(substr($match, $music_pos+6));
            }
        }
    }
}
?>
