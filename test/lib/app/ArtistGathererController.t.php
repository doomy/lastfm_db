<?php
$BASE_PATH = '../../../';

include_once ($BASE_PATH . 'lib/env.php');
$env = new Env($BASE_PATH);
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/app/ArtistGathererController.php');

class UnitTest_FinFetcherController extends UnitTestBase {
    public function test_construct() {
        return ($ArtistGathererController = new ArtistGathererController($this->env));
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_FinFetcherController($env));

?>
