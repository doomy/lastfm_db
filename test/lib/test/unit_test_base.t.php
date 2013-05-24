<?php

include_once ('../../../lib/env.php');
$env = new Env('../../../');
include_once ($env->basedir . 'lib/test/unit_test_base.php');

class TestUnitTestRunner extends UnitTestRunner {
    function _test($test_object, $method) {
        $this->called_tests[] = $method;
    }
}

class MockTestClass {
    public function test_first_test() {
        return true;
    }
    
    public function test_second_test() {
        return false;
    }
    
    public function not_a_test() {
        return true;
    }
}


class UnitTest_UnitTestRunner extends UnitTestBase {

    public function test_run_tests() {
        $mock_test_object = new MockTestClass;
        $test_unit_test_base = new TestUnitTestRunner;
        $test_unit_test_base->run_tests($mock_test_object);
        $expected_test_names = array('test_first_test', 'test_second_test');
        return ($expected_test_names == $test_unit_test_base->called_tests);
    }

    public function run_tests() {
        include_once($this->env->basedir.'/lib/test/unit_test_base.php');
        $unit_test_base = new UnitTestBase;
        $unit_test_base->run_tests($this);
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_UnitTestRunner($env));

?>
