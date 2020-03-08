<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
// use Drupal\subject_evaluation\Database\DatabaseOperations;

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

      return $form;
  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
}

}

?>