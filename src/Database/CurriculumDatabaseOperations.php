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

        // curriculum table fields:
        // curriculum_uid	int(11) Auto Increment	
        // curriculum_no	varchar(40) NULL	
        // curriculum_isLock	varchar(40) NULL	
        // curriculum_yearCreated	varchar(40) NULL	
        // curriculum_schoolYearCreated	varchar(40) NULL	
        // curriculum_semCreated	varchar(40) NULL	
        // program_uid	int(11) NULL
        try {
            $resultCurriUID = $connection->insert('curriculums')
            ->fields([
                'curriculum_uid' => NULL,
                'curriculum_no' => $curri_info['curr_num'],
                'curriculum_isLock' => $isLock,
                'curriculum_yearCreated' => $curri_info['curr_yearCreated'],
                'curriculum_schoolYearCreated' => $curri_info['curr_schoolYear'],
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
                        $subj_code = $curri_subj[$i]['subj_code'];
                        $subj_labUnits = $curri_subj[$i]['number-container']['lab_units'];
                        $subj_lecUnits = $curri_subj[$i]['number-container']['lec_units'];
                        $subj_labHours = $curri_subj[$i]['number-container']['lab_hours'];
                        $subj_lecHours = $curri_subj[$i]['number-container']['lect_hours'];
                        $subj_prerequi1 = $curri_subj[$i]['subj_prerequi_1'];
                        $subj_prerequi2 = $curri_subj[$i]['subj_prerequi_2'];
                        
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

                        if($subj_code != 'none'){
                            $insertedSubjUID = $connection->insert('curriculum_subjects')
                            ->fields([
                                'curricSubj_uid' => NULL,
                                'curriculum_uid' => $curri_uid,
                                'subject_uid' => $subj_code,
                                'curricSubj_prerequisite1' => $subj_prerequi1,
                                'curricSubj_prerequisite2' => $subj_prerequi2,
                                'curricSubj_labUnits' => $subj_labUnits,
                                'curricSubj_lecUnits' => $subj_lecUnits,
                                'curricSubj_labHours' => $subj_labHours,
                                'curricSubj_lecHours' => $subj_lecHours,
                                'curricSubj_year' => $year,
                                'curricSubj_sem' => $sem,
                            ])
                            ->execute();
                        }

                    }
            
                }    
        
            }

            // get the elective subjects
            $elect_subjs = $curr_subjs['electives'];

            // loop through the array and save the elective subject infos in database
            for($i = 1; $i < count($elect_subjs); $i++){
                $elec_subj_code = $elect_subjs[$i]['subj_code'];
                $elec_subj_prerequi1 = $elect_subjs[$i]['subj_prerequi_1'];
                $elec_subj_prerequi2 = $elect_subjs[$i]['subj_prerequi_2'];

                // curriculum_electives attributes:
                // curricElect_uid	int(11) Auto Increment	
                // curriculum_uid	int(11) NULL	
                // electiveSubj_uid	int(11) NULL	
                // electiveSubj_prerequisite1	varchar(40) NULL	
                // electiveSubj_prerequisite2	varchar(40) NULL

                if($elec_subj_code != 'none'){
                    $insertedElectiveSubjUID = $connection->insert('curriculum_electives')
                    ->fields([
                        'curricElect_uid' => NULL,
                        'curriculum_uid' => $curri_uid,
                        'electiveSubj_uid' => $elec_subj_code,
                        'electiveSubj_prerequisite1' => $elec_subj_prerequi1,
                        'electiveSubj_prerequisite2' => $elec_subj_prerequi2,
                    ])
                    ->execute();
                }

            }

            return true;
        } catch (DatabaseExceptionWrapper $e) {
            \Drupal::logger('type')->error($e->getMessage());
            $transaction->rollBack();
            return false;
        }

    }

    

}

?>