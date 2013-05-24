<?php
include_once ('../../lib/env.php');
$env = new Env('../../');
include_once($env->basedir . 'lib/test/unit_test_base.php');

class TestEnv extends Env {
    function _set_env_vars_from_env_files() {
        $this->mock_setting_env_vars = true;
    }
}

class UnitTest_Env extends UnitTestBase{
    private $mock_basedir = '../../';

    public function __construct() {
        $this->env = new Env($this->mock_basedir);
    }

    public function test_set_basepath() {
        return ($this->env->basedir == $this->mock_basedir);
    }
    
    public function test_env_vars_setting() {
        $test_env = new TestEnv($this->mock_basedir);
        return $test_env->mock_setting_env_vars;
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_Env);

?>
