<?php

include_once ('../../../lib/env.php');
$env = new Env('../../../');
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/test/tester.php');

class UnitTest_Tester extends UnitTestBase {

    public function init() {
        $this->tester = new Tester($this->env);
    }

    public function test_run_testfile() {
        $this->dir->put_contents_into_file('tester.data.t.php', '<?php $GLOBALS[\'TEST_VARIABLE\'] = \'test_variable_content\'; ?>');
        $this->tester->run_testfile('lib/test/tester.data.t.php');
        $result = ($GLOBALS['TEST_VARIABLE'] == 'test_variable_content');
        unset($GLOBALS['TEST_VARIABLE']);
        $this->dir->delete_file('tester.data.t.php');
        return $result;
    }

    public function test_run_multiple_tests() {

        $this->dir->put_contents_into_file('tester2.data.t.php', '<?php $GLOBALS[\'TEST_VARIABLE2\'] = \'test_variable_content2\'; ?>');
        $this->dir->put_contents_into_file('tester3.data.t.php', '<?php $GLOBALS[\'TEST_VARIABLE3\'] = \'test_variable_content3\'; ?>');
        $this->tester->run_multiple_testfiles(array('lib/test/tester2.data.t.php', 'lib/test/tester3.data.t.php'));
        $result = (($GLOBALS['TEST_VARIABLE2'] == 'test_variable_content2') && ($GLOBALS['TEST_VARIABLE3'] == 'test_variable_content3'));
        $this->dir->delete_files(array('tester2.data.t.php', 'tester3.data.t.php'));
        unset($GLOBALS['TEST_VARIABLE2']);
        unset($GLOBALS['TEST_VARIABLE3']);

        return $result;
    }

    public function test_test_all() {
        $this->dir->put_contents_into_file('tester4.data.t.php', '<?php $GLOBALS[\'TEST_VARIABLE4\'] = \'test_variable_content4\'; ?>');
        $this->dir->put_contents_into_file('tester5.data.t.php', '<?php $GLOBALS[\'TEST_VARIABLE5\'] = \'test_variable_content5\'; ?>');
        $this->tester->run_all_tests();
        $result = (($GLOBALS['TEST_VARIABLE4'] == 'test_variable_content4') && ($GLOBALS['TEST_VARIABLE5'] == 'test_variable_content5'));
        $this->dir->delete_files(array('tester4.data.t.php', 'tester5.data.t.php'));
        unset($GLOBALS['TEST_VARIABLE4']);
        unset($GLOBALS['TEST_VARIABLE5']);
        return $result;
    }

}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_Tester($env));

?>
