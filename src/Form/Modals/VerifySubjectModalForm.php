<?php

namespace Drupal\sedm\Form\Modals;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\sedm\Database\DatabaseOperations;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\sedm\Form\Templates\Curriculum\SubjectsTab\AddNewSubjects; 

class VerifySubjectModalForm extends FormBase {


        /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        // Create a new form object and inject its services.
        $form = new static();
        $form->setRequestStack($container->get('request_stack'));
        $form->setStringTranslation($container->get('string_translation'));
        $form->setMessenger($container->get('messenger'));
        return $form;
    }

        /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'curriculum_subj_tab_verify_modal_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $subject = array()) {

        $output = $this->t('Code: @code, Units: @units, isElective: @isElective, isActive: @isActive', [
            '@code' => $subject['code'],
            '@units' => $subject['units'],
            '@isElective' => $subject['isElective'] ? 'elective' : 'not elective',
            '@isActive' => $subject['isActive'] ? 'active' : 'not active',
            ]
        );

        $form['modal-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="modal-container-wrapper">',
            '#suffix' => '</div>'
        ];

        $form['modal-container']['verify'] = [
            '#type' => 'item',
            '#markup' => $this->t('Are you sure you want to add the subject?'),
        ];

        $form['modal-container']['fieldset'] = [
            '#type' => 'fieldset',
            '#title' => 'Subject Info',
        ];

        $form['modal-container']['fieldset']['initial-info'] = [
            '#type' => 'item',
            '#markup' => $output,
        ];

        $form['modal-container']['fieldset']['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('Description: @description', ['@description' => $subject['description']]),
        ];

        $form['modal-container']['actions'] = [
            '#type' => 'actions',
        ];

        // Add a submit button that handles the submission of the form.
        $form['modal-container']['actions']['yes'] = [
            '#type' => 'button',
            '#value' => $this->t('Yes'),
            '#attributes' => [
              'class' => ['use-ajax',],
              'data-dialog-type' => 'modal',
            ],
            '#ajax' => [
              'callback' => '::proceedSubjectAdding',
              'wrapper' => 'modal-container-wrapper',
              'event' => 'click',
            ],
        ];

        // add the cancel button that handles the closing of modal
        // and cancel verification of subject
        $form['modal-container']['actions']['no'] = [
            '#type' => 'button',
            '#value' => $this->t('No'),
            // '#attributes' => [
            //   'class' => ['use-ajax',],
            //   'data-dialog-type' => 'modal',
            // ],
            '#ajax' => [
              'callback' => '::cancelSubjectAdding',
              'wrapper' => 'modal-container-wrapper',
              'event' => 'click',
            ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;

    }


    public function proceedSubjectAdding(array &$form, FormStateInterface $form_state){


        $addNewSubject = new AddNewSubjects();

        $dumpTest = 'Test for debugging';
    
        $data = $form_state->getTriggeringElement()['#data']; 
    
        $result = $addNewSubject->addSubject($data);
    
    }


    public function cancelSubjectAdding(array $form, FormStateInterface $form_state){
    
        $response = new AjaxResponse();
    
        // $response->addCommand(new CloseModalDialogCommand());
        $response->addCommand(new CloseDialogCommand('#verify-subject-dialog'));
    
        return $response;
    
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