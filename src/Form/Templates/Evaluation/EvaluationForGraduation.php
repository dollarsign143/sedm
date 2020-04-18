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
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\EvaluationDatabaseOperations;


class EvaluationForGraduation extends FormBase {
    use LoggerChannelTrait;
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_eval_for_graduation';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-for-grad-form-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Evaluation for Graduating Students</h2>'),
        ];

        $form['form-container']['student-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Student Info.'
        ];

        $form['form-container']['student-details-container']['notice-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['student-details-container']['id-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['student-details-container']['id-container']['id-number'] = [
            '#type' => 'textfield',
            '#title' => $this->t('ID Number'),
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => $this->t('2015-0001'),
            ],
        ];

        $form['form-container']['student-details-container']['button-container']['evaluateSubjs'] = [
            '#type' => 'submit',
            '#value' => $this->t('Evaluate Subjects'),
            '#ajax' =>  [
                'callback' => '::evaluateStudent',
                'wrapper' => 'eval-for-grad-form-container-wrapper', 
                'event' => 'click',
            ],
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
        ];

        $form['form-container']['eval-sheet-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-sheet-container-wrapper">',
            '#suffix' => '</div>',
        ];

        return $form;

    }


    public function evaluateStudent(array &$form, FormStateInterface $form_state){

        // this condition will return errors to the form if there are
        if($form_state->getErrors()){
    
            $form['form-container']['student-details-container']
            ['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];
    
        }
        else {
    
            $studIdNumber = $form_state->getValue(['form-container','student-details-container','id-container','id-number']);
        
            $EDO = new EvaluationDatabaseOperations();
            $stud_info = $EDO->getStudentInfo($studIdNumber);

            $form['form-container']['eval-sheet-container']['eval-sheet'] = [
                '#type' => 'details',
                '#title' => $this->t('Student\'s Subjects'),
                '#open' => TRUE,
            ];
        
            $form['form-container']['eval-sheet-container']['eval-sheet']['description'] = [
                '#type' => 'item',
                '#markup' => $this->t('The subjects listed below are evaluated.'),
            ];
        
            $form['form-container']['eval-sheet-container']['eval-sheet']['table'] = [
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
    
        return $form['form-container'];
    
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        $idNumber = $form_state->getValue(['form-container','student-details-container','id-container','id-number']);

        if(empty($idNumber)){
            $form_state->setError($form, $this->t('ID number is empty!'));
        }

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }


}
?>