<?php

namespace Drupal\sedm\Database;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\sedm\Database\DatabaseCredentials; // custom class: database credentials

class DatabaseOperations {

    protected $database;

    public function __construct(){

        $dbcreds = new DatabaseCredentials();

        Database::addConnectionInfo('test_drupal_data', 'default', $dbcreds->getCreds());

    }

    public function getSubjects(){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();;


        /**
         * Example Query
         * $query = $database->query("SELECT id, example FROM {mytable} WHERE created > :created", [
         *      ':created' => REQUEST_TIME - 3600,
         *    ]);
         */

        $query = $connection->query("SELECT * FROM {programs}");
        $result = $query->fetchAll();

        Database::setActiveConnection();

        return $result;
    }

    public function getColleges(){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        /**
         * Example Query
         * $query = $database->query("SELECT id, example FROM {mytable} WHERE created > :created", [
         *      ':created' => REQUEST_TIME - 3600,
         *    ]);
         */

        $query = $connection->query("SELECT * FROM {colleges}");
        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    public function getDepartments($collegeUID){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        /**
         * Example Query
        * $query = $database->query("SELECT id, example FROM {mytable} WHERE created > :created", [
        *      ':created' => REQUEST_TIME - 3600,
        *    ]);
        */

        $query = $connection->query("SELECT * FROM {departments} WHERE college_uid = :collegeUID", 
        [
            ':collegeUID' => $collegeUID,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }


}

?>