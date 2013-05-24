<?php
class dbHandler {
    # version 13

    private $connection;

    public function __construct($env) {
        $this->env = $env;
        $this->connection = mysql_connect(
            $this->env->ENV_VARS['DB_HOST'],
            $this->env->ENV_VARS['DB_USER'],
            $this->env->ENV_VARS['DB_PASS']
        );
        mysql_select_db($env->ENV_VARS['DB_NAME'], $this->connection);
        if ($this->env->ENV_VARS['DB_CREATE']) {
            $this->_create_db();
        }
        $this->_manage_upgrades();
    }

    public function query_get_assoc_onerow(
        $columns_list, $table, $where = false, $order_by = '', $desc = false
    ) {
        $result = $this->_query_get_result($columns_list, $table, $where, $order_by, $desc, 1);
        return $this->fetch_one_from_result($result, 'assoc');
    }

    public function query_get_obj_onerow(
        $columns_list, $table, $where = false, $order_by = '', $desc = false
    ) {
        $result = $this->_query_get_result($columns_list, $table, $where, $order_by, $desc, 1);
        return $this->fetch_one_from_result($result, 'object');
    }

    public function query($sql) {
        return mysql_query($sql, $this->connection);
    }

    public function get_array_of_rows_from_table(
        $table_name, $columns = null, $where = null, $format = 'object'
    ) {
        $result = $this->_query_get_result($columns, $table_name, $where);
        return $this->fetch_multiple_from_result($result, $format);
    }

    public function process_sql($sql) {
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $this->query($query.';');
        }
    }

    public function process_sql_file($path) {
        $sql = file_get_contents($path);
        $this->process_sql($sql);
    }
    
    public function fetch_one_from_sql($sql, $format = 'object') {
        $result = $this->query($sql);
        return call_user_func(array($this, "_fetch_".$format), $result);
    }

    public function fetch_one_from_result($result, $format = 'object') {
        $function_name = "mysql_fetch_$format";
        return $function_name($result);
    }

    public function fetch_multiple_from_result($result, $format = 'object') {
        $function_name = "mysql_fetch_$format";
        while ($row = $function_name($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function run_db_call($package, $db_call_name) {
        include_once($this->env->basedir . "db_calls/$package.php");
        $package_class = $package."_db_calls";
        $package = new $package_class();
        $arg_list = func_get_args();
        array_shift($arg_list);
        array_shift($arg_list);
        array_unshift($arg_list, $this);
        return call_user_func_array(array($package, $db_call_name), $arg_list);
    }
    
    function _fetch_array($result) {
        return mysql_fetch_array($result);
    }
    
    function _fetch_object($result) {
        return mysql_fetch_object($result);
    }
    
    private function _create_db() {
        $this->process_sql_file($this->env->basedir.'sql/base.sql');
    }

    private function _manage_upgrades() {
        $last_processed_upgrade_id = $this->_get_last_processed_upgrade_id();
        $upgrade_files = $this->_get_upgrade_files();
        sort($upgrade_files);
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
        $this->_update_upgrade_version($upgrade_id);
    }

    private function _get_last_processed_upgrade_id() {
        $assoc_array = @$this->query_get_assoc_onerow(
            array('id'), 'upgrade_history', false, 'id', true
        );
        return $assoc_array['id'];
    }

    private function _get_upgrade_files() {
        include_once($this->env->basedir.'lib/dir.php');
        $dir_handler = new Dir($this->env);
        return $dir_handler->get_files_from_dir_by_extension(
             $this->env->basedir.'sql/upgrade', 'sql'
        );
    }

    private function _update_upgrade_version($upgrade_id) {
        $sql = "INSERT INTO upgrade_history (id, message) VALUES('$upgrade_id', 'Upgrade no. $upgrade_id');";
        $this->query($sql);
    }

    private function _query_get_result(
        $columns_list = null, $table, $where = null, $order_by = '', $desc = false, $limit = null
    ) {
        if (!$columns_list) $columns = '*';
        else $columns = implode(', ', $columns_list);
        if ($order_by <> '')
            $order_by = "ORDER BY $order_by";
        if ($where) $where = "WHERE $where";
        if ($desc) $desc = 'DESC';
        if ($limit) $limit = "LIMIT $limit";
        else
            $desc = '';
        $sql = "SELECT $columns FROM $table $where $order_by $desc $limit;";
        return $this->query($sql);
    }
}
?>
