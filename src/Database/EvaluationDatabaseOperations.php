<?php

namespace Drupal\sedm\Database;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\DatabaseOperations;

class EvaluationDatabaseOperations extends DatabaseOperations {

    public function getActiveSubjects($college){
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

        $query = $connection->query('SELECT *
        FROM {subjects, curriculum_subjects, curriculums, programs}
        WHERE programs.program_uid = curriculums.program_uid
        AND curriculums.curriculum_uid = curriculum_subjects.curriculum_uid
        AND curriculum_subjects.subject_uid = subjects.subject_uid
        AND subjects.subject_isActive = :active
        AND subjects.college_uid = :college_uid', [
            ':active' => 'active',
            ':college_uid' => $college,
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

    public function getAvailableSubjects($id_number, $year_level, $sem){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query('');

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

}

?>