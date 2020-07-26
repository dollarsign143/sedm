<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\EvaluationDatabaseOperations;

class EnrollmentEvaluation extends FormBase {
    use LoggerChannelTrait;

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_enrollment_evaluation';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="enrollment-eval-form-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Enrollment Evaluation</h2>'),
        ];

        $form['form-container']['student'] = [
            '#type' => 'details',
            '#title' => 'Fill out student info.',
            '#open' => TRUE,
        ];

        $form['form-container']['student']['notice-container'] = [
            '#type' => 'container',
        ];

        $form['form-container']['student']['details-container'] = [
            '#type' => 'container',
            '#weight' => 1
        ];

        $form['form-container']['student']['details-container']['idNumber'] = [
            '#type' => 'textfield',
            '#title' => 'Id number',
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => $this->t('2015-0001'),
            ],
        ];

        $form['form-container']['student']['details-container']['select_container'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['inline-container-col2',],
            ],
        ];

        $years = [
            'first-year' => 'First Year',
            'second-year' => 'Second Year',
            'third-year' => 'Third Year',
            'fourth-year' => 'Fourth Year'
        ];
        $form['form-container']['student']['details-container']['select_container']['yearLevel'] = [
            '#type' => 'select',
            '#title' => 'Year Level',
            '#options' => $years,
            '#attributes' => [
                'class' => ['flat-element',],
            ],
        ];

        $semesters = [
            'first-sem' => 'First Semester',
            'second-sem' => 'Second Semester',
            'summer-sem' => 'Summer'
        ];
        $form['form-container']['student']['details-container']['select_container']['semester'] = [
            '#type' => 'select',
            '#title' => 'Select Semester',
            '#options' => $semesters,
            '#attributes' => [
                'class' => ['flat-element',],
            ],
        ];

        $form['form-container']['student']['details-container']['evaluate'] = [
            '#type' => 'submit',
            '#value' => 'Evaluate',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::searchAvailableSubjects',
                'wrapper' => 'enrollment-eval-form-container-wrapper', 
                'event' => 'click',
            ]
        ];

        $form['form-container']['student-info-fieldset'] = [
            '#type' => 'fieldset',
            '#title' => 'Student Info.',
            '#weight' => 2
        ];

        $form['form-container']['student-info-fieldset']['stud-info-container'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['inline-container-col3',],
            ],
        ];

        $form['form-container']['student-info-fieldset']['stud-info-container']['last-name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Last Name'),
            '#value' => 'Last Name',
            '#attributes' => [
                'class' => ['flat-input',],
                'disabled' => TRUE,
            ],
        ];

        $form['form-container']['subject-table-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="subj-table-container-wrapper">',
            '#suffix' => '</div>',
            '#weight' => 3,
        ];

        $form['form-container']
        ['subject-table-container']['subjectsAvailable'] = [
            '#type' => 'details',
            '#title' => $this->t('Regular Subjects (No Issues)'),
            '#open' => TRUE,
        ];

        $form['form-container']
        ['subject-table-container']['subjectsAvailable']['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('The subjects listed below are advisable to enroll.'),
        ];

        $form['form-container']
        ['subject-table-container']['subjectsAvailable']['table'] = [
            '#type' => 'markup',
            '#markup' => $this->t('
            <div>
                <table>
                <thead>
                    <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Units</th>
                    <th>Grade</th>
                    <th>Pre-Requisite</th>
                    <th>Grade</th>
                    <th>Co-Requisite</th>
                    <th>Grade</th>
                    <th>Status</th>
                    </tr>
                </thead>
                <tbody class="subjectsNoIssuesBody">
                '.$available['available'].'
                <tr>
                    <td colspan="7"></td>
                    <td>Max Load Units</td>
                    <td>'.$availableSubjects['regularSubjs']['totalMaxUnits'].'</td>
                </tr>
                <tr>
                    <td colspan="7"></td>
                    <td>Total Aquired Units</td>
                    <td>'.$available['totalUnits'].'</td>
                </tr>
                </tbody>
                </table>
            </div>'),
        ];


        return $form;
    }

    /**
     * @function searchAvailableSubjects : this will fetch the query response 
     * template
     */
    public function searchAvailableSubjects(array &$form, FormStateInterface $form_state){

        $logger = $this->getLogger('sedm');
        $form['form-container']['student-info-fieldset']['stud-info-container']['last-name']['#value'] = 'Caton';
            
        return $form['form-container'];
    }

    public function updateTableCallback(array &$form, FormStateInterface $form_state){
        return $form['form-container'];
    }




    protected function getNoIssueAvailableSubjects($subjects){
        // var_dump($subjects);
        $available = "";
        $unitsAcquired = 0;
        $result = array();
        if(empty($subjects)){
            $available .= '<tr>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
            </tr>';
        }
        else {
            foreach($subjects as $subject => $key){
                // var_dump($key);
                if($key['reason'] == "OK" || $key['reason'] == "NOT_ENROLLED" ) {
                    $available .= '<tr>
                        <td>'.$key['subj_code'].'</td>
                        <td>'.$key['subj_description'].'</td>
                        <td>'.$key['subj_units'].'</td>
                        <td>'.$key['subj_grade'].'</td>
                        <td>'.$key['prerequi1'].'</td> 
                        <td>'.$key['prerequi1remarks'].'</td>
                        <td>'.$key['prerequi2'].'</td>
                        <td>'.$key['prerequi2remarks'].'</td>
                        <td>Requisites Complied</td>
                    </tr>';
                    $unitsAcquired += $key['subj_units'];
                }
            }
        }

        $result['available'] = $available;
        $result['totalUnits'] = $unitsAcquired;

        return $result;

    }

    protected function getWithIssueAvailableSubjects($subjects){
        $nonAvailable = "";
        $result = array();
        if(empty($subjects)){
            $nonAvailable .= '<tr>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
            </tr>';
        }
        else {
            foreach($subjects as $subject => $key){
                // var_dump($key);
                
                if($key['reason'] == "ISSUES"){
                    
                    $nonAvailable .= '<tr style="background-color: orange; font-weight: bold" >
                        <td>'.$key['subj_code'].'</td>
                        <td>'.$key['subj_description'].'</td>
                        <td>'.$key['subj_units'].'</td>
                        <td>'.$key['prerequi1'].'</td> 
                        <td>'.$key['prerequi1remarks'].'</td>
                        <td>'.$key['prerequi2'].'</td>
                        <td>'.$key['prerequi2remarks'].'</td>
                        <td>Requisites Issue</td>
                    </tr>';
                  
                }
            }
        }

        return $nonAvailable;

    }

    protected function getAlternativeSubjects($subjects){
        $alternatives = "";
        
        if(empty($subjects)){
            $alternatives .= '<tr>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
            </tr>';
        }
        else {
                foreach($subjects as $subject => $key){
                // var_dump($key);
                
                if($key['reason'] == "OK" || $key['reason'] == "INCOMPLETE" || 
                    $key['reason'] == "FAILED" ) {
                    $alternatives .= '<tr>
                        <td>'.$key['subj_code'].'</td>
                        <td>'.$key['subj_description'].'</td>
                        <td>'.$key['subj_units'].'</td>
                        <td>'.$key['subj_grade'].'</td>
                        <td>'.$key['prerequi1'].'</td> 
                        <td>'.$key['prerequi1remarks'].'</td>
                        <td>'.$key['prerequi2'].'</td>
                        <td>'.$key['prerequi2remarks'].'</td>
                        <td>Requisites Complied</td>
                    </tr>';
                }

            }
        }

        return $alternatives;
    }

    protected function getAdditionalSubject($withIssues, $alternatives){

        $additionalSubjects = [];
        foreach($alternatives as $alternative => $key){
            // var_dump($key);
            
            if($key['reason'] == "OK" || $key['reason'] == "INCOMPLETE" || 
                $key['reason'] == "FAILED" ) {
                $additionalSubjects[] = [
                    $key['subj_uid'] => $key['subj_code'].' - '.$key['subj_description']
                ];
            }

        }

        foreach($withIssues as $withIssue => $key){
            // var_dump($key);
            
            if($key['reason'] == "ISSUES") {
                $additionalSubjects[] = [
                    $key['subj_uid'] => $key['subj_code'].' - '.$key['subj_description']
                ];
            }

        }

        return $additionalSubjects;
    }

    public function verifyAddingSubject(array &$form, FormStateInterface $form_state){
        
        $response = new AjaxResponse();
        $selectedSubj = $form_state->getValue(['form-container','subject-table-container','subjectsAvailable','selectSubject']);
        // var_dump($selectedSubj);
        $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifyCurriculumToSaveModalForm');
        $command = new OpenDialogCommand($this->t('Add subject'), $modal_form, ['width' => '50%']);

        $response->addCommand($command);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        $idNumber = $form_state->getValue(['form-container','student','details-container','idNumber']);

        if(empty($idNumber)){
            $form_state->setError($form, $this->t('ID number is empty!'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        
    }
    
}

?>