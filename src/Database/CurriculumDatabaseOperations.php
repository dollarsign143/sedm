<?php

namespace Drupal\sedm\Database;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\DatabaseOperations;

class CurriculumDatabaseOperations extends DatabaseOperations {

    /**
     * @public
     * @function addNewSubject : method to add new subject
     * @param $subject : an array type of data that holds the 
     *                   subject infos.
     *  
     */
    public function addNewSubject($subject){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $transaction = $connection->startTransaction();

        /**
         * Example Query
        * $query = $database->query("SELECT id, example FROM {mytable} WHERE created > :created", [
        *      ':created' => REQUEST_TIME - 3600,
        *    ]);
        */

        /**
         * Subject table entities
         */
            // subject_uid	int(11) Auto Increment	
            // subject_code	varchar(40) NULL	
            // subject_desc	varchar(255) NULL	
            // subject_isActive	varchar(40) NULL
            // subjCat_uid	int(11) NULL
            // college_uid	int(11) NULL

        try {

            $result = $connection->insert('subjects')
            ->fields([
                'subject_uid' => NULL,
                'subject_code' => $subject['code'],
                'subject_desc' => $subject['description'],
                'subject_isActive' => $subject['isActive'],
                'subjCat_uid' => $subject['subjCat'],
                'college_uid' => $subject['collegeUID'],
            ])
            ->execute();
    
            return true;

        } catch (Exception $e) {
            \Drupal::logger('type')->error($e->getMessage());
            return false;
        }



    }

    public function getSubjects(){
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

        $query = $connection->query("SELECT * FROM {subjects}");

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
        
    }

    public function getSubjectCategory($subj_cat_uid){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();


        $query = $connection->query("SELECT * FROM {subjects_category} WHERE subjCat_uid = :subj_cat_uid",
        [
            ':subj_cat_uid' => $subj_cat_uid,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

}

?>