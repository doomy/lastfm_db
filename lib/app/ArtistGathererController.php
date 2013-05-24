<?php
class ArtistGathererController extends BasePackage {
// version 3
    public function __construct($env) {
        $this->env = $env;
    }

    public function run() {
        echo $this->env->ENV_VARS["gatherer_start_page"];
    }
}
?>
