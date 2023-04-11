<?php
# version 7

class UnitTestBase {
    public function __construct($env) {
        $this->env = $env;
        include_once($this->env->basedir . 'lib/dir.php');
        $this->dir = new Dir($this->env);
        $this->init();
    }
    
    public function init() {
    }
}

class UnitTestRunner {
    public function run_tests($test_object) {
        $methods = get_class_methods(get_class($test_object));
        foreach ($methods as $method) {
            if(strpos($method, 'test_') > -1) {
                $this->_test($test_object, $method);
            }
        }
     }
     
    function _test($test_object, $method) {
        if (!$test_object->$method())
            echo "<strong style='color: red;' />FAIL: $method </strong></br>";
    }
}

?>
