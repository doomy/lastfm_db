<?php 


final class Environment {
    private static $env;
    private static $dbh;

    public function __construct(private readonly CurlFetcher $curlFetcher, private readonly DbHandler $dbHandler) {}

    public function get_env() {
        if(!self::$env) {
            self::$env = new Env('', $this->curlFetcher);
        }
        return self::$env;
    }
    
    public function get_dbh() {
        return $this->dbHandler;
    }

    public static function var_dump($value, $caption = null) {
        echo "<p>";
            if ($caption)
                echo "<strong>$caption</strong><br />";
            var_dump($value);
        echo "</p>";
    }

    public static function var_dump_die($value, $caption = null) {
        self::var_dump($value, $caption);
        die();
    }

    public static function getConfig($configName) {
        return self::$env->CONFIG[$configName];
    }
}


?>