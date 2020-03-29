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

    /**
     * 
     */
    public function insertNewCurriculum($curri_info, $curr_subjs, $isLock = false){
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
            $result = $connection->insert('curriculums')
            ->fields([
                'curriculum_uid' => NULL,
                'curriculum_no' => $curri_info['curr_num'],
                'curriculum_isLock' => $isLock,
                'curriculum_yearCreated' => $curri_info['curr_yearCreated'],
                'curriculum_schoolYearCreated' => $curri_info['curr_schoolYear'],
                'program_uid' => $curri_info['curr_program'],
            ])
            ->execute();
            
            $isCurriSubjectsInserted = $this
            ->insertCurriculumSubjects($curri_info['curr_program'], $curri_info['curr_num'], $curr_subjs);
            
            if($isCurriSubjectsInserted){
                return true;
            }
            else {
                $transaction->rollBack();
                return false;
            }
            

        } catch (DatabaseExceptionWrapper $e) {
            \Drupal::logger('type')->error($e->getMessage());
            $transaction->rollBack();
            return false;
        }

        $connection->commit();
        
    }

    public function insertCurriculumSubjects($programUID, $curri_num, $curr_subjs){

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

        /**
         * @var $curri_uid : variable to hold the result of getCurriculumInfo method
         * @method getCurriculumInfo($programUID, $curri_num) : returns the id of the curriculum
         * @param $programUID : program unique id of a college program
         * @param $curri_num : curriculum number
         * method in DatabaseOperations parent class
         */
        $curri_uid = $this->getCurriculumInfo($programUID, $curri_num);

        // check if the curriculum is existing
        // if returns NULL this method will return false
        if($curri_uid == NULL){
            return false;
        }
        else {
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
                        var_dump($subj_description);
                    }
            
                }    
        
            }

            // get the elective subjects
            $elect_subjs = $curr_subjs['electives'];

            // loop through the array and save the elective subject infos in database
            for($i = 1; $i <= count($elect_subjs); $i++){
                $elec_subj_code = $elect_subjs[$i]['subj_code'];
                $elec_subj_prerequi1 = $elect_subjs[$i]['subj_prerequi_1'];
                $elec_subj_prerequi2 = $elect_subjs[$i]['subj_prerequi_2'];
            }

            return true;

        }

    }

    

}

?>