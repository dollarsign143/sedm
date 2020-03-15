<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
/**
 * module classes
 */
use Drupal\sedm\Database\DatabaseOperations; // class for database common operations
// class for curriculum subjects tab adding new subject part
use Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\AddNewSubjectForm; 

class CurriculumMenuSubjectsTab extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sedm_menu_curriculum_subjects_tab';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#tree'] = TRUE;
    $form['curriculum_subject'] = array(
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-search-subject',
      );

      $form['search-subject'] = array(
        '#type' => 'details',
        '#title' => $this->t('Search Subject'),
        '#group' => 'curriculum_subject',
      );

      $form['search-subject']['search-subject-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="search-subject-container-wrapper">',
        '#suffix' => '</div>',
      ];

      /**
       * +++++++++++++++++ Add New Subject Part ++++++++++++++++++++++++++++++
       */

      $form['add-subject'] = array(
        '#type' => 'details',
        '#title' => $this->t('Add Subject'),
        '#group' => 'curriculum_subject',
      );

      $form['add-subject']['add-subject-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="add-subject-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['add-subject']['add-subject-container']['add-subject-form'] = [
        '#type' => 'container',
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="add-subject-form-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['form-title'] = [
          '#type' => 'item',
          '#markup' => $this->t('<h2>Add New Subject</h2>'),
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container'] = [
          '#type' => 'fieldset',
          '#title' => 'Subject Info.'
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['description'] = [
          '#type' => 'item',
          '#markup' => $this->t('Fill out all the required details.'),
      ];

      /**
       * @RenderElement container : container is the wrapper 
       * of the college and department select render elements
       */
      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['select-container'] = [
          '#type' => 'container',
          '#prefix' => '<div id="subj-details-select-container-wrapper">',
          '#suffix' => '</div>',
      ];

      /**
       * @Variable $dbOperations = object to hold DatabaseOperations class
       * @Variable $colleges = object to hold the result of the query
       * @Variable array $collegeOpt : holds the custom layout of every college
       *      for select render element
       */
      $dbOperations = new DatabaseOperations(); // instantiate DatabaseOperations Class
      $colleges = $dbOperations->getColleges(); // get colleges
      $collegeOpt = array();

      foreach ($colleges as $college) {

        $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;

      }

      /**
       * @RenderElement select: this element will trigger the 
       * selection of department
       */
      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['select-container']['college'] = [
        '#type' => 'select',
        '#title' => $this->t('College'),
        '#options' => $collegeOpt,
        '#required' => TRUE,
        '#attributes' => array('class' => array('flat-input')),
        '#ajax' => [
          'callback' => '::buildDepartment',
          'wrapper' => 'subj-details-select-container-wrapper',
        ],
      ];

    /**
     * @RenderElement container : this is the container of
     * all the render elements that are inline in style
     * ::subj-code
     * ::subj-units
     * ::subj-lecture-hrs
     * ::subj-lab-hrs
     */

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['inline-container-col2'],
          ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-code'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject Code'),
        '#maxlength' => 10,
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Eng1',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Subject Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-lecture-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Lecture Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-lab-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Laboratory Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-lecture-hrs'] = [
        '#type' => 'number',
        '#title' => $this->t('Lecture Hours'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['subj-lab-hrs'] = [
        '#type' => 'number',
        '#title' => $this->t('Laboratory Hours'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['isElective'] = [
        '#type' => 'checkbox',
        '#title' => 'Set as Elective',
        '#return_value' => 'elective',
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['inline-container']['isActive'] = [
        '#type' => 'checkbox',
        '#title' => 'Set as Active',
        '#return_value' => 'active',
      ];

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['subj-description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject Description'),
        '#size' => 60,
        '#maxlength' => 100,
        '#required' => TRUE,
        '#attributes' => [
            'class' => ['flat-input',],
            'placeholder' => 'Subject Description',
        ],
      ];


      // Group submit handlers in an actions element with a key of "actions" so
      // that it gets styled correctly, and so that other modules may add actions
      // to the form.
      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['actions'] = [
        '#type' => 'actions',
      ];

      // Add a submit button that handles the submission of the form.
      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['actions']['submit'] = [
        '#type' => 'button',
        '#value' => $this->t('Submit'),
        '#ajax' => [
          'callback' => '::verifySubject',
          'wrapper' => 'add-subject-container-wrapper',
          'event' => 'click',
        ],
      ];

  // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

      // Add the curriculum forms css styles
      $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

      // Add the core AJAX library.
      // Important for ajax features
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      return $form;
  }

  public function buildDepartment(array &$form, FormStateInterface $form_state){

    // get the college select value
    $college = $form_state->getValue(['add-subject','add-subject-container',
    'add-subject-form','form-container','subject-details-container','select-container','college']);

    // instatiate DatabaseOperations Class
    $dbOperations = new DatabaseOperations();

    if(!empty($college)){

      // get departments
      $departments = $dbOperations->getDepartments($college);
      $departmentOpt = array();
      // $departmentOpt['none'] = 'NONE';
  
      foreach ($departments as $department) {
  
        $departmentOpt[$department->department_uid] = $department->department_abbrev.' - '.$department->department_name;
  
      }

      $form['add-subject']['add-subject-container']['add-subject-form']
      ['form-container']['subject-details-container']['select-container']['department'] = [
        '#type' => 'select',
        '#title' => $this->t('Department'),
        '#options' => $departmentOpt,
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['flat-input',],
        ],
      ];

    }

    return $form['add-subject']['add-subject-container']['add-subject-form']
    ['form-container']['subject-details-container']['select-container'];

  }


  public function verifySubject(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();

    if($form_state->getErrors()){

      $content['form-container']['notice-container']['status_messages'] = [
        '#type' => 'status_messages',
      ];

      $response->addCommand(new OpenDialogCommand('#notice-dialog',$this->t('Add New Subject'), $content, ['width' => '50%',]));

      return $response;

    }
    else {

      $subject['code'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-code']);

      $subject['units'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-units']);

      $subject['lectUnits'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-lecture-units']);

      $subject['labUnits'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-lab-units']);

      $subject['lecHours'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-lecture-hrs']);

      $subject['labHours'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','subj-lab-hrs']);

      $subject['description'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','subj-description']);

      $subject['isElective'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','isElective']);

      $subject['isActive'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','inline-container','isActive']);

      $subject['departmentUID'] = $form_state->getValue(['add-subject','add-subject-container',
      'add-subject-form','form-container','subject-details-container','select-container','department']);

      // $addNewSubject = new AddNewSubjects();

      // $result = $addNewSubject->addSubject($subject);

      // if($result == true){
      //   $content['status'] = [
      //     '#type' => 'item',
      //     '#markup' => $this->t('Subject Added Successfully!'), 
      //   ];
  
      //   $modal_command = new OpenDialogCommand('#verify-subject-dialog', $this->t('Add New Subject'), $content, ['width' => '50%',]);
  
      //   $response->addCommand($modal_command);
      // } 
      // else {
      //   $content['status'] = [
      //     '#type' => 'item',
      //     '#markup' => $this->t('Failed to add new subject!'), 
      //   ];
  
      //   $modal_command = new OpenDialogCommand('#verify-subject-dialog', $this->t('Add New Subject'), $content, ['width' => '50%',]);
  
      //   $response->addCommand($modal_command);
      // }

      $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifySubjectModalForm');

      $command = new OpenModalDialogCommand($this->t('Add new Subject'), $modal_form, ['width' => '50%']);

      $response->addCommand($command);

      return $response;


    } // end of else 

  } // END OF verifySubject FUNCTION

  public function cancelAddingSubject(array $form, FormStateInterface $form_state){

    $response = new AjaxResponse();

    $command = new CloseModalDialogCommand();

    $response->addCommand($command);

    return $response; 
}

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}

?>