<?php

namespace Drupal\sedm\Form\Templates\Curriculum\SubjectsTab;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\CurriculumDatabaseOperations;

class SearchSubjectForm extends FormBase {
    use LoggerChannelTrait;

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_curriculum_menu_search_subject';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="search-subject-form-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Search Subject</h2>'),
        ];

        $form['form-container']['subject-info'] = [
            '#type' => 'details',
            '#title' => 'Fill out subject info.',
            '#open' => TRUE,
        ];

        $form['form-container']['subject-info']['notice-container'] = [
            '#type' => 'container',
        ];

        $form['form-container']['subject-info']['details-container'] = [
            '#type' => 'container',
            '#weight' => 1
        ];

        $form['form-container']['subject-info']['details-container']['subj_details'] = [
            '#type' => 'textfield',
            '#title' => 'Enter Subject Code or Description',
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => $this->t('Ex. Math 1.7 or College Algebra'),
            ],
        ];


        $form['form-container']['subject-info']['details-container']['search'] = [
            '#type' => 'submit',
            '#value' => 'Search',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::searchSubject',
                'wrapper' => 'search-subject-form-container-wrapper', 
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

        $logger = $this->getLogger('sedm');
        
        if($form_state->getErrors()){

            $form['form-container']['student']
            ['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];

            return $form['form-container'];
        }
            // this condition will append the subjects table
        else{

            $info['id_number'] = $form_state->getValue(['form-container','student','details-container','idNumber']);
            $info['year_level'] = $form_state->getValue(['form-container','student','details-container','select_container','yearLevel']);
            $info['semester'] = $form_state->getValue(['form-container','student','details-container','select_container','semester']);
            
            $EDO = new EvaluationDatabaseOperations();
            $stud_info = $EDO->getStudentInfo($info['id_number']);
            
            if(empty($stud_info)){
                $response = new AjaxResponse();
                $content['form-container']['notice-container']['message'] = [
                    '#type' => 'item',
                    '#markup' => $this->t('Can\'t find the ID number!'),
                ];
                $command = new OpenModalDialogCommand($this->t('Error!'), $content, ['width' => '50%',]);

                $response->addCommand($command);
            
                return $response;

            }
            else {
                $curri_uid = $stud_info[0]->curriculum_uid;
                $availableSubjects = $EDO->getAvailableSubjects($info, $curri_uid);
    
                $data = NULL;
                if(empty($availableSubjects)){
                    $data .= '<tr>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                    </tr>';
                }
                else {
                    foreach($availableSubjects as $availableSubject => $key){
                        $data .= '<tr>
                            <td>'.$key['subj_code'].'</td>
                            <td>'.$key['subj_description'].'</td>
                            <td>'.$key['subj_units'].'</td>
                        </tr>';
                    }
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
            
                return $form['form-container'];
            }
        }

    }

    public function searchSubject(array &$form, FormStateInterface $form_state){
        
        if($form_state->getErrors()){

            $form['form-container']['subject-info']
            ['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];

            return $form['form-container'];
        }
        else {

            $subject = $form_state->getValue(['form-container','subject-info','details-container','subj_details']);
            $CDO = new CurriculumDatabaseOperations();

            $subj_info = $CDO->getSubjectInfoByCode($subject);

            $data = NULL;
            if(empty($subj_info)){
                $subj_info = $CDO->getSubjectInfoByDesc($subject);
                if(empty($subj_info)){
                    $data .= $this->t('<tr>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                        <td>NONE</td>
                    </tr>');
                }
                else {
                    foreach($subj_info as $subject){
                        $data .= $this->t('<tr>
                            <td>'.$subject->subject_code.'</td>
                            <td>'.$subject->subject_desc.'</td>
                            <td>'.$subject->college_abbrev.'</td>
                            <td>'.$subject->subjCat_name.'</td>
                            <td>'.$subject->subject_isActive.'</td>
                        </tr>');
                    }
                }
            }
            else {
                
                foreach($subj_info as $subject){
                    $data .= $this->t('<tr>
                        <td>'.$subject->subject_code.'</td>
                        <td>'.$subject->subject_desc.'</td>
                        <td>'.$subject->college_abbrev.'</td>
                        <td>'.$subject->subjCat_name.'</td>
                        <td>'.$subject->subject_isActive.'</td>
                    </tr>');
                }
            }

            $form['form-container']['subject-table-container']['subject-details'] = [
                '#type' => 'details',
                '#title' => 'Subject Details',
                '#open' => TRUE,
            ];
            $form['form-container']['subject-table-container']['subject-details']['subject-details-table'] = [
                '#type' => 'item',
                '#markup' => $this->t('
                <div>
                    <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>College</th>
                            <th>Category</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="subject-details-table-body">
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

        $subject = $form_state->getValue(['form-container','subject-info','details-container','subj_details']);

        if(empty($subject)){
            $form_state->setError($form, $this->t('Subject info. is empty!'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }
    
}

?>