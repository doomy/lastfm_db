<?php
class File {
//version 4
    public function __construct($name) {
        $this->set_name($name);
    }

    public function set_name($name) {
        $this->name = $name;
    }
    
    public function get_name() {
        return $this->name;
    }
    
    public function put_contents($data) {
        if (!isset($this->name)) die('No filename specified!');
        file_put_contents($this->name, $data, FILE_APPEND);
    }
    
    public function get_contents() {
        return file_get_contents($this->name);
    }
    
    public function get_trimmed_lines() {
        return $this->_trim_lines(explode("\n", $this->get_contents()));
    }
    
    function _trim_lines($untrimmed_lines) {
        foreach ($untrimmed_lines as $untrimmed_line) {
            $trimmed_lines[] = trim($untrimmed_line);
        }
        return $trimmed_lines;
    }
}
?>
