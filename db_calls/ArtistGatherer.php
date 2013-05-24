<?php
class ArtistGatherer_db_calls {
// version 1

    public function artist_exists($dbh, $artist_name) {
        $sql = "SELECT 1 as result FROM t_artist_names WHERE name = '$artist_name';";
        $result = $dbh->fetch_one_from_sql($sql);
        if ($result)
            return $result->result;
    }
}
?>
