<?php

namespace Drupal\sedm\Form\Templates\Curriculum\DefaultTab;

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

class SearchCurriculumForm extends FormBase{

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_menu_curriculum_default_tab_curri_search_form';
    }

    private static $years = [
        'first-year' => 'First Year',
        'second-year' => 'Second Year',
        'third-year' => 'Third Year',
        'fourth-year' => 'Fourth Year'
    ];

    private static $sems = [
        'first-sem' => 'First Semester', 
        'second-sem' => 'Second Semester', 
        'summer-sem' => 'Summer Class'
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        // Initial container to contain whole form
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="search-curriculum-form-container-wrapper">',
            '#suffix' => '</div>',
        ];
        
        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Search Curriculum</h2>'),
        ];
            
        // Curriculum Info fieldset
        $form['form-container']['curriculum'] = [
            '#type' => 'details',
            '#title' => 'Curriculum Info.',
            '#open' => TRUE,
        ];
            
            
        // Curriculum Notice container
        $form['form-container']['curriculum']['message-container'] = [
            '#type' => 'container',
        ];
            
        // this container holds the inlinement of  
        $form['form-container']['curriculum']['curriculum-info-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="search-curriculum-info-container-wrapper">',
            '#suffix' => '</div>',
            '#attributes'  => [
                'class' => ['inline-container-col2', ]
            ],
        ];

        /**
         * @Variable $dbOperations = object to hold DatabaseOperations class
         * @Variable $colleges = object to hold the result of the query
         */
        $CDO = new CurriculumDatabaseOperations();
        $colleges = $CDO->getColleges();
        $collegeOpt = array();
            
        foreach ($colleges as $college) {
            
            $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;
            
        }

        $form['form-container']['curriculum']['curriculum-info-container']['college'] = [
            '#type' => 'select',
            '#title' => $this->t('College'),
            '#options' => $collegeOpt,
            '#required' => TRUE,
            '#attributes' => [
            'class' => ['flat-element', ],
            ],
            '#submit' => ['::form_rebuild'],
            '#ajax' => [
                'callback' => '::buildProgramSelection',
                'wrapper' => 'search-curriculum-info-container-wrapper',
                'event' => 'change',
            ],
            '#weight' => 1,
        ];

        $programOpt = array();
        $programOpt['none'] = 'NONE';
        $form['form-container']['curriculum']['curriculum-info-container']['program'] = [
            '#type' => 'select',
            '#title' => $this->t('Program'),
            '#options' => $programOpt,
            '#required' => TRUE,
            '#attributes' => [
            'class' => ['flat-element', ],
            ],
            '#validated' => TRUE,
            '#weight' => 2,
        ];

        $form['form-container']['curriculum']['curriculum-info-container']['curriculum-num'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Curriculum No.'),
            '#required' => TRUE,
            '#attributes' => [
            'placeholder' => 'Ex. 2019-001',
            'class' => ['flat-input', ],
            ],
            '#weight' => 3,
        ];

        $form['form-container']['curriculum']['actions'] = [
            '#type' => 'actions',
            '#weight' => 4,
        ];

        $form['form-container']['curriculum']['actions']['search'] = [
            '#type' => 'submit',
            '#value' => 'Search',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::searchCurriculum',
                'wrapper' => 'search-curriculum-subjects-container-wrapper', 
                'event' => 'click',
            ]
        ];
            
        $form['form-container']['curriculum-subjects']['subjects-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="search-curriculum-subjects-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }

    public function buildProgramSelection(array &$form, FormStateInterface $form_state){

        // get the value of selected college
        $college = $form_state->getValue(['form-container', 'curriculum', 'curriculum-info-container','college']);

        $form_state->set('selected_college', $college);
        // instatiate DatabaseOperations Class
        $CDO = new CurriculumDatabaseOperations();

        // get departments
        if($college != NULL){

            $programs = $CDO->getProgramsByCollege($college);
            $programOpt = array();
        
            foreach ($programs as $program) {
        
                $programOpt[$program->program_uid] = $program->program_abbrev.' - '.$program->program_name;
        
            }

            $form['form-container']['curriculum']['curriculum-info-container']
            ['program']['#options'] = $programOpt;

        }
        
        return $form['form-container']['curriculum']['curriculum-info-container'];
    
    } 

    public function searchCurriculum(array &$form, FormStateInterface $form_state){

        if($form_state->getErrors()){

            $content['form-container']['notice-container']['message'] = [
                '#type' => 'status_messages',
            ];
            return $this->errorModal($content);
        }
        else {
            $info['college'] = $form_state->getValue(['form-container','curriculum','curriculum-info-container','college']);
            $info['program'] = $form_state->getValue(['form-container','curriculum','curriculum-info-container','program']);
            $info['curri_num'] = $form_state->getValue(['form-container','curriculum','curriculum-info-container','curriculum-num']);
            
            $CDO = new CurriculumDatabaseOperations();
            $curri_info = $CDO->getCurriculumInfo($info['program'], $info['curri_num']);

            if(empty($curri_info)){
                $content['form-container']['notice-container']['message'] = [
                    '#type' => 'item',
                    '#markup' => $this->t('No matching item!'),
                ];
                return $this->errorModal($content);
            }
            else {
                return $this->buildCurriculumData($form, $form_state, $curri_info);
            }
        }

    }

    public function buildCurriculumData(array &$form, FormStateInterface $form_state, $curri_info = array()){

        foreach(self::$years as $year => $yearTitle){
            $form['form-container']['curriculum-subjects']['subjects-container'][$year] = [
                '#type' => 'details',
                '#title' => $yearTitle,
            ];
            
            $form['form-container']['curriculum-subjects']['subjects-container'][$year]['description'] = [
                '#type' => 'item',
                '#markup' => $this->t('The subjects listed below are subjects advisable for @year students.', 
                ['@year' => strtolower($yearTitle)]),

            ];

            foreach(self::$sems as $sem => $semTitle){

                $form['form-container']['curriculum-subjects']['subjects-container'][$year][$sem] = [
                    "#type" => 'fieldset',
                    '#title' => $semTitle,
                ];

                $form['form-container']['curriculum-subjects']['subjects-container'][$year][$sem][$sem.'-container'] = [
                    '#type' => 'container',
                    '#prefix' => '<div id="subjects-'.$year.'-'.$sem.'-container-wrapper">',
                    '#suffix' => '</div>',
                    '#attributes' => [
                        'class' => ['container-block', ],
                    ],
                ];

                $CDO = new CurriculumDatabaseOperations();
                $subjects = $CDO->getCurriculumSubjects($year, $sem, $curri_info[0]->curriculum_uid);

                $data = NULL;
                if(empty($subjects)){
                    $data .= '<tr>
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
                        $prerequi1 = $CDO->getSubjectInfoByUID($key->curricSubj_prerequisite1);
                        $prerequi2 = $CDO->getSubjectInfoByUID($key->curricSubj_prerequisite2);
                        $data .= '<tr>
                            <td>'.$key->subject_code.'</td>
                            <td>'.$key->subject_desc.'</td>
                            <td>'.$key->curricSubj_labUnits.'</td>
                            <td>'.$key->curricSubj_lecUnits.'</td>
                            <td>'.$key->curricSubj_labHours.'</td>
                            <td>'.$key->curricSubj_lecHours.'</td>
                            <td>'.$prerequi1[0]->subject_code.', '.$prerequi2[0]->subject_code.'</td>
                        </tr>';
                    }
                }

                $form['form-container']['curriculum-subjects']['subjects-container'][$year][$sem][$sem.'-container']['table'] = [
                    '#type' => 'markup',
                    '#markup' => $this->t('
                    <div>
                        <table>
                        <thead>
                            <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Laboratory Units</th>
                            <th>Lecture Units</th>
                            <th>Laboratory Hours</th>
                            <th>Lecture Hours</th>
                            <th>Prerequisites</th>
                            </tr>
                        </thead>
                        <tbody class="curriculumSubjectsBody">
                        '.$data.'
                        </tbody>
                        </table>
                    </div>'),
                ];
            }
        }

        $form['form-container']['curriculum-subjects']['subjects-container']['electives'] = [
            '#type' => 'details',
            '#title' => $this->t('Electives'),
        ];
        
        $form['form-container']['curriculum-subjects']['subjects-container']['electives']['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('The subjects listed below are elective subjects.'),
        ];

        $CDO = new CurriculumDatabaseOperations();
        $electives = $CDO->getCurriculumElectiveSubjects($curri_info[0]->curriculum_uid);

        $electiveData = NULL;
        if(empty($electives)){
            $data .= '<tr>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
                <td>NONE</td>
            </tr>';
        }
        else {
            foreach($electives as $elective => $key){
                $prerequi1 = $CDO->getSubjectInfoByUID($key->electiveSubj_prerequisite1);
                $prerequi2 = $CDO->getSubjectInfoByUID($key->electiveSubj_prerequisite2);
                $electiveData .= '<tr>
                    <td>'.$key->subject_code.'</td>
                    <td>'.$key->subject_desc.'</td>
                    <td>'.$key->curricSubj_labUnits.'</td>
                    <td>'.$key->curricSubj_lecUnits.'</td>
                    <td>'.$prerequi1[0]->subject_code.', '.$prerequi2[0]->subject_code.'</td>
                </tr>';
            }
        }

        $form['form-container']['curriculum-subjects']['subjects-container']['electives']['table'] = [
            '#type' => 'markup',
            '#markup' => $this->t('
            <div>
                <table>
                <thead>
                    <tr>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Laboratory Units</th>
                    <th>Lecture Units</th>
                    <th>Prerequisites</th>
                    </tr>
                </thead>
                <tbody class="curriculumElectiveSubjectsBody">
                '.$electiveData.'
                </tbody>
                </table>
            </div>'),
        ];

        return $form['form-container']['curriculum-subjects']['subjects-container'];

    }

    public function errorModal($content){
        $response = new AjaxResponse();

        $command = new OpenModalDialogCommand($this->t('Error!'), $content, ['width' => '50%',]);

        $response->addCommand($command);
    
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }


} // end of the class

?>