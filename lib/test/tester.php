<?php


class Tester {
// version 5

    public function __construct($env) {
        $this->env = $env;
        include_once($this->env->basedir . 'lib/dir.php');
        $this->dir = new Dir($this->env);

    }
    
    public function run_testfile($testfile_path) {
        $path_parts = explode('/', $testfile_path);
        $testfile_name = array_pop($path_parts);
        echo "<strong>$testfile_name</strong><br /><p style='margin-top: 5px'>";
        $original_directory = getcwd();
        $new_dir = $this->env->basedir . 'test/' .implode('/', $path_parts);
        $this->dir->change_dir($new_dir);
        include_once($testfile_name);
        $this->dir->change_dir($original_directory);
        echo "</p>";
    }
    
    public function run_multiple_testfiles($testfiles) {
        foreach ($testfiles as $testfile) {
            $this->run_testfile($testfile);
        }
    }
    
    public function run_all_tests($dir = "") {
        $all_tests =
            $this->dir->get_files_from_dir_and_its_subdirs(
                $this->env->basedir . 'test'
            );
        $this->run_multiple_testfiles($all_tests);
    }
}

?>
