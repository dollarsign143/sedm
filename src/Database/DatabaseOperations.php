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

    public function addNewSubject($subject){
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

        // subject_uid	int(11) Auto Increment	
        // subject_code	varchar(40) NULL	
        // subject_desc	varchar(255) NULL	
        // subject_lecture	int(11) NULL	
        // subject_lab	int(11) NULL	
        // subject_units	int(11) NULL	
        // subject_lecHrs	int(11) NULL	
        // subject_labHrs	int(11) NULL	
        // subject_isElective	varchar(40) NULL	
        // subject_isActive	varchar(40) NULL	
        // department_uid	int(11) NULL

        $result = $connection->insert('subjects')
        ->fields([
            'subject_uid' => NULL,
            'subject_code' => $subject['code'],
            'subject_desc' => $subject['description'],
            'subject_lecture' => $subject['lectUnits'],
            'subject_lab' => $subject['labUnits'],
            'subject_units' => $subject['units'],
            'subject_lecHrs' => $subject['lecHours'],
            'subject_labHrs' => $subject['labHours'],
            'subject_isElective' => $subject['isElective'],
            'subject_isActive' => $subject['isActive'],
            'department_uid' => $subject['departmentUID'],

        ])
        ->execute();

        return $result;

    }


}

?>