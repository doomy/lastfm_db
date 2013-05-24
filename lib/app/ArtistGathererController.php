<?php
class ArtistGathererController extends BasePackage {
// version 3
    public function __construct($env) {
        $this->env = $env;
        $this->include_packages(array('log'));
        $this->log = new Log('artist_gatherer', $this->env, array('stdout', 'filesystem'));
    }

    public function run() {
        $this->log->log($this->env->ENV_VARS["gatherer_start_page"]);
    }
}
?>
