<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\DatabaseOperations;

class ActiveSubjects extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_active_subjects';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="active-subjects-form-container-wrapper">',
            '#suffix' => '</div>',
          ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Active Subjects</h2>'),
        ];

        $form['form-container']['subject-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Subject Info.'
        ];

        $form['form-container']['subject-details-container']['college-container'] = [
            '#type' => 'container'
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

        $form['form-container']['subject-details-container']['college-container']['college-select'] = [
            '#type' => 'select',
            '#title' => $this->t('Select College'),
            '#options' => $collegeOpt,
            '#empty_option' => 'Select College',
            '#attributes' => [
                'class' => ['flat-element',],
            ],
            '#ajax' => [
                'callback' => '::displayActiveSubjects',
                'wrapper' => 'active-subjects-form-container-wrapper',
              ],
        ];

        $form['form-container']['subject-details-container']['subjects-table-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="active-subjects-department-container-wrapper">',
            '#suffix' => '</div>',
        ];

        return $form;

    }

    public function getActiveSubjectsTemplForm($college){

        // instatiate DatabaseOperations Class
        $dbOperations = new DatabaseOperations();
        
        if(!empty($college)){


            $form['subjects-table'] = [
                '#type' => 'details',
                '#title' => $this->t('Active Subjects'),
                '#open' => TRUE,
            ];
        
            $form['subjects-table']['description'] = [
                '#type' => 'item',
                '#markup' => $this->t('The subjects listed below are active an can be enrolled'),
            ];
        
            $form['subjects-table']['table'] = [
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

        return $form;

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