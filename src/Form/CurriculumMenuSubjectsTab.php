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
use Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\AddNewSubjects; 

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

      $form['add-subject'] = array(
        '#type' => 'details',
        '#title' => $this->t('Add Subject'),
        '#group' => 'curriculum_subject',
      );

      $form['add-subject']['add-subject-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="enrollment-eval-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $addNewSubject = new AddNewSubjects();
      $form['add-subject']['add-subject-container']['add-subject-form'] = $addNewSubject->getTemplForm();

      $form['edit-subject'] = array(
        '#type' => 'details',
        '#title' => $this->t('Edit Subject'),
        '#group' => 'curriculum_subject',
      );

      $form['delete-subject'] = array(
        '#type' => 'details',
        '#title' => $this->t('Delete Subject'),
        '#group' => 'curriculum_subject',
      );

      // Add the curriculum forms css styles
      $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

      // Add the core AJAX library.
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
      'add-subject-form','form-container','subject-details-container','inline-container','department']);

      // $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifySubjectModalForm', $subject);

      // $response->addCommand(new OpenDialogCommand('#verify-subject-dialog', $this->t('Add New Subject'), $modal_form, ['width' => '50%',]));

      $addNewSubject = new AddNewSubjects();

      $result = $addNewSubject->addSubject($subject);

      $content['status'] = [
        '#type' => 'item',
        '#markup' => $this->t('Subject Added Successfully!'), 
      ];

      $response->addCommand(new OpenDialogCommand('#verify-subject-dialog', $this->t('Add New Subject'), $content, ['width' => '50%',]));

      return $response;


    } // end of else 

  } // END OF verifySubject FUNCTION


    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {


  }

}

?>