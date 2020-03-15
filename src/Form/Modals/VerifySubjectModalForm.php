<?php

namespace Drupal\sedm\Form\Modals;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;

class VerifySubjectModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sedm_menu_curriculum_subjects_tab_verify_subject_modal_form';
  }

    /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#tree'] = TRUE;
    $form['modal-form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="verify-subject-modal-container-wrapper">',
        '#suffix' => '</div>',
    ]; 

    $form['modal-form-container']['sample'] = [
      '#type' => 'item',
      '#markup' => $this->t('I hope this will work!'),
    ];

    $form['modal-form-container']['action'] = [
        '#type' => 'action',
    ];

    $subject = 'test';

    $form['modal-form-container']['action']['submit-yes'] = [
      '#type' => 'button',
      '#value' => $this->t('Yes'),
      '#data' => $subject,
      '#attributes' => [
        'class' => ['use-ajax',],
      ],
      '#ajax' => [
        'callback' => '::proceedAddingSubject',
        'event' => 'click',
        'url' => Url::fromRoute('sedm.menu.curriculum.subjects.tab.verify.subject.modal.form'),
        'options' => ['query' => ['ajax_form' => 1]],
       ],
    ];

    $form['modal-form-container']['action']['submit-no'] = [
        '#type' => 'button',
        '#value' => $this->t('No'),
        '#attributes' => [
          'class' => ['use-ajax',],
        ],
        '#ajax' => [
          'callback' => '::closeModalDialog',
          'event' => 'click',
          'url' => Url::fromRoute('sedm.menu.curriculum.subjects.tab.verify.subject.modal.form'),
          'options' => ['query' => ['ajax_form' => 1]],
         ],
    ];

    $form['#attached']['library'][] = 'subject_evaluation/curriculum.forms.styles';


    return $form;
  }

  public function proceedAddingSubject(array $form, FormStateInterface $form_state){
    $response = new AjaxResponse();
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);
    $content['message'] = [
      '#type' => 'item',
      '#markup' => $this->t('Subject has been added successfully!'),
    ];
    $command = new OpenDialogCommand('#success-adding-subject', $this->t('Successful'), $content, ['width' => '50%']);
    $response->addCommand($command);
    return $response;
  }

  public function closeModalDialog(array $form, FormStateInterface $form_state){

    $response = new AjaxResponse();
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }


}

?>