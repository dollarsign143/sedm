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
use Drupal\sedm\Database\CurriculumDatabaseOperations; // class for database common operations
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

      $searchSubjectForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\SearchSubjectForm');
      // $searchSubjectForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\EnrollmentEvaluation');
      $form['search-subject']['search-subject-container']['search-subject-form'] = $searchSubjectForm;

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

      $addNewSubjectForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\AddNewSubjectForm');
      $form['add-subject']['add-subject-container']['add-subject-form'] = $addNewSubjectForm;


  // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

      // Add the curriculum forms css styles
      $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

      // Add the core AJAX library.
      // Important for ajax features
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