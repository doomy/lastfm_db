<?php

final readonly class Env {
    private string $basedir;


    /**
     * @var array<string, int|string>
     */
    private array $config;

    public function __construct($basedir, private readonly CurlFetcher $curlFetcher) {
        $this->basedir = $basedir;
        //$this->include_packages(array('dir', 'template'));
        $this->_set_env_vars_from_env_files();
    }
    
    public function include_snippet($name, $args = null) {
        $snippet = new Template("snippets/$name.tpl.php", $args);
        $snippet->show();
    }

    function _set_env_vars_from_env_files() {
        $files = $this->_get_files_from_env_dir();
        if ($files) {
            $this->_set_env_vars($files);
        }
    }
    
    private function _get_files_from_env_dir() {
        include_once(__DIR__ . "/dir.php");
        $dir_handler = new Dir($this, $this->curlFetcher);
        return $dir_handler->get_files_from_dir_by_extension(
            $this->basedir.'config', 'php'
        );
    }
    
    private function _set_env_vars($files) {
        foreach ($files as $file) {
            include($this->basedir . 'config/'.$file);
        }
        assert(isset($CONFIG) && is_array($CONFIG));
        $this->config = $CONFIG;
    }

    public function getBasedir(): string
    {
        return $this->basedir;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
