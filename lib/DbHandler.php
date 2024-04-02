<?php

final class DbHandler {
    # 23.11.2014

    private $mysqli;

    public function __construct(
        private readonly Env $env,
        private readonly CurlFetcher $curlFetcher
    ) {
        $this->mysqli = $this->get_mysqli_connection();
        $this->mysqli->set_charset('utf8');

        if ($this->env->CONFIG['DB_CREATE']) {
            $this->_create_db();
        }
        $this->_manage_upgrades();
    }

    public function process_sql($sql) {
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $this->mysqli->query($query.';');
        }
    }

    public function process_sql_file($path) {
        $sql = file_get_contents($path);
        $this->process_sql($sql);
    }
    
    public function run_db_call($package, $db_call_name) {
        include_once(__DIR__ . "/../db_calls/$package.php");
        $package_class = $this->_get_valid_db_call_class_name($package);
        $package = new $package_class($this->mysqli);
        $arg_list = func_get_args();
        array_shift($arg_list);
        array_shift($arg_list);
        return call_user_func_array(array($package, $db_call_name), $arg_list);
    }

    function _get_valid_db_call_class_name($package) {
        $parts = explode('/', $package);
        return array_pop($parts) . "_db_calls";
    }
    
    private function _create_db() {
        $this->process_sql_file($this->env->basedir.'sql/base.sql');
    }

    private function _manage_upgrades() {
        $last_processed_upgrade_id = $this->run_db_call('DbHandler', 'get_last_processed_upgrade_id');
        $upgrade_files = $this->_get_upgrade_files();
        if(!$upgrade_files) return;
        sort($upgrade_files, SORT_NUMERIC);
        $last_file = @end($upgrade_files);
        $newest_upgrade_id = $this->_get_upgrade_id_from_filename($last_file);

        if ($newest_upgrade_id > $last_processed_upgrade_id) {
            $this->_upgrade_to_actual(
                $upgrade_files, $last_processed_upgrade_id
            );
        }
    }

    private function _upgrade_to_actual(
        $upgrade_files, $last_processed_upgrade_id
    )
    {
        foreach ($upgrade_files as $upgrade_file) {
            $upgrade_id = $this->_get_upgrade_id_from_filename($upgrade_file);
            if ($upgrade_id > $last_processed_upgrade_id) {
                $this->_upgrade_to_version($upgrade_id, $upgrade_file);
            }
        }
    }

    private function _get_upgrade_id_from_filename($upgrade_file) {
        $parts = explode('.', $upgrade_file);
        return $parts[0];
    }

    private function _upgrade_to_version($upgrade_id, $upgrade_file) {
        $this->process_sql_file(
            $this->env->basedir . 'sql/upgrade/' . $upgrade_file
        );
        if (!($this->_get_db_error())) $this->_update_upgrade_version($upgrade_id);
        else die($this->_get_db_error());
    }
    
    private function _get_db_error() {
        if ($this->mysqli->error != "Query was empty")
            return $this->mysqli->error;
        return false;
    }

    private function _get_upgrade_files() {
        $dir_handler = new Dir($this->env, $this->curlFetcher);
        return $dir_handler->get_files_from_dir_by_extension(
             $this->env->basedir.'sql/upgrade', 'sql'
        );
    }

    private function _update_upgrade_version($upgrade_id) {
        $sql = "INSERT INTO t_upgrade_history (id, message) VALUES('$upgrade_id', 'Upgrade no. $upgrade_id');";
        $this->mysqli->query($sql);
    }

    public function get_mysqli_connection() {
        if ($this->mysqli) return $this->mysqli;
        else {

            $this->mysqli = new mysqli(
                $this->env->CONFIG['DB_HOST'],
                $this->env->CONFIG['DB_USER'],
                $this->env->CONFIG['DB_PASS'],
                $this->env->CONFIG['DB_NAME'],
                $this->getPort()
            );
        }
        return $this->mysqli;
    }

    private function getPort(): int
    {
        return $this->env->CONFIG['DB_PORT'] ?? ini_get("mysqli.default_port");
    }
}
?>
