<?php
include 'package.php';

class BaseController extends BasePackage {
// version 1

    public function __construct($env)  {
        $this->env = $env;
        $this->dbh = Environment::get_dbh();
    }
}

?>
