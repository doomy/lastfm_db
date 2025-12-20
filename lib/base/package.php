<?php
class BasePackage {
    // version 2
    public function __construct(protected Env $env) {
    }
    
    public function include_packages($packages) {
        foreach ($packages as $package) {
            include_once($this->env->getBasedir() . 'lib/' . $package . '.php');
        }
    }
}
