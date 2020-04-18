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

    /**
     * @param $data : includes $data['id_number'], $data['year_level'], $data['semester']
     * @param $curri_uid : curriculum unique id
     */
    public function getAvailableSubjects($data, $curri_uid){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $availableSubjs = array();

        // Algorithm:
        // #1: get the curriculum subjects of a certain year level and semester
        $curri_subjs = $this->getCurriculumSubjects($curri_uid, $data['year_level'], $data['semester']);
        // #2: for each subjects fetched will proceed to a 3 layer checking
        foreach($curri_subjs as $curri_subj){
            // #2.1: checking the subject using its code
            $enrolledSubjInfo = $this->getEnrolledSubjectInfoByCode($data['id_number'], $curri_subj->subject_code);
            // 2.1.1: if the subject is not found, proceed to checking the subject by description
            if(empty($enrolledSubjInfo)){
                // #2.2: checking the subject using its description
                $enrolledSubjInfo = $this->getEnrolledSubjectInfoByDesc($data['id_number'], $curri_subj->subject_desc);
                // #2.2.1: if the subject is not found.. proceed to the next checking the prerequisite satifactory
                if(empty($enrolledSubjInfo)){ 
                    // #2.3: check the prerequisite 1 satisfactory
                    $isPrereque1Satisfied = $this->isSubjectPrereqSatisfied($data['id_number'], $curri_subj->curricSubj_prerequisite1);
                    // #2.4: check the prerequisite 2 satisfactory
                    $isPrereque2Satisfied = $this->isSubjectPrereqSatisfied($data['id_number'], $curri_subj->curricSubj_prerequisite2);
                    // #2.5: if the prerequisites are satisfied the subject will be included to the available subjects for student
                    if($isPrereque1Satisfied && $isPrereque2Satisfied){
                        $availableSubjs[$curri_subj->subject_uid] = [
                            'subj_code' => $curri_subj->subject_code,
                            'subj_description' => $curri_subj->subject_desc,
                            'subj_units' => ($curri_subj->curricSubj_labUnits + $curri_subj->curricSubj_lecUnits),
                        ];
                    }
                }
                // #2.2.2: if the subject is found/enrolled proceed to checking the remarks satifactory
                else {
                    // #2.2.3: check the subject's remarks satisfactory
                    $isEnrolledSubjectRemarksSatisfied = $this->isSubjectRemarksSatisfied($enrolledSubjInfo);
                    // #2.2.4: if the enrolled subject's remarks are not satisfied. the subject will be added
                    // to the available subjects
                    if(!$isEnrolledSubjectRemarksSatisfied){ 
                        $availableSubjs[$curri_subj->subject_uid] = [
                            'subj_code' => $curri_subj->subject_code,
                            'subj_description' => $curri_subj->subject_desc,
                            'subj_units' => ($curri_subj->curricSubj_labUnits + $curri_subj->curricSubj_lecUnits),
                        ];
                    }
                }
            }
            // #2.1.2: if the subject is found/enrolled proceed to checking the remarks satifactory
            else {
                // #2.1.3: check the subject's remarks satisfactory
                $isEnrolledSubjectRemarksSatisfied = $this->isSubjectRemarksSatisfied($enrolledSubjInfo);
                // #2.1.4: if the enrolled subject's remarks are not satisfied. the subject will be added
                // to the available subjects
                if(!$isEnrolledSubjectRemarksSatisfied){ 
                    $availableSubjs[$curri_subj->subject_uid] = [
                        'subj_code' => $curri_subj->subject_code,
                        'subj_description' => $curri_subj->subject_desc,
                        'subj_units' => ($curri_subj->curricSubj_labUnits + $curri_subj->curricSubj_lecUnits),
                    ];
                }
            }

        }

        return $availableSubjs;
    }

    public function getCurriculumSubjects($curr_uid, $year_level, $sem){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        $query = $connection->query('SELECT * FROM curriculum_subjects,subjects
        WHERE subjects.subject_uid = curriculum_subjects.subject_uid
        AND subjects.subject_isActive = :active
        AND curriculum_subjects.curriculum_uid = :curr_uid
        AND curriculum_subjects.curricSubj_year = :year_level
        AND curriculum_subjects.curricSubj_sem = :semester',
        [
            ':active' => 'active',
            ':curr_uid' => $curr_uid,
            ':year_level' => $year_level,
            ':semester' => $sem
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

    public function getEnrolledSubjectInfoByCode($stud_uid, $subject_code){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        //query by subject code
        $query = $connection->query('SELECT * FROM subjects, students_subjects
        WHERE subjects.subject_uid = students_subjects.subject_uid
        AND students_subjects.student_uid = :stud_uid
        AND subjects.subject_code LIKE :subj_code', 
        [
            ':stud_uid' => $stud_uid,
            ':subj_code' => '"%'.$subject_code.'%"',
        ]);

        $result = $query->fetchAll();

        if(empty($result))

        Database::closeConnection();

        return $result;
    }

    public function getEnrolledSubjectInfoByDesc($stud_uid, $subject_desc){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        //query by subject code
        $query = $connection->query('SELECT * FROM subjects, students_subjects
        WHERE subjects.subject_uid = students_subjects.subject_uid
        AND students_subjects.student_uid = :stud_uid
        AND subjects.subject_desc LIKE :subj_desc', 
        [
            ':stud_uid' => $stud_uid,
            ':subj_desc' => '"%'.$subject_desc.'%"',
        ]);

        $result = $query->fetchAll();

        if(empty($result))

        Database::closeConnection();

        return $result;
    }

    public function isSubjectPrereqSatisfied($stud_uid, $preRequi_uid){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();

        if($preRequi_uid == 'none'){
            return true;
        }
        else {
            $query = $connection->query('SELECT * FROM students_subjects
            WHERE student_uid = :stud_uid
            AND subject_uid = :subj_uid', 
            [
                ':stud_uid' => $stud_uid,
            ]);
    
            $result = $query->fetchAll();
    
            Database::closeConnection();
    
            return $this->isSubjectRemarksSatisfied($result);
        }

    }

    public function isSubjectRemarksSatisfied($subject){

        if($subject[0]->studSubj_finalRemarks == 'INC' || 
        $subject[0]->studSubj_finalRemarks == 'DRP' || 
        $subject[0]->studSubj_finalRemarks == 'DROP' || 
        $subject[0]->studSubj_finalRemarks > 3){
            return false;
        }
        else {
            return true;
        }
        
    }

}

?>