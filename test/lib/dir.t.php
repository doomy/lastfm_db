<?php
include_once('../../lib/env.php');
$env = new Env('../../');
include_once($env->basedir . 'lib/test/unit_test_base.php');

class UnitTest_Dir extends UnitTestBase{

    private $TESTING_DIRECTORY_NAME = 'testing_directory';
    private $TESTING_FILE_NAME = 'testing_file.tst';
    
    public function test_get_files_from_dir_by_extension_returns_false_when_directory_doesnt_exist() {
        return (!$this->dir->get_files_from_dir_by_extension('non-existent-dir', 'somext'));
    }
    
    public function test_create_dir() {
        $this->_init_testing_dir();
        $result = is_dir($this->TESTING_DIRECTORY_NAME);
        $this->dir->delete_dir($this->TESTING_DIRECTORY_NAME);
        return $result;
    }
    
    public function test_delete_dir() {
        $this->_init_testing_dir();
        $this->dir->delete_dir($this->TESTING_DIRECTORY_NAME);
        $result = !is_dir($this->TESTING_DIRECTORY_NAME);
        return $result;
    }

    public function test_get_current_dirname__and__change_dir() {
        $this->_init_testing_dir();
        $this->dir->change_dir($this->TESTING_DIRECTORY_NAME);
        $result = ($this->dir->get_current_dirname() == $this->TESTING_DIRECTORY_NAME);
        $this->dir->change_dir('..');
        $this->dir->delete_dir($this->TESTING_DIRECTORY_NAME);
        return $result;
    }

    public function test_get_files_from_dir_and_its_subdirs() {
        $this->_init_testing_dir();
        $second_level_dir_path = $this->TESTING_DIRECTORY_NAME . '/' . 'second_level_dir';
        $testing_file_path_1 = $this->TESTING_DIRECTORY_NAME . '/testing_file_name_1.tst';
        $testing_file_path_2 = $second_level_dir_path . '/testing_file_name_2.tst';
        $this->dir->create_dir($second_level_dir_path);
        $this->dir->create_empty_file($testing_file_path_1);
        $this->dir->create_empty_file($testing_file_path_2);
        $result = $this->dir->get_files_from_dir_and_its_subdirs(
            $this->TESTING_DIRECTORY_NAME
        );
        
        unlink($testing_file_path_2);
        $this->dir->delete_dir($second_level_dir_path);
        unlink($testing_file_path_1);
        $this->_reset_testing_dir();
        return ($result == array('second_level_dir/testing_file_name_2.tst', 'testing_file_name_1.tst'));
    }
    
    public function test_get_files_from_dir_and_its_subdirs_by_extension() {
        $this->_init_testing_dir();
        $second_level_dir_path = $this->TESTING_DIRECTORY_NAME . '/' . 'second_level_dir';
        $testing_file_path_1 = $this->TESTING_DIRECTORY_NAME . '/testing_file_name_1.tst';
        $testing_file_path_2 = $second_level_dir_path . '/testing_file_name_2.tst2';
        $this->dir->create_dir($second_level_dir_path);
        $this->dir->create_empty_file($testing_file_path_1);
        $this->dir->create_empty_file($testing_file_path_2);
        $result = $this->dir->get_files_from_dir_and_its_subdirs_by_extension(
            $this->TESTING_DIRECTORY_NAME, 'tst'
        );
        unlink($testing_file_path_2);
        $this->dir->delete_dir($second_level_dir_path);
        unlink($testing_file_path_1);
        $this->_reset_testing_dir();
        return ($result == array('testing_file_name_1.tst'));
    }

    public function test_create_empty_file_file_created() {
        $this->dir->create_empty_file($this->TESTING_FILE_NAME);
        $result = ($this->dir->file_exists($this->TESTING_FILE_NAME) && (filesize($this->TESTING_FILE_NAME) == 0));
        $this->dir->delete_file ($this->TESTING_FILE_NAME);
        return $result;
    }
    
    public function test_delete_file() {
        $this->dir->create_empty_file($this->TESTING_FILE_NAME);
        $this->dir->delete_file($this->TESTING_FILE_NAME);
        return !$this->dir->file_exists($this->TESTING_FILE_NAME);
    }
    
    public function test_delete_files() {
        $this->dir->create_empty_file('testing_file_name.tst');
        $this->dir->create_empty_file('testing_file_name2.tst');
        $this->dir->delete_files(array('testing_file_name.tst', 'testing_file_name2.tst'));
        return (!$this->dir->file_exists('testing_file_name.tst') && !$this->dir->file_exists('testing_file_name2.tst'));
    }
    
    public function test_put_contents_into_file_get_contents_of_file() {
        $testing_contents = 'TESTING CONTENTS';
        $this->dir->put_contents_into_file($this->TESTING_FILE_NAME, $testing_contents);
        $result =
            $this->dir->get_contents_from_file($this->TESTING_FILE_NAME) == $testing_contents;
        $this->dir->delete_file($this->TESTING_FILE_NAME);
        return $result;
    }
    
    public function test_file_exists() {
        $this->dir->create_empty_file('testing_file_name.tst');
        $file_should_exist = $this->dir->file_exists('testing_file_name.tst');
        $this->dir->delete_file('testing_file_name.tst');
        $file_should_not_exist = !$this->dir->file_exists('testing_file_name.tst');
        return ($file_should_exist && $file_should_not_exist);
    }

    private function _reset_testing_dir() {
        if (is_dir($this->TESTING_DIRECTORY_NAME)) {
            $this->dir->delete_dir($this->TESTING_DIRECTORY_NAME);
        }
    }
    
    private function _init_testing_dir() {
        $this->_reset_testing_dir();
        $this->dir->create_dir($this->TESTING_DIRECTORY_NAME);
    }
}

$unit_test_runner = new UnitTestRunner;
$unit_test_runner->run_tests(new UnitTest_Dir($env));
?>
