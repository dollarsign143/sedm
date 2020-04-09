<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\sedm\Form\Templates\Evaluation\EnrollmentEvaluation;
use Drupal\sedm\Form\Templates\Evaluation\ActiveSubjects;
use Drupal\sedm\Form\Templates\Evaluation\EvaluationForGraduation;


class EvaluationMenu extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sedm_evaluation_menu';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

      $form['#tree'] = TRUE;
      $form['evaluation_menu'] = [
          '#type' => 'vertical_tabs',
          '#default_tab' => 'edit-enrollment-eval',
      ];

      $form['enrollment_eval'] = [
        '#type' => 'details',
        '#title' => $this->t('Enrollment Evaluation'),
        '#group' => 'evaluation_menu',
      ];

      $form['enrollment_eval']['enrollment-eval-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="enrollment-eval-container-wrapper">',
        '#suffix' => '</div>',
      ];

      // $enrollmentEval = new EnrollmentEvaluation();
      $enrollmentEvalForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Evaluation\EnrollmentEvaluation');
      $form['enrollment_eval']['enrollment-eval-container']['enrollment-eval-form'] = $enrollmentEvalForm;

      $form['active_subjects'] = array(
        '#type' => 'details',
        '#title' => $this->t('Active Subjects'),
        '#group' => 'evaluation_menu',
      );

      $form['active_subjects']['active-subjects-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="active-subjects-container-wrapper">',
        '#suffix' => '</div>',
      ];

      // $activeSubjects = new ActiveSubjects();
      $activeSubjectsForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Evaluation\ActiveSubjects');
      $form['active_subjects']['active-subjects-container']['active-subjects-form'] = $activeSubjectsForm;


      $form['eval_for_graduation'] = [
        '#type' => 'details',
        '#title' => $this->t('Evaluation for Graduation'),
        '#group' => 'evaluation_menu',
      ];

      $form['eval_for_graduation']['eval-for-graduation-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="eval-for-graduation-container-wrapper">',
        '#suffix' => '</div>',
      ];

      // $evalForGrad = new EvaluationForGraduation();
      $evalForGradForm = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Templates\Evaluation\EvaluationForGraduation');
      $form['eval_for_graduation']['eval-for-graduation-container']['eval-for-grad-form'] = $evalForGradForm;

      $form['#attached']['library'][] = 'sedm/evaluation.forms.styles';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

?>