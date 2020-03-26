<?php

namespace Drupal\sedm\Database;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;


class DatabaseOperations {

    private $databaseCreds = array(
        'database' => 'test_drupal_data',
        'username' => 'testserver', // assuming this is necessary
        'password' => 'testserver', // assuming this is necessary
        'host' => 'localhost', // assumes localhost
        'port' => '3306', // default port
        'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql', // default namespace
        'driver' => 'mysql', // replace with your database driver
        // 'pdo' => array(PDO::ATTR_TIMEOUT => 2.0, PDO::MYSQL_ATTR_COMPRESS => 1), // default pdo settings
    );

    public function __construct(){

        Database::addConnectionInfo('test_drupal_data', 'default', $this->databaseCreds);

    }

    public function getSubjectsByCollege($collegeUID){

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

        $query = $connection->query("SELECT * FROM {subjects, departments, colleges} WHERE 
        departments.college_uid = colleges.college_uid
        AND
        subjects.department_uid = departments.department_uid
        AND
        subjects.subject_isActive = 'active'
        AND
        colleges.college_uid = :collegeUID", [
            ':collegeUID' => $collegeUID
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

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

    public function getProgramsByDepart($departmentUID){

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

        $query = $connection->query("SELECT * FROM {programs} WHERE department_uid = :departmentUID", 
        [
            ':departmentUID' => $departmentUID,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    public function getProgramsByCollege($collegeUID){
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

        $query = $connection->query("SELECT * FROM {programs} 
        WHERE college_uid = :collegeUID", 
        [
            ':collegeUID' => $collegeUID,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    /**
     * @public
     * @function getSubjectCategories : returns all the registered subject categories
     */
    public function getSubjectCategories(){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query("SELECT * FROM {subjects_category}");

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    
    }

    public function getSubjectByCategory($subj_cat){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query("SELECT * FROM {subjects}
        WHERE subjCat_uid = :subj_cat", 
        [
            ':subj_cat' => $subj_cat,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

    public function getSubjectByCode($subj_code, $subj_cat = NULL){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        if($subj_cat == NULL){
            $query = $connection->query("SELECT * FROM {subjects} WHERE subject_code = :subj_code",
            [
                ':subj_code' => $subj_code,
            ]);
        }
        else {
            $query = $connection->query("SELECT * FROM {subjects} 
            WHERE subject_code = :subj_code AND subjCat_uid = :subj_cat ",
            [
                ':subj_code' => $subj_code,
                ':subj_cat' => $subj_cat,
            ]);
        }

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

    public function isSubjectAvailable($subj_code, $subj_cat){

        $result = $this->getSubjectByCode($subj_code, $subj_cat);
        return $result ? NULL : true;
        
    }


}

?>