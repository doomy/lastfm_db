<?php
// version 4

include_once ('../lib/env.php');
include_once("../lib/base/package.php");
$env = new Env('../');
include_once ($env->basedir . 'lib/dir.php');
$dir = new Dir($env);

include_once($env->basedir . 'lib/test/tester.php');
$tester = new Tester($env);
$tester->run_all_tests();

?>
