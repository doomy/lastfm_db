<?php
class Dir {
    # version 17

    public function __construct($env) {
        $this->env = $env;
    }

    public function get_files_from_dir_by_extension($dir, $extension) {
        if ($all_files = $this->_get_files_from_dir($dir)) {
            foreach ($all_files as $file) {
                if ($this->_file_has_extension($file, $extension)) {
                    $files[] = $file;
                }
            }
            return $files;
        }
        else return false;
    }
    
    public function create_dir($dir_name) {
        mkdir($dir_name);
    }
    
    public function delete_dir($dir_name) {
        $this->_reset_dir_access($dir_name);
        rmdir($dir_name);
    }
    
    public function get_current_dirname() {
        $path = str_replace('\\', '/', getcwd());
        $parts = explode('/', $path);
        return array_pop($parts);
    }
    
    public function change_dir($dir) {
        chdir($dir);
    }

    public function get_files_from_dir_and_its_subdirs($dir) {
        $original_dir = getcwd();
        $all_from_dir = $this->_get_all_from_dir($dir);
        $file_list = array();
        foreach ($all_from_dir as $dir_element) {
            if ($this->_is_file($dir.'/'.$dir_element)) {
                $file_list[] = $dir_element;
            }
            else {
                $this->change_dir($dir);

                $filelist_to_add = $this->_add_path_to_filelist($this->get_files_from_dir_and_its_subdirs($dir_element), $dir_element);
                $file_list = array_merge($file_list, $filelist_to_add);
                $this->change_dir('..');
            }
        }
        $this->change_dir($original_dir);
        return $file_list;
    }
    
    public function get_files_from_dir_and_its_subdirs_by_extension($dir, $extension) {
        $file_list = $this->get_files_from_dir_and_its_subdirs($dir);
        $file_list_out = array();
        foreach ($file_list as $file) {
            if($this->_file_has_extension($file, $extension)) {
                $file_list_out[] = $file;
            }
        }
        return $file_list_out;
    }

    public function create_empty_file($file_path) {
        touch($file_path);
    }
    
    public function delete_file($file_path) {
        unlink($file_path);
    }
    
    public function delete_files($files) {
        foreach($files as $file) {
            $this->delete_file($file);
        }
    }
    
    public function put_contents_into_file($file_name, $contents) {
        include_once($this->env->basedir . 'lib/file.php');
        $file = new File($file_name);
        $file->put_contents($contents);
    }
    
    public function get_contents_from_file($file_name) {
        return file_get_contents($file_name);
    }
    
    public function file_exists($file_name) {
        return file_exists($file_name);
    }

    private function _is_file($path) {
        return !is_dir($path);
    }

    private function _get_files_from_dir($dir) {
        if ($all_from_dir = $this->_get_all_from_dir($dir)) {
            $files = array();
            foreach($all_from_dir as $dir_element)
                if ($this->_is_file($dir.'/'.$dir_element))
                    $files[] .= $dir_element;
            return $files;
        }
        else return false;
    }

    private function _get_all_from_dir($dir) {

        if ($handle = @opendir($dir)) {

            $files = array();
            while ($file = readdir($handle)) {
                if ($file != "." && $file != "..") {
                    $files[] = $file;
                }
            }
            return $files;
        }
        else return false;
    }

    private function _file_has_extension($filename, $extension) {
        $parts = explode('.', $filename);
        return (array_pop($parts) == $extension);
    }

    private function _reset_dir_access($dir) {
        $handle = opendir($dir);
        closedir($handle);
    }
    
    private function _add_path_to_filelist($filelist, $path) {
        foreach($filelist as $key => $filename) {
            $filelist[$key] = $path.'/'.$filename;
        }
        return $filelist;
    }
}
?>
