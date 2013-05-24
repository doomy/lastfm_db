<?php
$BASE_PATH = '../../';

include_once ($BASE_PATH . 'lib/env.php');
$env = new Env($BASE_PATH);
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/db_handler.php');

class mockDbHandler_dbHandler extends dbHandler {
    public function __construct($env) {
    }
    
    public function query($sql) {
        return null;
    }
    
    function _fetch_object($result) {
        $object = new stdClass;
        $object->mock_property = 'mock_value';
        return $object;
    }
}

class UnitTest_dbHandler extends UnitTestBase {
    public function test_fetch_one_from_select() {
        $dbh = new mockDbHandler_dbHandler('');
        $sql = "mock_sql";
        $object = $dbh->fetch_one_from_sql($sql, 'object');
        return ($object->mock_property == 'mock_value');
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_dbHandler($env));

?>
