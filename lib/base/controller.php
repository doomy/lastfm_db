<?php
include 'package.php';

class BaseController extends BasePackage {
// version 1

    public function __construct($env, protected readonly DbHandler $dbh)  {
        $this->env = $env;
    }
}
