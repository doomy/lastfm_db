<?php
include 'package.php';

class BaseController extends BasePackage {
// version 1

    public function __construct($env)  {
        $this->env = $env;
        include($this->env->basedir . 'lib/db_handler.php');
        $this->dbh = new dbHandler($this->env);
    }
}

?>
