<?php

include_once (__DIR__ . "/../lib/DbCall.php");

class DbHandler_db_calls extends DbCall  {
    public function get_last_processed_upgrade_id() {
        $sql = "SELECT MAX(id) AS max_id FROM t_upgrade_history;";
        $result = $this->mysqli->query($sql);
        // why the fuck the following line?
        // $this->mysqli->query("ALTER TABLE t_artist_names IMPORT TABLESPACE;");
        if (!$result) return 0;
        $row = $result->fetch_object();
        return $row->max_id;
    }

}
