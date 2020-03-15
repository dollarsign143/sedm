<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Form\Templates\Curriculum\DefaultTab\RegisterCurriculumForm;
use Drupal\sedm\Database\DatabaseOperations;

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
            'class' => ['inline-container', ]
        ],
      ];
        
      /**
       * @Variable $dbOperations = object to hold DatabaseOperations class
       * @Variable $colleges = object to hold the result of the query
       */
      $dbOperations = new DatabaseOperations();
      $colleges = $dbOperations->getColleges();
      $collegeOpt = array();
      $collegeOpt['none'] = 'NONE';
        
      foreach ($colleges as $college) {
        
        $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;
        
      }
        
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['college'] = [
        '#type' => 'select',
        '#title' => $this->t('College'),
        '#options' => $collegeOpt,
        '#attributes' => array('class' => array('inline-select')),
        '#ajax' => [
            'callback' => '::buildDepartment',
            'wrapper' => 'curriculum-info-container-wrapper',
        ],
      ];
        
        
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
      ['form-container']['curriculum']['curriculum-info-container']['curriculum-year'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Year of Effectivity'),
        '#attributes' => array('placeholder' => 'Ex. 2009', 'class' => array('inline-select')),
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
      $subj_opt = [
      'NULL' => $this->t('None'),
      'Eng1' => $this->t('Eng1 - English 1'),
      'Math1' => $this->t('Math1 - Mathematics 1'),
      'Fil1' => $this->t('Fil1 - Filipino 1'),
      ];
        
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
                'class' => ['inline-container-col3', ],
            ],
            ];


            /**
              * @Variable $subj_count: this variable holds the counter of subjects 
              * created on selected year and sem
              */

            $subj_count = $form_state->get($year.$sem.'_subj_count');

            // We have to ensure that there is at least one name field.
            if ($subj_count === NULL) {
            $subj_count = 3;
            $form_state->set($year.$sem.'_subj_count', $subj_count);
            }


            for($i = 0; $i < $subj_count; $i++){

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$i]['subj_description'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Subject'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$i]['subj_prerequi_1'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Prerequisite 1'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'_subjects_container'][$i]['subj_prerequi_2'] = [
                    '#type' => 'select',
                    '#title' => $this->t('Prerequisite 2'),
                    '#options' => $subj_opt,
                    '#attributes' => [
                    'class' => ['flat-element', ],
                    ],
                ];


            }

            $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
            ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'-action-container'] = [
            '#type' => 'actions',
            ];

            $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
            ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'-action-container']['subj-add-btn'] = [
            '#type' => 'button',
            '#value' => $this->t('Add Field'),
            '#ajax' => [
                'callback' => '::addNewField',
                'wrapper' => 'subjects-'.$year.'-'.$sem.'-container-wrapper',
            ],
            // '#attributes' => [
            //   'id' => 'curr-semester-action-btn',
            // ],
            ];

            if($subj_count > 1){

                $form['register-curriculum']['register-curriculum-container']['register-curriculum-form']
                ['form-container']['curriculum']['subjects-container'][$year][$sem][$sem.'-container'][$sem.'-action-container']['subj-remove-btn'] = [
                    '#type' => 'submit',
                    '#name' => $year.$sem,
                    '#value' => $this->t('Remove Field'),
                    '#size' => 5,
                    '#data' => ['year' => $year, 'sem' => $sem],
                    '#submit' => ['::removeSubject'],
                    '#ajax' => [
                    'callback' => '::updateSubjectCallback',
                    'event' => 'click',
                    'wrapper' => 'subjects-'.$year.'-'.$sem.'-container-wrapper',
                    ],
                    // '#attributes' => [
                    //   'id' => 'curr-semester-action-btn',
                    // ],
                ];

            }

        }
              
        
      }
        
      // ##################################################################
      
      $form['form-container']['curriculum']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit New Created Curriculum',
      ];


      $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      return $form;
  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

?>