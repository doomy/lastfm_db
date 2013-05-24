<?php
$BASE_PATH = '../../../';

include_once ($BASE_PATH . 'lib/env.php');
$env = new Env($BASE_PATH);
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/base/package.php');

class UnitTest_BasePackage extends UnitTestBase {
     public function test_construct() {
         return ($basePackage = new BasePackage);
     }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_BasePackage($env));

?>
