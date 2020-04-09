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

        $form['form-container']['subject-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Subject Info.'
        ];

        $form['form-container']['subject-details-container']['college-container'] = [
            '#type' => 'container'
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

        $form['form-container']['subject-details-container']['college-container']['college-select'] = [
            '#type' => 'select',
            '#title' => $this->t('Select College'),
            '#options' => $collegeOpt,
            '#empty_option' => 'Select College',
            '#attributes' => [
                'class' => ['flat-element',],
            ],
            '#ajax' => [
                'callback' => '::displayActiveSubjects',
                'wrapper' => 'active-subjects-form-container-wrapper',
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

        // get the value of selected college

        $college = $form_state->getValue([
            'form-container','subject-details-container',
            'college-container','college-select',
          ]);

        if(!empty($college)){

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
            $activeSubjects = $EDO->getActiveSubjects($college);
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
        }

        return $form['form-container'];

    }

    
        /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

        /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }

}

?>