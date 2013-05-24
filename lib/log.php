<?php
class Log extends BasePackage {
// version 4
    public function __construct($name, $env, $modes = array('filesystem')) {
        $this->modes = $modes;
        $this->env = $env;
        $this->include_packages(array('file'));
        $this->name = $name;
        $this->file = new File($this->env->basedir."log/$name.log");
    }

    public function log($text) {
        $log_string = $this->_get_log_datetime_string() . " " . $text.PHP_EOL;
        if (in_array('filesystem', $this->modes))
            $this->file->put_contents($log_string);
        if (in_array('stdout', $this->modes))
            print($log_string);
    }
    
    function _get_log_datetime_string() {
        return date('d.m.Y H:i:s');
    }
    
    
}
?>
