<?php

$BASE_PATH = '../../';

include_once ($BASE_PATH . 'lib/env.php');
$env = new Env($BASE_PATH);
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/log.php');

class MockLog extends Log {
    function _get_log_datetime_string() {
        return '26.04.1988 15:24:35';
    }
}

class UnitTest_Log extends UnitTestBase {
    public function test_construct() {
        return ($log = new Log('test', $this->env));
    }

    public function test_log() {
        $log = new MockLog('test', $this->env);
        $log_test_record = 'log test record';
        $log->log($log_test_record);
        $LOG_PATH = $this->env->basedir . 'log/test.log';
        $file_exists = $this->dir->file_exists($LOG_PATH);
        $log_file = new File($LOG_PATH);
        $is_correct_content =
            ($log_file->get_contents() ==
                "26.04.1988 15:24:35 $log_test_record".PHP_EOL);
        $this->dir->delete_file($LOG_PATH);
        return ($file_exists && $is_correct_content);
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_Log($env));
?>
