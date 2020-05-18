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
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;

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

      $searchCurriForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Curriculum\DefaultTab\SearchCurriculumForm');
      $form['search-curriculum']['search-curriculum-container']['search-curriculum-form'] = $searchCurriForm;

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

      $registerCurriForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Curriculum\DefaultTab\RegisterCurriculumForm');
      $form['register-curriculum']['register-curriculum-container']['register-curriculum-form'] = $registerCurriForm;

      return $form;
              
        
  }
        
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}

?>