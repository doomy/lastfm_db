<?php
class BasePackage {
    // version 2
    public function __construct($env) {
        $this->env = $env;
    }
    
    public function include_packages($packages) {
        foreach ($packages as $package) {
            include_once($this->env->basedir . 'lib/' . $package . '.php');
        }
    }
}
?>
