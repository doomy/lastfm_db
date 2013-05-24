<?php
class Env {
    # version 6

    public function __construct($basedir) {
        $this->basedir = $basedir;
        $this->_set_env_vars_from_env_files();
    }
    
    public function var_dump($var) {
        echo "<br />".var_dump($var)."</br>";
    }
    
    function _set_env_vars_from_env_files() {
        $files = $this->_get_files_from_env_dir();
        if ($files) {
            $this->_set_env_vars($files);
        }
    }
    
    private function _get_files_from_env_dir() {
        include_once($this->basedir.'lib/dir.php');
        $dir_handler = new Dir($this);
        return $dir_handler->get_files_from_dir_by_extension(
            $this->basedir.'env_spec', 'php'
        );
    }
    
    private function _set_env_vars($files) {
        foreach ($files as $file) {
            include($this->basedir . 'env_spec/'.$file);
        }
        @$this->ENV_VARS = $ENV_VARS;
    }
}
?>
