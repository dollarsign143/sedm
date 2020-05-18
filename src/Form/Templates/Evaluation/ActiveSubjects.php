<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\EvaluationDatabaseOperations;

class ActiveSubjects extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_active_subjects';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="active-subjects-form-container-wrapper">',
            '#suffix' => '</div>',
          ];
        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Active Subjects</h2>'),
        ];

        $form['form-container']['notice-container'] = [
            '#type' => 'container',
        ];

        $form['form-container']['subject-info-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Subject Info.'
        ];

        /**
         * @Variable $dbOperations = object to hold DatabaseOperations class
         * @Variable $colleges = object to hold the result of the query
         * @Variable array $collegeOpt : holds the custom layout of every college
         *      for select render element
         */
        $EDO = new EvaluationDatabaseOperations(); // instantiate EvaluationDatabaseOperations Class
        $colleges = $EDO->getColleges(); // get colleges
        $collegeOpt = array();

        foreach ($colleges as $college) {

            $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;

        }

        $form['form-container']['subject-info-container']['college-select'] = [
            '#type' => 'select',
            '#title' => $this->t('Select College'),
            '#options' => $collegeOpt,
            '#empty_option' => 'Select College',
            '#attributes' => [
                'class' => ['flat-element',],
            ],
        ];

        $form['form-container']['subject-info-container']['subject-keyword'] = [
            '#type' => 'textfield',
            '#title' => 'Subject Code or Description',
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => $this->t('Ex. Math 1.7 or College Algebra'),
            ],
        ];

        $form['form-container']['subject-info-container']['search'] = [
            '#type' => 'submit',
            '#value' => 'Search',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::displayActiveSubjects',
                'wrapper' => 'active-subjects-form-container-wrapper',
                'event' => 'click',
            ],
        ];

        $form['form-container']['subject-details-container']['subjects-table-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="active-subjects-department-container-wrapper">',
            '#suffix' => '</div>',
        ];

        return $form;

    }

    public function displayActiveSubjects(array &$form, FormStateInterface $form_state){

        if($form_state->getErrors()){

            $form['form-container']['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];

            return $form['form-container'];
        }
        else {
            $college = $form_state->getValue([
                'form-container', 'subject-info-container', 'college-select',]);
            $keyword = $form_state->getValue([
                'form-container', 'subject-info-container', 'subject-keyword']);
    
            $form['form-container']['subject-details-container']
            ['subjects-table-container']['subjects-table'] = [
                '#type' => 'details',
                '#title' => $this->t('Active Subjects'),
                '#open' => TRUE,
            ];
        
            $form['form-container']['subject-details-container']
            ['subjects-table-container']['subjects-table']['description'] = [
                '#type' => 'item',
                '#markup' => $this->t('The subjects listed below are active an can be enrolled'),
            ];
        
            $EDO = new EvaluationDatabaseOperations(); // instantiate EvaluationDatabaseOperations Class
            $data = NULL;
            if(empty($keyword)){
                $activeSubjects = $EDO->getActiveSubjects($college);
            }
            else {
                $activeSubjects = $EDO->getActiveSubjectsByCode($college, $keyword);
            }
            
            if(empty($activeSubjects)){
                $activeSubjects = $EDO->getActiveSubjectsByDesc($college, $keyword);
                if(empty($activeSubjects)){
                    $data = $this->t(
                        '<tr>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                        </tr>'
                    );
                }
                else {
                    foreach($activeSubjects as $activeSubject){
                        $data .= $this->t(
                            '<tr>
                            <td>'.$activeSubject->subject_code.'</td>
                            <td>'.$activeSubject->subject_desc .'</td>
                            <td>'.($activeSubject->curricSubj_labUnits + $activeSubject->curricSubj_lecUnits).'</td>
                            <td>'.($activeSubject->curricSubj_labHours + $activeSubject->curricSubj_lecHours).'</td>
                            <td>'.$activeSubject->program_abbrev.'</td>
                            </tr>'
                        );
                    }
                }
                
            }
            else {
                foreach($activeSubjects as $activeSubject){
                    $data .= $this->t(
                        '<tr>
                        <td>'.$activeSubject->subject_code.'</td>
                        <td>'.$activeSubject->subject_desc .'</td>
                        <td>'.($activeSubject->curricSubj_labUnits + $activeSubject->curricSubj_lecUnits).'</td>
                        <td>'.($activeSubject->curricSubj_labHours + $activeSubject->curricSubj_lecHours).'</td>
                        <td>'.$activeSubject->program_abbrev.'</td>
                        </tr>'
                    );
                }
            }

            $form['form-container']['subject-details-container']
            ['subjects-table-container']['subjects-table']['table'] = [
                '#type' => 'markup',
                '#markup' => $this->t('
                <div>
                    <table>
                    <thead>
                        <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Hours/per Week</th>
                        <th>Program</th>
                        </tr>
                    </thead>
                    <tbody class="subjectsAvailableBody">
                    '.$data.'
                    </tbody>
                    </table>
                </div>'),
            ];
            
    
            return $form['form-container'];
        }

    }

    
        /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        $college = $form_state->getValue([
            'form-container', 'subject-info-container', 'college-select',]);
        $keyword = $form_state->getValue([
            'form-container', 'subject-info-container', 'subject-keyword']);

        if(empty($college)){
            $form_state->setErrorByName('college-select', $this->t("Please select a college!"));
        }
    }

        /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }

}

?>