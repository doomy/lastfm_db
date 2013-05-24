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
        $this->log->log($start_page->get_contents());
    }
}
?>
