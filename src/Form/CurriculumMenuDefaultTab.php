<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Form\Templates\Curriculum\DefaultTab\RegisterCurriculumForm;
use Drupal\sedm\Database\CurriculumDatabaseOperations;

class CurriculumMenuDefaultTab extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sedm_menu_curriculum_default_tab';
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
    $form['curriculum_default'] = array(
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-search-curriculum',
      );

      $form['search-curriculum'] = array(
        '#type' => 'details',
        '#title' => $this->t('Search Curriculum'),
        '#group' => 'curriculum_default',
      );

      $form['search-curriculum']['search-curriculum-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="search-curriculum-container-wrapper">',
        '#suffix' => '</div>',
      ];


      /**
       * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
       * ++++++++++++++++++++ Curriculum Registration Part +++++++++++++++++++++++++
       */
      $form['register-curriculum'] = array(
        '#type' => 'details',
        '#title' => $this->t('Register Curriculum'),
        '#group' => 'curriculum_default',
      );

      $form['register-curriculum']['register-curriculum-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="register-curriculum-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form'] = [
        '#type' => 'container',
      ];

      // Initial container to contain whole form
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="reg-curriculum-form-container-wrapper">',
        '#suffix' => '</div>',
      ];
        
        
      // Curriculum Info fieldset
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum'] = [
        '#type' => 'fieldset',
        '#title' => 'New Curriculum Info.'
      ];
        
        
      // Curriculum Notice container
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['notice-container'] = [
        '#type' => 'container',
      ];
        
      // this container holds the inlinement of  
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="curriculum-info-container-wrapper">',
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

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['college'] = [
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
            'wrapper' => 'curriculum-info-container-wrapper',
        ],
        '#weight' => 1,
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['curriculum-num'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Curriculum No.'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 2019-001',
          'class' => ['flat-input', ],
        ],
        '#weight' => 4,
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['curriculum-school-year'] = [
        '#type' => 'textfield',
        '#title' => $this->t('School Year'),
        '#required' => TRUE,
        '#maxlength' => 9,
        '#attributes' => [
          'placeholder' => 'Ex. 2019-2020',
          'class' => ['flat-input', ],
        ],
        '#weight' => 5,
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['curriculum-year'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Year Created'),
        '#required' => TRUE,
        '#maxlength' => 4,
        '#attributes' => [
          'placeholder' => 'Ex. 2019',
          'class' => ['flat-input', ],
        ],
        '#weight' => 6,
      ];
        
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="subjects-container-wrapper">',
        '#suffix' => '</div>',
      ];
        
        
      /**
       * @Variable  $subj_opt: this variable holds all the subjects
       * fetch from the database to be used for the entire session of creating
       * new curriculum
       * 
       * @Variable years: this variable is an array that holds the years for a curriculum
       * 
       * @Variable sems: this variable is an array that holds the semesters use for year fields
       */
      // $college = $form_state->get('selected_college');
      // if($college === NULL){
      //   $collegeSubjects = $CDO->getSubjects();
      // }
      // else {
      //   $collegeSubjects = $CDO->getSubjectsByCollege($college);
      // }
      // $subj_opt = array();
      // // $collegeSubjects = $dbOperations->getSubjects();
      // $subj_opt['none'] = 'NONE';

      // if($collegeSubjects != NULL){
  
      //   foreach ($collegeSubjects as $collegeSubject) {
  
      //     $subj_opt[$collegeSubject->subject_uid] = $collegeSubject->subject_code.' - '.$collegeSubject->subject_desc;
    
      //   }
       
      // }

      $subj_opt = $this->buildSubjectOptData();

      foreach(self::$years as $year => $yearTitle){
        
        $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
        ['form-container']['curriculum']['subjects-container'][$year] = [
            '#type' => 'details',
            '#title' => $yearTitle,
        ];
        
        $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
        ['form-container']['curriculum']['subjects-container'][$year]['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('The subjects listed below are subjects advisable for @year students.', 
            ['@year' => strtolower($yearTitle)]),

        ];
        
        // This block is for subjects of First Year - First Semester

        foreach(self::$sems as $sem => $semTitle){

            $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
            ['form-container']['curriculum']['subjects-container'][$year][$sem] = [
              "#type" => 'fieldset',
              '#title' => $semTitle,
            ];

            $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
            ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'] = [
              '#type' => 'container',
              '#prefix' => '<div id="subjects-'.$year.'-'.$sem.'-container-wrapper">',
              '#suffix' => '</div>',
              '#attributes' => [
                  'class' => ['container-block', ],
              ],
            ];


            /**
              * @Variable $subj_count: this variable holds the counter of subjects 
              * created on selected year and sem
              */

            // $subj_count = $form_state->get($year.$sem.'_subj_count');
            $subj_fields = $form_state->get($year.$sem.'_subj_fields');

            // We have to ensure that there is at least one name field.
            // if ($subj_count === NULL) {
            // $subj_count = 3;
            // $form_state->set($year.$sem.'_subj_count', $subj_count);
            // }
              if(empty($subj_fields)){
                $subj_fields = [1, 2];
                $form_state->set($year.$sem.'_subj_fields', $subj_fields);
              }

            foreach($subj_fields as $subj_field){

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field] = [
                  '#type' => 'container',
                  '#attributes' => [
                    'class' => ['inline-container-col2', ],
                  ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['subj_description'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Subject'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['number-container'] = [
                  '#type' => 'container',
                  '#attributes' => [
                    'class' => ['inline-container-col5',],
                  ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['number-container']['lab_units'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Laboratory Units'),
                  '#attributes' => [
                    'placeholder' => 'Ex. 3',
                    'class' => ['flat-input', ],
                  ],
                  '#min' => '0',
                  '#max' => '255',
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['number-container']['lec_units'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Lecture Units'),
                  '#attributes' => [
                    'placeholder' => 'Ex. 3',
                    'class' => ['flat-input', ],
                  ],
                  '#min' => '0',
                  '#max' => '255',
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['number-container']['lab_hours'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Laboratory Hours'),
                  '#attributes' => [
                    'placeholder' => 'Ex. 3',
                    'class' => ['flat-input', ],
                  ],
                  '#min' => '0',
                  '#max' => '255',
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container']
                [$sem.'_subjects_container'][$subj_field]['number-container']['lect_hours'] = [
                  '#type' => 'number',
                  '#title' => $this->t('Lecture Hours'),
                  '#attributes' => [
                    'placeholder' => 'Ex. 3',
                    'class' => ['flat-input', ],
                  ],
                  '#min' => '0',
                  '#max' => '255',
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$subj_field]['subj_prerequi_1'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Prerequisite 1'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$subj_field]['subj_prerequi_2'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Prerequisite 2'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];

                if(count($subj_fields) > 1){

                  $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                  ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$subj_field]['subj-remove-btn'] = [
                      '#type' => 'submit',
                      '#name' => $year.$sem.$subj_field,
                      '#value' => $this->t('Remove Field'),
                      '#data' => ['year' => $year, 'sem' => $sem, 'subj_field' => $subj_field],
                      '#submit' => ['::removeField'],
                      '#ajax' => [
                        'callback' => '::updateSubjectCallback',
                        'event' => 'click',
                        'wrapper' => 'subjects-'.$year.'-'.$sem.'-container-wrapper',
                      ],
                  ];
  
                }
            }

              $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
              ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'-action-container'] = [
                '#type' => 'actions',
                '#attributes' => [
                  'class' => ['action-container',],
                ],
              ];
  
              $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
              ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'-action-container']['subj-add-btn'] = [
                '#type' => 'submit',
                '#name' => $year.$sem,
                '#value' => $this->t('Add Field'),
                '#data' => ['year' => $year, 'sem' => $sem,],
                '#submit' => ['::addNewField'],
                '#ajax' => [
                  'callback' => '::updateSubjectCallback',
                  'event' => 'click',
                  'wrapper' => 'subjects-'.$year.'-'.$sem.'-container-wrapper',
                ],
              ];

        }

      }

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container']['elective'] = [
          '#type' => 'details',
          '#title' => 'Electives',
      ];
      
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container']['elective']['description'] = [
          '#type' => 'item',
          '#markup' => $this->t('The subjects listed below are elective subjects.'),
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container']['elective']['subjects-elective-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="subjects-elective-container-wrapper">',
        '#suffix' => '</div>',
        '#attributes' => [
            'class' => ['inline-container-col4', ],
        ],
      ];

      $electFields = $form_state->get('elective_fields');

      if(empty($electFields)){
        $electFields = [1, 2];
        $form_state->set('elective_fields', $electFields);
      }

      foreach($electFields as $electField){

        $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
        ['form-container']['curriculum']['subjects-container']
        ['elective']['subjects-elective-container'][$electField]['subj_description'] = [
            '#type' => 'select',
            '#title' => $this->t('Subject'),
            '#options' => $subj_opt,
            '#attributes' => [
            'class' => ['flat-element', ],
            ],
        ];

        $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
        ['form-container']['curriculum']['subjects-container']
        ['elective']['subjects-elective-container'][$electField]['subj_prerequi_1'] = [
            '#type' => 'select',
            '#title' => $this->t('Prerequisite 1'),
            '#options' => $subj_opt,
            '#attributes' => [
            'class' => ['flat-element', ],
            ],
        ];

        $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
        ['form-container']['curriculum']['subjects-container']
        ['elective']['subjects-elective-container'][$electField]['subj_prerequi_2'] = [
            '#type' => 'select',
            '#title' => $this->t('Prerequisite 2'),
            '#options' => $subj_opt,
            '#attributes' => [
            'class' => ['flat-element', ],
            ],
        ];

        if(count($electFields) > 1){

          $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
          ['form-container']['curriculum']['subjects-container']
          ['elective']['subjects-elective-container'][$electField]['subj-remove-btn'] = [
              '#type' => 'submit',
              '#name' => 'removeElectiveField',
              '#value' => $this->t('Remove Field'),
              '#data' => ['elective_field' => $electField,],
              '#submit' => ['::removeElectiveSubject'],
              '#ajax' => [
                'callback' => '::updateElectiveSubjectCallback',
                'event' => 'click',
                'wrapper' => 'subjects-elective-container-wrapper',
              ],
          ];

        }
    }

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container']
      ['elective']['subjects-elective-container']['elective-action-container'] = [
        '#type' => 'actions',
        '#attributes' => [
          'class' => ['action-container',],
        ],
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['subjects-container']
      ['elective']['subjects-elective-container']['elective-action-container']['subj-add-btn'] = [
        '#type' => 'submit',
        '#name' => 'addElectiveField',
        '#value' => $this->t('Add Field'),
        '#submit' => ['::addNewElectiveField'],
        '#ajax' => [
          'callback' => '::updateElectiveSubjectCallback',
          'event' => 'click',
          'wrapper' => 'subjects-elective-container-wrapper',
        ],
      ];


      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['submit']['save'] = [
        '#type' => 'button',
        '#value' => $this->t('Save Curriculum'),
        '#ajax' => [
          'callback' => '::verifyCurriculumToSave',
          'wrapper' => 'register-curriculum-container-wrapper',
          'event' => 'click',
        ],
        
      ];

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['submit']['publish'] = [
        '#type' => 'button',
        '#value' => $this->t('Publish Curriculum'),
        '#ajax' => [
          'callback' => '::verifyCurriculumToPublish',
          'wrapper' => 'register-curriculum-container-wrapper',
          'event' => 'click',
        ],
        
      ];

      // ####################### END OF REGISTER CURRICULUM PART ################################


      $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      return $form;
              
        
  }
        

  public function buildProgramSelection(array &$form, FormStateInterface $form_state){

    // get the value of selected college
    $college = $form_state->getValue(['register-curriculum','register-curriculum-container',
    'register-curriculum-form','form-container', 'curriculum', 'curriculum-info-container','college']);

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

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['program'] = [
        '#type' => 'select',
        '#title' => $this->t('Program'),
        '#options' => $programOpt,
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['flat-element', ],
        ],
        '#weight' => 2,
      ];

    }
  
    $form_state->setRebuild();
    return $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
    ['form-container']['curriculum']['curriculum-info-container'];

  }

  public function buildSubjectOptData(){
    
    
    $CDO = new CurriculumDatabaseOperations();
    $subj_cats = $CDO->getSubjectCategories();
    $subj_opt = array();
    $subj_opt['Default']['none'] = 'None';
    foreach($subj_cats as $subj_cat){
      $subjectsByCat = $CDO->getSubjectByCategory($subj_cat->subjCat_uid);

      foreach($subjectsByCat as $subjectByCat){
        $subj_opt[$subj_cat->subjCat_name][$subjectByCat->subject_uid] = $subjectByCat->subject_code.' - '.$subjectByCat->subject_desc;
      }
    }

    return $subj_opt;

  }

  public function form_rebuild(array &$form, FormStateInterface $form_state){
    $college = $form_state->getValue(['register-curriculum','register-curriculum-container',
    'register-curriculum-form','form-container', 'curriculum', 'curriculum-info-container','college']);

    $form_state->set('selected_college', $college);

    $form_state->setRebuild();
  }

  public function buildProgram(array &$form, FormStateInterface $form_state){

    $department = $form_state->getValue(['register-curriculum','register-curriculum-container',
    'register-curriculum-form','form-container', 'curriculum', 'curriculum-info-container','department']);

    // instatiate DatabaseOperations Class
    $CDO = new CurriculumDatabaseOperations();

    // get department programs
    if($department != NULL){

      $programs = $CDO->getPrograms($department);
      $programOpt = array();
      $programOpt['none'] = 'NONE';
  
      foreach ($programs as $program) {
  
        $programOpt[$program->program_uid] = $program->program_abbrev.' - '.$program->program_name;
  
      }

      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['program'] = [
        '#type' => 'select',
        '#title' => $this->t('Program'),
        '#options' => $programOpt,
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['flat-element', ],
        ],
      ];

    }

    return $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
    ['form-container']['curriculum']['curriculum-info-container'];

  }

  public function updateSubjectCallback(array &$form, FormStateInterface $form_state){

    $data = $form_state->getTriggeringElement()['#data'];
    $year = $data['year'];
    $sem = $data['sem'];
    $form_state->setTriggeringElement(NULL);

    return $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
    ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'];
    
  }

  public function removeField(array &$form, FormStateInterface $form_state){

    
    $data = $form_state->getTriggeringElement()['#data'];
    $year = $data['year'];
    $sem = $data['sem'];
    $subj_field = $data['subj_field'];

    $subject_fields = $form_state->get($year.$sem.'_subj_fields');

    if (($key = array_search($subj_field, $subject_fields)) !== false) {
      unset($subject_fields[$key]);
      $form_state->set($year.$sem.'_subj_fields', ($subject_fields));
    }
    
    $output = 'Field has been removed!';

    $this->messenger()->addMessage($output);

    $form_state->setRebuild();

  }


  public function addNewField(array &$form, FormStateInterface $form_state){
    
    $data = $form_state->getTriggeringElement()['#data'];
    $year = $data['year'];
    $sem = $data['sem'];
    $subj_fields = $form_state->get($year.$sem.'_subj_fields');

    $last_subj_field_ele = end($subj_fields);

    $subj_fields[] = $last_subj_field_ele + 1;

    $form_state->set($year.$sem.'_subj_fields', $subj_fields);

    $output = 'A new field has been added!';

    $this->messenger()->addMessage($output);

    $form_state->setRebuild();
  }

  /**
   * Additional function addressing the electives
   */
  public function updateElectiveSubjectCallback(array &$form, FormStateInterface $form_state){

    return $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
    ['form-container']['curriculum']['subjects-container']['elective']['subjects-elective-container'];

  }

  public function removeElectiveSubject(array &$form, FormStateInterface $form_state){
    $data = $form_state->getTriggeringElement()['#data'];
    $elective_field = $data['elective_field'];
    $elective_fields = $form_state->get('elective_fields');

    if (($key = array_search($elective_field, $elective_fields)) !== false) {
      unset($elective_fields[$key]);
      $form_state->set('elective_fields', $elective_fields);
    }
    
    $output = 'Field has been removed!'; 

    $this->messenger()->addMessage($output);

    $form_state->getTriggeringElement(NULL);
    $form_state->setRebuild();

  }

  public function addNewElectiveField(array &$form, FormStateInterface $form_state){
    $elective_fields = $form_state->get('elective_fields');

    $last_elective_field_ele = end($elective_fields);

    $elective_fields[] = $last_elective_field_ele + 1;

    $form_state->set('elective_fields', $elective_fields);

    $output = 'A new field has been added!'; 

    $this->messenger()->addMessage($output);

    $form_state->setRebuild();
  }

  // END OF ADDITIONAL FUNCTIONS

  public function verifyCurriculumToSave(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();

    if($form_state->getErrors()){

      $content['form-container']['notice-container']['status_messages'] = [
        '#type' => 'status_messages',
      ];

      $command = new OpenDialogCommand('#register-curriculum-notice-dialog',$this->t('Register New Curriculum'), $content, ['width' => '50%',]);

      $response->addCommand($command);

      return $response;

    }
    else {

      $curr_subjs = array();
      $curr_info = array();

      $curr_info['curr_num'] = $form_state->getValue(['register-curriculum','register-curriculum-container',
      'register-curriculum-form','form-container','curriculum','curriculum-info-container','curriculum-num']);

      $curr_info['curr_schoolYear'] = $form_state->getValue(['register-curriculum','register-curriculum-container',
      'register-curriculum-form','form-container','curriculum','curriculum-info-container','curriculum-school-year']);

      $curr_info['curr_yearCreated'] = $form_state->getValue(['register-curriculum','register-curriculum-container',
      'register-curriculum-form','form-container','curriculum','curriculum-info-container','curriculum-year']);

      $curr_info['curr_college'] = $form_state->getValue(['register-curriculum','register-curriculum-container',
      'register-curriculum-form','form-container','curriculum','curriculum-info-container','college']);

      $curr_info['curr_program'] = $form_state->getValue(['register-curriculum','register-curriculum-container',
      'register-curriculum-form','form-container','curriculum','curriculum-info-container','program']);

      foreach(self::$years as $year => $yearTitle){
      
        foreach(self::$sems as $sem => $semTitle){
  
          $subjects = $form_state->getValue([
            'register-curriculum','register-curriculum-container','register-curriculum-form',
            'form-container', 'curriculum', 'subjects-container', 
            $year, $sem, $sem.'-container', $sem.'_subjects_container']);

          $curr_subjs[$year.$sem] = $subjects;
        }
  
      }

      $electives = $form_state->getValue([
        'register-curriculum','register-curriculum-container','register-curriculum-form',
        'form-container','curriculum','subjects-container',
        'elective','subjects-elective-container'
      ]);

      $curr_subjs['electives'] = $electives;

      $_SESSION['sedm']['curr_subjs'] = $curr_subjs;
      $_SESSION['sedm']['curr_infos'] = $curr_info; 

      $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifyCurriculumToSaveModalForm');

      $command = new OpenModalDialogCommand($this->t('Register New Curriculum'), $modal_form, ['width' => '50%']);

      $response->addCommand($command);

      return $response;

    }

  }

  public function verifyCurriculumToPublish(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();

    if($form_state->getErrors()){

      $content['form-container']['notice-container']['status_messages'] = [
        '#type' => 'status_messages',
      ];

      $command = new OpenDialogCommand('#register-curriculum-notice-dialog',$this->t('Register New Curriculum'), $content, ['width' => '50%',]);

      $response->addCommand($command);

      return $response;

    }
    else {

      $curr_subjs = array();

      foreach(self::$years as $year => $yearTitle){
      
        foreach(self::$sems as $sem => $semTitle){
  
          $subjects = $form_state->getValue([
            'register-curriculum','register-curriculum-container','register-curriculum-form',
            'form-container', 'curriculum', 'subjects-container', 
            $year, $sem, $sem.'-container', $sem.'_subjects_container']);

          $curr_subjs[$year.$sem] = $subjects;
        }
  
      }

      $electives = $form_state->getValue([
        'register-curriculum','register-curriculum-container','register-curriculum-form',
        'form-container','curriculum','subjects-container',
        'elective','subjects-elective-container'
      ]);

      $curr_subjs['electives'] = $electives;


      $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifyCurriculumToPublishModalForm', $curr_subjs);

      $command = new OpenModalDialogCommand($this->t('Register New Curriculum'), $modal_form, ['width' => '50%']);

      $response->addCommand($command);

      return $response;

    }

  }
  
    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}

?>