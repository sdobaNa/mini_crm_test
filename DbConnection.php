<?php

class DbConnection
{
    private static $connection = null;

    public static function getConnection()
    {
        require "config.php";
        if (self::$connection == null)
            try {
                self::$connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        return self::$connection;
    }
}
