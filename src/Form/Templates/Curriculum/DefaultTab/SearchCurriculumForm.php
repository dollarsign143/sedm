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
            '#type' => 'fieldset',
            '#title' => 'Curriculum Info.'
        ];
            
            
        // Curriculum Notice container
        $form['form-container']['curriculum']['notice-container'] = [
            '#type' => 'container',
        ];
            
        // this container holds the inlinement of  
        $form['form-container']['curriculum']['curriculum-info-container'] = [
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
                'wrapper' => 'curriculum-info-container-wrapper',
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
            '#weight' => 4,
        ];

        $form['form-container']['curriculum']['actions'] = [
            '#type' => 'actions'
        ];

        $form['form-container']['curriculum']['actions']['search'] = [
            '#type' => 'submit',
            '#value' => 'Search'
        ];
            
        $form['form-container']['curriculum']['subjects-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="subjects-container-wrapper">',
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


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }


} // end of the class

?>