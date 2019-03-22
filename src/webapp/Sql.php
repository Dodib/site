<?php

namespace ttm4135\webapp;

class Sql
{
    static $pdo;

    function __construct()
    {
    }

    /**
     * Create tables.
     */
    static function up() {
        $q1 = "CREATE TABLE users (id INTEGER PRIMARY KEY, username VARCHAR(50), password VARCHAR(100), email varchar(50),  bio varhar(50), isadmin INTEGER, failedattempts INTEGER);";

        self::$pdo->exec($q1);

        print "[ttm4135] Done creating all SQL tables.".PHP_EOL;

        self::insertDummyUsers();
    }

    static function insertDummyUsers() {


	$password_admin = password_hash('wearegods123', PASSWORD_DEFAULT);
	$password_bob = password_hash('testing14', PASSWORD_DEFAULT);
	$q1 = "INSERT INTO users(username, password, isadmin, failedattempts) VALUES ('admin', '$password_admin', 1, 0)";
        $q2 = "INSERT INTO users(username, password, failedattempts) VALUES ('bobby', '$password_bob', 0)";
        self::$pdo->exec($q1);
        self::$pdo->exec($q2);
		
        print "[ttm4135] Done inserting dummy users.".PHP_EOL;
    }


    static function down() {
        $q1 = "DROP TABLE users";

        self::$pdo->exec($q1);

        print "[ttm4135] Done deleting all SQL tables.".PHP_EOL;
    }

}
try {
    // Create (connect to) SQLite database in file
    Sql::$pdo = new \PDO('sqlite:/home/grp14/apache/htdocs/site/app.db');
    // Set errormode to exceptions
    Sql::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch(\PDOException $e) {
    echo $e->getMessage();
    exit();
}
