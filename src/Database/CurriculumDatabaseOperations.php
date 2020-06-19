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
                'subject_isActive' => $subject['isActive'] ? 'active' : 'inactive',
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

    public function getSubjectsByKeyword($keyword){
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

        $query = $connection->query("SELECT * 
        FROM subjects
        WHERE subject_desc LIKE :keyword
        OR subject_code LIKE :keyword", [
            ':keyword' => '%'.$keyword.'%'
        ]);

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

    /**
     * 
     */
    public function insertNewCurriculum($curri_info, $curr_subjs, $isLock){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();
        $transaction = $connection->startTransaction();

        // curriculum_uid	int(11) Auto Increment	
        // curriculum_no	varchar(40) NULL	
        // curriculum_isLock	varchar(40) NULL	
        // curriculum_yearCreated	varchar(40) NULL	
        // curriculum_schoolYearCreated	varchar(40) NULL	
        // curriculum_basis	varchar(60) NULL	
        // program_uid	int(11) NULL
        try {
            $resultCurriUID = $connection->insert('curriculums')
            ->fields([
                'curriculum_uid' => NULL,
                'curriculum_no' => $curri_info['curr_num'],
                'curriculum_isLock' => $isLock,
                'curriculum_yearCreated' => $curri_info['curr_yearCreated'],
                'curriculum_schoolYearCreated' => $curri_info['curr_schoolYear'],
                'curriculum_basis' => $curri_info['curr_basis'],
                'program_uid' => $curri_info['curr_program'],
            ])
            ->execute(); 
            
            return $resultCurriUID;

        } catch (DatabaseExceptionWrapper $e) {
            \Drupal::logger('type')->error($e->getMessage());
            $transaction->rollBack();
            return false;
        }
        
    }

    public function insertCurriculumSubjects($curri_uid, $curr_subjs){

        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();
        $transaction = $connection->startTransaction();
        
        /**
         * @var $years : array that holds all possible years in a curriculum
         * @var $sems : array that holds all possible semesters in a curriculum
         */
        $years = [
            'first-year','second-year',
            'third-year','fourth-year',
        ];
        
        $sems = [
            'first-sem', 'second-sem', 
            'summer-sem',
        ];

        try {
            
            // Parsing the data structure of created curriculum
            // iterate through years array
            foreach($years as $year){
                
                // iterate through semesters array
                foreach($sems as $sem){
                    
                    // get the data structure of subjects in a year and semester
                    $curri_subj = $curr_subjs[$year.$sem];
                    
                    // loop through the array and save all the infos in database
                    for($i = 1; $i <= count($curri_subj); $i++){
                        // Perform a regular expression match
                        preg_match('/(?P<digit>\d+)/', $curri_subj[$i]['subj_code_autoComplete'], $subj_code);
                        preg_match('/(?P<digit>\d+)/', $curri_subj[$i]['subj_prerequisite'], $subj_prerequi1);
                        preg_match('/(?P<digit>\d+)/', $curri_subj[$i]['subj_corequisite'], $subj_prerequi2);

                        // $subj_code = substr($curri_subj[$i]['subj_code_autoComplete'], 0, 1);
                        $subj_labUnits = $curri_subj[$i]['number-container']['lab_units'];
                        $subj_lecUnits = $curri_subj[$i]['number-container']['lec_units'];
                        $subj_labHours = $curri_subj[$i]['number-container']['lab_hours'];
                        $subj_lecHours = $curri_subj[$i]['number-container']['lect_hours'];
                        // $subj_prerequi1 = substr($curri_subj[$i]['subj_prerequisite'], 0, 1);
                        // $subj_prerequi2 = substr($curri_subj[$i]['subj_corequisite'], 0, 1);
                        
                        // curriculum_subjects attributes:
                        // curricSubj_uid	int(11) Auto Increment	
                        // curriculum_uid	int(11) NULL	
                        // subject_uid	int(11) NULL	
                        // curricSubj_prerequisite1	varchar(40) NULL	
                        // curricSubj_prerequisite2	varchar(40) NULL	
                        // curricSubj_labUnits	int(11) NULL	
                        // curricSubj_lecUnits	int(11) NULL	
                        // curricSubj_labHours	int(11) NULL	
                        // curricSubj_lecHours	int(11) NULL	
                        // curricSubj_year	varchar(40) NULL	
                        // curricSubj_sem	varchar(40) NULL

                        if(!empty($subj_code)){
                            $insertedSubjUID = $connection->insert('curriculum_subjects')
                            ->fields([
                                'curricSubj_uid' => NULL,
                                'curriculum_uid' => $curri_uid,
                                'subject_uid' => $subj_code[0],
                                'curricSubj_prerequisite1' => empty($subj_prerequi1[0]) ? 'none' : $subj_prerequi1[0],
                                'curricSubj_prerequisite2' => empty($subj_prerequi2[0]) ? 'none' : $subj_prerequi2[0],
                                'curricSubj_labUnits' => empty($subj_labUnits) ? 0 : $subj_labUnits,
                                'curricSubj_lecUnits' => empty($subj_lecUnits) ? 0 : $subj_lecUnits,
                                'curricSubj_labHours' => empty($subj_labHours) ? 0 : $subj_labHours,
                                'curricSubj_lecHours' => empty($subj_lecHours) ? 0 : $subj_lecHours,
                                'curricSubj_year' => $year,
                                'curricSubj_sem' => $sem,
                            ])
                            ->execute();
                        }

                    }
            
                }    
        
            }


            return true;
        } catch (DatabaseExceptionWrapper $e) {
            \Drupal::logger('type')->error($e->getMessage());
            $transaction->rollBack();
            return false;
        }

    }

    public function insertCurriculumElectiveSubjects($curri_uid, $curr_subjs){
        //setting up test_drupal_data database into active connection
        Database::setActiveConnection('test_drupal_data');
        // get the active connection and put into an object
        $connection = Database::getConnection();
        $transaction = $connection->startTransaction();

        try {
            
            // Parsing the data structure of created curriculum
            // get the elective subjects
            $elect_subjs = $curr_subjs['electives'];

            // loop through the array and save the elective subject infos in database
            for($i = 1; $i < count($elect_subjs); $i++){

                // Perform a regular expression match
                preg_match('/(?P<digit>\d+)/', $elect_subjs[$i]['subj_code_autoComplete'], $elec_subj_code);
                preg_match('/(?P<digit>\d+)/', $elect_subjs[$i]['subj_prerequisite'], $elec_subj_prerequi1);
                preg_match('/(?P<digit>\d+)/', $elect_subjs[$i]['subj_corequisite'], $elec_subj_prerequi2);
                // $elec_subj_code = substr($elect_subjs[$i]['subj_code_autoComplete'], 0, 1);
                $elect_subj_labUnits = $elect_subjs[$i]['number-container']['lab_units'];
                $elect_subj_lecUnits = $elect_subjs[$i]['number-container']['lec_units'];
                $elect_subj_labHours = $elect_subjs[$i]['number-container']['lab_hours'];
                $elect_subj_lecHours = $elect_subjs[$i]['number-container']['lect_hours'];
                // $elec_subj_prerequi1 = substr($elect_subjs[$i]['subj_prerequisite'], 0, 1);
                // $elec_subj_prerequi2 = substr($elect_subjs[$i]['subj_corequisite'], 0, 1);

                // curricElect_uid	int(11) Auto Increment	
                // curriculum_uid	int(11) NULL	
                // electiveSubj_uid	int(11) NULL	
                // electiveSubj_labUnits	int(11) NULL	
                // electiveSubj_lecUnits	int(11) NULL	
                // electiveSubj_labHours	int(11) NULL	
                // electiveSubj_lecHours	int(11) NULL	
                // electiveSubj_prerequisite1	varchar(40) NULL	
                // electiveSubj_prerequisite2	varchar(40) NULL	

                if(!empty($elec_subj_code)){
                    $insertedElectiveSubjUID = $connection->insert('curriculum_electives')
                    ->fields([
                        'curricElect_uid' => NULL,
                        'curriculum_uid' => $curri_uid,
                        'electiveSubj_uid' => $elec_subj_code[0],
                        'electiveSubj_labUnits' => empty($elect_subj_labUnits) ? 0 : $elect_subj_labUnits,
                        'electiveSubj_lecUnits' => empty($elect_subj_lecUnits) ? 0 : $elect_subj_lecUnits,
                        'electiveSubj_labHours' => empty($elect_subj_labHours) ? 0 : $elect_subj_labHours,
                        'electiveSubj_lecHours' => empty($elect_subj_lecHours) ? 0 : $elect_subj_lecHours,
                        'electiveSubj_prerequisite1' => empty($elec_subj_prerequi1[0]) ? 'none' : $elec_subj_prerequi1[0],
                        'electiveSubj_prerequisite2' => empty($elec_subj_prerequi2[0]) ? 'none' : $elec_subj_prerequi2[0],
                    ])
                    ->execute();
                    
                    if(empty($insertedElectiveSubjUID)){

                    }
                }

            }

            return true;
        } catch (DatabaseExceptionWrapper $e) {
            \Drupal::logger('type')->error($e->getMessage());
            $transaction->rollBack();
            return false;
        }

    }

    public function getSubjectInfoByCode($subj_code){
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
        FROM subjects, subjects_category, colleges
        WHERE subjects.subjCat_uid = subjects_category.subjCat_uid
        AND subjects.college_uid = colleges.college_uid
        AND subjects.subject_code LIKE :subj_code',
        [
            ':subj_code' => '%'. ucwords($subj_code) .'%',
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    public function getSubjectInfoByDesc($subj_desc){
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

        $query = $connection->query("SELECT *
        FROM subjects, subjects_category, colleges
        WHERE subjects.subjCat_uid = subjects_category.subjCat_uid
        AND subjects.college_uid = colleges.college_uid
        AND subjects.subject_desc LIKE :subj_desc", 
        [
            ':subj_desc' => '%'.ucwords($subj_desc).'%',
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    public function getSubjectInfoByUID($subj_uid){
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
        FROM subjects
        WHERE subject_uid = :subject_uid',
        [
            ':subject_uid' => $subj_uid,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    // public function getCurriculumInfo($curri_info){
    //     //setting up test_drupal_data database into active connection
    //     Database::setActiveConnection('test_drupal_data');
    //     // get the active connection and put into an object
    //     $connection = Database::getConnection();

    //     /**
    //      * Example Query
    //      * $query = $database->query("SELECT id, example FROM {mytable} WHERE created > :created", [
    //      *      ':created' => REQUEST_TIME - 3600,
    //      *    ]);
    //      */

    //     $query = $connection->query("SELECT *
    //     FROM curriculums
    //     WHERE curriculum_no = :curri_num
    //     AND program_uid = :program_uid", 
    //     [
    //         ':curri_num' => $curri_info['curri_num'],
    //         ':program_uid' => $curri_info['program']
    //     ]);

    //     $result = $query->fetchAll();

    //     Database::closeConnection();

    //     return $result;
    // }

    public function getCurriculumSubjects($year, $sem, $curri_uid){
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

        $query = $connection->query("SELECT *
        FROM curriculum_subjects, subjects
        WHERE curriculum_subjects.subject_uid = subjects.subject_uid
        AND curriculum_subjects.curriculum_uid = :curri_uid
        AND curriculum_subjects.curricSubj_year = :year
        AND curriculum_subjects.curricSubj_sem = :sem", 
        [
            ':curri_uid' => $curri_uid,
            ':year' => $year,
            ':sem' => $sem
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;
    }

    public function getCurriculumElectiveSubjects($curri_uid){
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

        $query = $connection->query("SELECT *
        FROM curriculum_electives, subjects
        WHERE curriculum_electives.electiveSubj_uid = subjects.subject_uid
        AND curriculum_electives.curriculum_uid = :curri_uid", 
        [
            ':curri_uid' => $curri_uid,
        ]);

        $result = $query->fetchAll();

        Database::closeConnection();

        return $result;

    }

}

?>