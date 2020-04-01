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
    $form['evaluation_menu'] = array(
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-enrollment-eval',
      );

      $form['enrollment_eval'] = array(
        '#type' => 'details',
        '#title' => $this->t('Enrollment Evaluation'),
        '#group' => 'evaluation_menu',
      );

      $form['enrollment_eval']['enrollment-eval-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="enrollment-eval-container-wrapper">',
        '#suffix' => '</div>',
      ];

      // $enrollmentEval = new EnrollmentEvaluation();
      $form['enrollment_eval']['enrollment-eval-container']['enrollment-eval-form'] = $enrollmentEval->getTemplForm();

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

      $activeSubjects = new ActiveSubjects();
      $form['active_subjects']['active-subjects-container']['active-subjects-form'] = $activeSubjects->getTemplForm();


      $form['eval_for_graduation'] = array(
        '#type' => 'details',
        '#title' => $this->t('Evaluation for Graduation'),
        '#group' => 'evaluation_menu',
      );

      $form['eval_for_graduation']['eval-for-graduation-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="eval-for-graduation-container-wrapper">',
        '#suffix' => '</div>',
      ];

      $evalForGrad = new EvaluationForGraduation();
      $form['eval_for_graduation']['eval-for-graduation-container']['eval-for-grad-form'] = $evalForGrad->getTemplForm();

      $form['#attached']['library'][] = 'sedm/evaluation.forms.styles';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

      return $form;
  }


  /**
   * @function searchAvailableSubjects : this will fetch the query response 
   * template
   */
  public function searchAvailableSubjects(array &$form, FormStateInterface $form_state){


    if($form_state->getErrors()){

      $form['enrollment_eval']['enrollment-eval-container']
      ['enrollment-eval-form']['form-container']['student']
      ['notice-container']['status_messages'] = [
        '#type' => 'status_messages',
      ];
    }
    // this condition will append the subjects table
    else{

      $enrollmentEval = new EnrollmentEvaluation();
      $form['enrollment_eval']['enrollment-eval-container']
      ['enrollment-eval-form']['form-container']
      ['subject-table-container'] = $enrollmentEval->getSubjectSearchTemplResponse();

    }

    return $form['enrollment_eval']['enrollment-eval-container']
    ['enrollment-eval-form']['form-container'];

  }

  /**
   * @function buildDepartment : this will append the department selection
   * for Active Subjects Tab
   */
  public function displayActiveSubjects(array &$form, FormStateInterface $form_state){

      // get the value of selected college
      $college = $form_state->getValue([
        'active_subjects', 'active-subjects-container', 
        'active-subjects-form','form-container','subject-details-container',
        'college-container','college-select',
      ]);

      $activeSubjects = new ActiveSubjects();

      $form['active_subjects']['active-subjects-container']
      ['active-subjects-form']['form-container']['subject-details-container']
      ['subjects-table-container'] = $activeSubjects->getActiveSubjectsTemplForm($college);

      return $form['active_subjects']['active-subjects-container']
      ['active-subjects-form']['form-container'];

  }

  public function evaluateStudent(array &$form, FormStateInterface $form_state){

    // this condition will return errors to the form if there are
    if($form_state->getErrors()){

      $form['eval_for_graduation']['eval-for-graduation-container']
      ['eval-for-grad-form']['form-container']['student-details-container']
      ['notice-container']['status_messages'] = [
        '#type' => 'status_messages',
      ];

    }
    else {

      $studIdNumber = $form_state->getValue(['eval_for_graduation','eval-for-graduation-container',
      'eval-for-grad-form','form-container','student-details-container','id-container','id-number']);

      $evalForGrad = new EvaluationForGraduation();
  
      // $form['eval_for_graduation']['eval-for-graduation-container']
      //     ['eval-for-grad-form']['form-container']
      //     ['eval-sheet-container'] = $evalForGrad->getStudentEvaluatedSubjects($studIdNumber);

      $form['eval_for_graduation']['eval-for-graduation-container']
      ['eval-for-grad-form']['form-container']['eval-sheet-container']['eval-sheet'] = [
        '#type' => 'details',
        '#title' => $this->t('Student\'s Subjects'),
        '#open' => TRUE,
      ];
  
      $form['eval_for_graduation']['eval-for-graduation-container']
      ['eval-for-grad-form']['form-container']['eval-sheet-container']['eval-sheet']['description'] = [
          '#type' => 'item',
          '#markup' => $this->t('The subjects listed below are evaluated.'),
      ];
  
      $form['eval_for_graduation']['eval-for-graduation-container']
      ['eval-for-grad-form']['form-container']['eval-sheet-container']['eval-sheet']['table'] = [
          '#type' => 'markup',
          '#markup' => $this->t('
          <div>
              <table>
              <thead>
                  <tr>
                  <th>Subject Name</th>
                  <th>Units</th>
                  <th>Remarks</th>
                  </tr>
              </thead>
              <tbody class="subjectsAvailableBody">
  
              </tbody>
              </table>
          </div>'),
      ];

    }

    return $form['eval_for_graduation']['eval-for-graduation-container']
    ['eval-for-grad-form']['form-container'];

  }


    /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $tabCaller = $data = $form_state->getTriggeringElement()['#tabCaller'];

    if($tabCaller == 'enrollmentEval'){

      $idNumber = $form_state->getValue(['enrollment_eval','enrollment-eval-container',
      'enrollment-eval-form','form-container','student','details-container','idNumber']);
      if(empty($idNumber)){
        $form_state->setError($form, $this->t('ID number is empty!'));
      }

    }
    elseif($tabCaller == 'evalForGrad'){
      $idNumber = $form_state->getValue(['eval_for_graduation','eval-for-graduation-container',
      'eval-for-grad-form','form-container','student-details-container','id-container','id-number']);

      if(empty($idNumber)){
        $form_state->setError($form, $this->t('ID number is empty!'));
      }
    }

  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

?>