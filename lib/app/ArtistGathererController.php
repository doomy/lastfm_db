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
                if(strpos($match, 'music/') > 0)
                    $this->log->log($match);
                // $matches[2] = array of link addresses
                //$matches[3] = array of link text - including HTML code
            }
        }
    }
}
?>
