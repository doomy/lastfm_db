<?php
include 'package.php';

abstract class BaseController extends BasePackage {
// version 1

    public function __construct(protected Env $env, protected readonly DbHandler $dbh)  {
    }
}
