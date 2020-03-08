<?php

namespace Drupal\sedm\Database;


class DatabaseCredentials {


    public function __construct(){
        $this->database = 'test_drupal_data';
        $this->username = 'testserver';
        $this->password = 'testserver';
        $this->host = 'localhost';
        $this->driver = 'mysql';
    }

    public function getCreds(){

        $databaseCreds = array(
            'database' => $this->database,
            'username' => $this->username, // assuming this is necessary
            'password' => $this->password, // assuming this is necessary
            'host' => $this->host, // assumes localhost
            'port' => '3306', // default port
            'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql', // default namespace
            'driver' => $this->driver, // replace with your database driver
            // 'pdo' => array(PDO::ATTR_TIMEOUT => 2.0, PDO::MYSQL_ATTR_COMPRESS => 1), // default pdo settings
        );

        return $databaseCreds;


    }

}

?>