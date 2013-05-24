<?php

include_once ('../../lib/env.php');
$env = new Env('../../');
include_once ($env->basedir . 'lib/test/unit_test_base.php');
include_once ($env->basedir . 'lib/file.php');

class UnitTest_File extends UnitTestBase {
     public function init() {
         $this->file = new File('testing_file_name.tst');
     }

    public function test_set_name_get_name() {
        $this->file->set_name('testing_file_name2.tst');
        return ($this->file->get_name() == ('testing_file_name2.tst'));
        $this->file->set_name('testing_file_name.tst');
    }
    
    public function test_put_contents_get_contents() {
        $this->file->put_contents('test contents');
        $result = ($this->file->get_contents() == 'test contents');
        $this->dir->delete_file($this->file->get_name());
        return $result;
    }
    
    public function test_get_trimmed_lines() {
        $lines = <<<EOT
LINE 1
LINE 2
EOT;
        $this->file->put_contents($lines);
        $lines = $this->file->get_trimmed_lines();
        $this->dir->delete_file($this->file->get_name());
        return ($lines == array("LINE 1", "LINE 2"));
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_File($env));

?>
