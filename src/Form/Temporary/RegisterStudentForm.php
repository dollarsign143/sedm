<?php

namespace Drupal\sedm\Form\Temporary;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\TemporaryDatabaseOperations;

class RegisterStudentForm extends FormBase {
        /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_temporary_menu_register_student';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="register-new-student-form-container-wrapper">',
            '#suffix' => '</div>'
        ];

        $form['form-container']['status-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['stud-info'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Fill student info.')
        ];

        $form['form-container']['stud-info']['school'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['inline-container-col3',],
            ],
        ];

        $form['form-container']['stud-info']['school']['stud_idNumber'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Student ID Number'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => 'Ex. 2015-0001',
            ],
        ];

        $form['form-container']['stud-info']['school']['stud_yearLevel'] = [
            '#type' => 'select',
            '#title' => $this->t('Student Year Level'),
            '#required' => TRUE,
            '#options' => [
                '1st' => 'First Year',
                '2nd' => 'Second Year',
                '3rd' => 'Third Year',
                '4th' => 'Fourth Year',
                '5th' => 'Fifth Year'
            ],
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['stud-info']['personal'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['inline-container-col3',],
            ],
        ];

        $form['form-container']['stud-info']['personal']['lname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Last Name'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => 'Last Name',
            ],
        ];

        $form['form-container']['stud-info']['personal']['fname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('First Name'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => 'First Name',
            ],
        ];

        $form['form-container']['stud-info']['personal']['mname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Middle Name'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => 'Middle Name',
            ],
        ];

        $form['form-container']['stud-info']['personal']['age'] = [
            '#type' => 'number',
            '#title' => $this->t('Age'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['stud-info']['personal']['gender'] = [
            '#type' => 'select',
            '#title' => $this->t('Gender'),
            '#required' => TRUE,
            '#options' => [
                'male' => $this->t('Male'),
                'female' => $this->t('Female'),
            ],
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['actions'] = [
            '#type' => 'actions'
        ];

        $form['form-container']['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::registerStudent',
                'wrapper' => 'register-new-student-form-container-wrapper', 
                'event' => 'click',
            ]
        ];

        // Add the curriculum forms css styles
        $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

        // Add the core AJAX library.
        // Important for ajax features
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }


    public function registerStudent(array &$form, FormStateInterface $form_state){

        $response = new AjaxResponse();

        if($form_state->getErrors()){
            $form['form-container']['status-container']['status_messages'] = [
                '#type' => 'status_messages'
            ];

            return $form['form-container'];
        }
        else {

            $stud_info['id_number'] = $form_state->getValue(['form-container','stud-info','school','stud_idNumber']);
            $stud_info['year_level'] = $form_state->getValue(['form-container','stud-info','school','stud_yearLevel']);
            $stud_info['last_name'] = $form_state->getValue(['form-container','stud-info','personal','lname']);
            $stud_info['first_name'] = $form_state->getValue(['form-container','stud-info','personal','fname']);
            $stud_info['middle_name'] = $form_state->getValue(['form-container','stud-info','personal','mname']);
            $stud_info['age'] = $form_state->getValue(['form-container','stud-info','personal','age']);
            $stud_info['gender'] = $form_state->getValue(['form-container','stud-info','personal','gender']);

            $TDO = new TemporaryDatabaseOperations();
            $isStudAlreadyReg = $TDO->isStudentAlreadyRegistered($stud_info['id_number']);

            if(isStudAlreadyReg){
                $modal_form['message'] = [
                    '#type' => 'item',
                    '#markup' => $this->t('Student is already registered!'),
                ];
            }
            else {
                $_SESSION['sedm']['subject'] = $subject; // final approach to be made
                $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifySubjectModalForm', $subject);
            }

            $command = new OpenModalDialogCommand($this->t('Add new Student'), $modal_form, ['width' => '50%']);

            $response->addCommand($command);
      
            return $response;

        }

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