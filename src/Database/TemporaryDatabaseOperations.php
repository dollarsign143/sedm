<?php

namespace Drupal\sedm\Database;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\DatabaseOperations;

class TemporaryDatabaseOperations extends DatabaseOperations{

    public function checkStudIdNumber($id_number){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query("SELECT *
        FROM students
        WHERE student_schoolId = :id_number",
        [
            ':id_number' => $id_number,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();
        

        return $result;
    }

    public function isStudentAlreadyRegistered($id_number){

        $result = $this->checkStudIdNumber($id_number);

        return (!empty($result)) ? true : false;
    }

    public function getSubjectInfo($subject_uid){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query("SELECT *
        FROM subjects
        WHERE subject_uid = :subject_uid",
        [
            ':subject_uid' => $subject_uid,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();
        

        return $result;
    }

    public function getStudentInfo($id_number){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query('SELECT *
        FROM students, student_profile, programs, colleges
        WHERE students.student_uid = student_profile.student_uid
        AND students.program_uid = programs.program_uid
        AND programs.college_uid = colleges.college_uid
        AND students.student_schoolId = :student_id', 
        [
            ':student_id' => $id_number,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

    public function checkSubjectOnStudentSubjects($stud_uid, $subj_uid){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query("SELECT *
        FROM students_subjects
        WHERE subject_uid = :subj_uid
        AND student_uid = :stud_uid",
        [
            ':subj_uid' => $subj_uid,
            ':stud_uid' => $stud_uid,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();
        
        if(empty($result)){
            return false;
        }
        else {
            return true;
        }

    }

    public function insertStudentSubjectGrade($subj_info, $stud_uid){
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
        // studSubj_uid	int(11) Auto Increment	
        // student_uid	int(11)	
        // subject_uid	int(11)	
        // studSubj_remarks	varchar(25)	
        // studSubj_finalRemarks	varchar(25)

        try {

            $result = $connection->insert('students_subjects')
            ->fields([
                'studSubj_uid' => NULL,
                'student_uid' => $stud_uid,
                'subject_uid' => $subj_info['subject_uid'],
                'studSubj_remarks' => $subj_info['remarks'],
                'studSubj_finalRemarks' => $subj_info['final_remarks'],
            ])
            ->execute();
    
            return true;

        } catch (Exception $e) {
            \Drupal::logger('type')->error($e->getMessage());
            return false;
        }
    }
}

?>