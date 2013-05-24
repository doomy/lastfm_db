<?php
class BasePackage {
    // version 1
    public function include_packages($packages) {
        foreach ($packages as $package) {
            include_once($this->env->basedir . 'lib/' . $package . '.php');
        }
    }
}
?>
