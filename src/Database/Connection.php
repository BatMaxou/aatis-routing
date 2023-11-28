<?php

namespace Aatis\Core\Database;

class Connection
{
    private const HOST = 'db';
    private const USER = 'root';
    private const PASS = 'root';
    private const DBNAME = 'aatis_bundle';

    private static ?\PDO $connection = null;

    public static function connect(): \PDO
    {
        if (!self::$connection) {
            try {
                self::$connection = new \PDO('mysql:host='.self::HOST.';dbname='.self::DBNAME, self::USER, self::PASS);
                self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                dd('Echec de connexion'.$e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function disconnect(): void
    {
        self::$connection = null;
    }
}
