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

        $form['form-container']['subject-table-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="subj-table-container-wrapper">',
            '#suffix' => '</div>',
            '#weight' => 3,
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

            $data['id_number'] = $form_state->getValue(['form-container','student','details-container','idNumber']);
            $data['year_level'] = $form_state->getValue(['form-container','student','details-container','select_container','yearLevel']);
            $data['semester'] = $form_state->getValue(['form-container','student','details-container','select_container','semester']);
            
            $EDO = new EvaluationDatabaseOperations();
            $stud_info = $EDO->getStudentInfo($data['id_number']);
            $curri_uid = $stud_info[0]->curriculum_uid;
            $availableSubjects = $EDO->getAvailableSubjects($data, $curri_uid);

            $data = NULL;
            
            foreach($availableSubjects as $availableSubject => $key){
                $data .= '<tr>
                    <td>'.$key['subj_code'].'</td>
                    <td>'.$key['subj_description'].'</td>
                    <td>'.$key['subj_units'].'</td>
                </tr>';
            }

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
                '#value' => ucwords($stud_info[0]->studProf_lname),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['first-name'] = [
                '#type' => 'textfield',
                '#title' => $this->t('First Name'),
                '#value' => ucwords($stud_info[0]->studProf_fname),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['middle-name'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Middle Name'),
                '#value' => ucwords($stud_info[0]->studProf_mname),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['age'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Age'),
                '#value' => ucwords($stud_info[0]->studProf_age),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['gender'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Gender'),
                '#value' => ucwords($stud_info[0]->studProf_gender),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['college'] = [
                '#type' => 'textfield',
                '#title' => $this->t('College'),
                '#value' => ucwords($stud_info[0]->college_abbrev),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['year'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Year Level'),
                '#value' => ucwords($stud_info[0]->student_yearLevel),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']['student-info-fieldset']['stud-info-container']['program'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Program'),
                '#value' => ucwords($stud_info[0]->program_abbrev),
                '#attributes' => [
                    'class' => ['flat-input',],
                    'disabled' => TRUE,
                ],
            ];

            $form['form-container']
            ['subject-table-container']['subjectsAvailable'] = [
                '#type' => 'details',
                '#title' => $this->t('Available Subjects'),
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