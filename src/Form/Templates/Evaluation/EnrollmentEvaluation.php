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

use Drupal\sedm\Database\DatabaseOperations;

class EnrollmentEvaluation extends FormBase {

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
            '#type' => 'fieldset',
            '#title' => 'Student Info.'
        ];

        $form['form-container']['student']['notice-container'] = [
            '#type' => 'container',
        ];

        $form['form-container']['student']['details-container'] = [
            '#type' => 'container'
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

        $form['form-container']['student']['details-container']['select_container']['yearLevel'] = [
            '#type' => 'select',
            '#title' => 'Year Level',
            '#options' => [
                '1' => 'First Year',
                '2' => 'Second Year',
                '3' => 'Third Year',
                '4' => 'Fourth Year',
                '5' => 'Fifth Year',
            ],
            '#attributes' => [
                'class' => ['flat-element',],
            ],
        ];

        $form['form-container']['student']['details-container']['select_container']['semester'] = [
            '#type' => 'select',
            '#title' => 'Select Semester',
            '#options' => [
                '1' => 'First Semester',
                '2' => 'Second Semester',
                '3' => 'Summer',
            ],
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

        $form['form-container']['subject-table-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="subj-table-container-wrapper">',
            '#suffix' => '</div>',
        ];

        return $form;
    }

    /**
     * @function searchAvailableSubjects : this will fetch the query response 
     * template
     */
    public function searchAvailableSubjects(array &$form, FormStateInterface $form_state){


        if($form_state->getErrors()){

            $form['form-container']['student']
            ['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];
        }
            // this condition will append the subjects table
        else{

            $form['form-container']
            ['subject-table-container']['subjectsAvailable'] = [
                '#type' => 'details',
                '#title' => $this->t('Advisable Subjects'),
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
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="subjectsAvailableBody">
    
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