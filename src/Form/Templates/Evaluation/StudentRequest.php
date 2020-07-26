<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\EvaluationDatabaseOperations;

class StudentRequest extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_student_request';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="student-request-form-container-wrapper">',
            '#suffix' => '</div>',
          ];
        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Student Request</h2>'),
        ];

        $form['form-container']['notice-container'] = [
            '#type' => 'container',
        ];

        $form['form-container']['request-info-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Request Info.'
        ];

        /**
         * @Variable $dbOperations = object to hold DatabaseOperations class
         * @Variable $colleges = object to hold the result of the query
         * @Variable array $collegeOpt : holds the custom layout of every college
         *      for select render element
         */
        $EDO = new EvaluationDatabaseOperations(); // instantiate EvaluationDatabaseOperations Class
        $colleges = $EDO->getColleges(); // get colleges
        $collegeOpt = array();

        foreach ($colleges as $college) {

            $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;

        }

        $form['form-container']['request-info-container']['student_id_number'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Student Id Number'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['request-info-container']['subject'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Subject'),
            '#autocomplete_route_name' => 'sedm.autocomplete.subjects',
            '#placeholder' => $this->t('Input subject code or description'),
            '#attributes' => [
                'class' => ['flat-element'],
            ],
        ];

        $form['form-container']['request-info-container']['allow'] = [
            '#type' => 'submit',
            '#value' => $this->t('Allow Request'),
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::requestSubject',
                'wrapper' => 'student-request-form-container-wrapper',
                'event' => 'click',
            ],
        ];

        $form['form-container']['result'] = [
            '#type' => 'container',
        ];

        // Add the curriculum forms css styles
        $form['#attached']['library'][] = 'sedm/evaluation.forms.styles';

        // Add the core AJAX library.
        // Important for ajax features
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;

    }

    public function requestSubject(array &$form, FormStateInterface $form_state){

        $form['form-container']['result']['message'] = [
            '#type' => 'item',
            '#markup' => $this->t("result test")
        ];

        return $form['form-container'];

    }

    
        /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        $studIdNum = $form_state->getValue([
            'form-container', 'request-info-container', 'student_id_number',
        ]);
        $subject = $form_state->getValue([
            'form-container', 'request-info-container', 'subject',
        ]);

        if(empty($studIdNum)){
            $form_state->setErrorByName('Id Number', $this->t("Please enter an id number!"));
        }
        if(empty($subject)){
            $form_state->setErrorByName('Subject', $this->t("Please choose a subject!"));
        }
    }

        /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }

}

?>