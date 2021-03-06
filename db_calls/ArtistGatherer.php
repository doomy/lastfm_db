<?php
class ArtistGatherer_db_calls {
// version 2

    public function __construct($dbh) {
        $this->dbh = $dbh;
    }

    public function artist_exists($artist_name) {
        $sql = "SELECT 1 AS result FROM t_artist_names WHERE name = '$artist_name';";
        $result = $this->dbh->fetch_one_from_sql($sql);
        if ($result)
            return $result->result;
    }
    
    public function insert_artist($artist_name) {
        $sql = "INSERT INTO t_artist_names (name) VALUES('$artist_name');";
        $this->dbh->query($sql);
    }
    
    public function random_artist() {
        $sql = "SELECT name FROM t_artist_names ORDER BY RAND() LIMIT 1;";
        $result = $this->dbh->fetch_one_from_sql($sql);
        if ($result) return $result->name;
    }
    
    public function artist_count() {
        $sql = "SELECT COUNT(*) AS artist_count FROM t_artist_names;";
        $result = $this->dbh->fetch_one_from_sql($sql);
        if ($result) return $result->artist_count;
    }
}
?>
