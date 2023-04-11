<?php

use Base\Model;

class ArtistGatherer_db_calls extends Model {
// version 2

    public function artist_exists($artist_name) {
        $sql = "SELECT 1 AS result FROM t_artist_names WHERE LOWER(name) = LOWER('$artist_name');";
        $result = $this->mysqli->query($sql);
        if (!$result) return FALSE;
        $row = $result->fetch_object();
        if ($row)
            return $row->result;
        return false;
    }
    
    public function insert_artist($artist_name) {
        $sql = "INSERT INTO t_artist_names (name) VALUES('$artist_name');";
        $this->mysqli->query($sql);
    }
    
    public function random_artist($minRating) {
        $sql = "SELECT id, name, rating, note FROM t_artist_names WHERE rating >= $minRating ORDER BY RAND() LIMIT 1;";
        $result = $this->mysqli->query($sql);
        return $result->fetch_object();
    }
    
    public function artist_count() {
        $sql = "SELECT COUNT(*) AS artist_count FROM t_artist_names;";
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_object();
        if ($row) return $row->artist_count;
    }
    
    public function max_rating() {
        $sql = "SELECT MAX(rating) max_rating FROM t_artist_names;";
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_object();
        if ($row) return $row->max_rating;
    }
    
    public function change_rating($id, $direction) {

        if ($direction == 'minus')
            $this->mysqli->query("UPDATE t_artist_names SET rating = rating / 2 WHERE id = $id;");
        elseif ($direction == 'plus')
            $this->mysqli->query("UPDATE t_artist_names SET rating = rating + 1 WHERE id = $id;");
        return true;
    }
    
    /*public function kickstart($id) {
        $this->mysqli->query("UPDATE t_artist_names SET rating = 1600 WHERE id = $id;");
        return true;
    }*/

    public function fetchUnprocessedUsernames($limit) {
       $result = $this->mysqli->query("SELECT username FROM t_username WHERE processed = 0 ORDER BY id ASC LIMIT $limit");
       $rows = $result->fetch_all();
       $usernames = [];
       foreach ($rows as $row) {
            $usernames[] = $row[0];
       }

       return $usernames;
    }


public function insertUsernames($usernames): ?int
    {
        $valuesToInsert = [];
        foreach ($usernames as $username) {
            $valuesToInsert[] = "('$username')";
        }

        if ($valuesToInsert === []) {
            return null;
        }

        $valuesCode = implode(",", $valuesToInsert);
        $sql = "INSERT IGNORE INTO t_username(username) VALUES $valuesCode;";


        try {
            $this->mysqli->query($sql);
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
            var_dump($sql);
            die;
        }
        //die($this->mysqli->error);
        return $this->mysqli->affected_rows;

    }

    public function markUsernameAsProcessed($username) {
        return $this->mysqli->query("UPDATE t_username SET processed = 1 WHERE username = '$username';");
    }

    public function getUnprocessedUsernamesCount() {
        return $this->mysqli->query("SELECT COUNT(*) cnt FROM t_username WHERE processed = 0;")->fetch_object()->cnt;
    }
}
?>
