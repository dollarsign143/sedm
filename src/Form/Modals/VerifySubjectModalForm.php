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

use Drupal\sedm\Database\CurriculumDatabaseOperations;

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
  public function buildForm(array $form, FormStateInterface $form_state, $subject = array()) {

    $subject = $_SESSION['sedm']['subject'];
    $form['#tree'] = TRUE;
    $form['modal-form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="verify-subject-modal-container-wrapper">',
        '#suffix' => '</div>',
    ]; 

    $form['modal-form-container']['subject-info-fieldset'] = [
      '#type' => 'fieldset',
    ];

    $form['modal-form-container']['subject-info-fieldset']['question'] = [
      '#type' => 'item',
      '#markup' => $this->t('Are you sure you want to add this subject?'),
    ];

    $form['modal-form-container']['subject-info-fieldset']['subject-info-container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['inline-container-col4'],
      ],
    ];
// ++++++++++++++++++++++++++++++++++++++++++++++
    // Subject Code
    $form['modal-form-container']['subject-info-fieldset']['subject-info-container']['subject_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject Code'),
      '#default_value' => $subject['code'],
      '#disabled' => TRUE,
      '#attributes' => [
        'class' => ['flat-input',],
      ],
    ];
    // Subject Description
    $form['modal-form-container']['subject-info-fieldset']['subject-info-container']['subject-desc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject Description'),
      '#default_value' => $subject['description'],
      '#disabled' => TRUE,
      '#attributes' => [
        'class' => ['flat-input',],
      ],
    ];
    $CDO = new CurriculumDatabaseOperations();
    $subj_cat = $CDO->getSubjectCategory($subject['subjCat']);
    
    foreach($subj_cat as $cat){
      $category = $cat->subjCat_name;
    }
    // Subject Category
    $form['modal-form-container']['subject-info-fieldset']['subject-info-container']['subject-cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject Category'),
      '#default_value' => $category,
      '#disabled' => TRUE,
      '#attributes' => [
        'class' => ['flat-input',],
      ],
    ];
// ++++++++++++++++++++++++++++++++++++++++++++++
    // Subject is active
    $form['modal-form-container']['subject-info-fieldset']['subject-info-container']['subject-is-active'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject is Active?'),
      '#default_value' => $subject['isActive'] ? 'active' : 'No',
      '#disabled' => TRUE,
      '#attributes' => [
        'class' => ['flat-input',],
      ],
    ];


    $form['modal-form-container']['action'] = [
        '#type' => 'action',
    ];

    $form_state->set('subject', $subject);

    $form['modal-form-container']['action']['submit-yes'] = [
      '#type' => 'button',
      '#value' => $this->t('Yes'),
      '#data' => ['subject' => $subject],
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

  public function proceedAddingSubject(array &$form, FormStateInterface $form_state){
    $response = new AjaxResponse();
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);

    $CDO = new CurriculumDatabaseOperations(); // instantiate DatabaseOperations Class
    $subj_info = $_SESSION['sedm']['subject'];
    $result = $CDO->addNewSubject($subj_info);
    
    if($result){
      $content['message'] = [
        '#type' => 'item',
        '#markup' => $this->t('Subject has been added successfully!'),
      ];
      unset($_SESSION['sedm']['subject']);
    }
    else {
      $content['message'] = [
        '#type' => 'item',
        '#markup' => $this->t('ERROR! Failed to add the subjec. Please check the error logs!'),
      ];
      unset($_SESSION['sedm']['subject']);
    }
    $command = new OpenDialogCommand('#success-adding-subject', $this->t('Successful'), $content, ['width' => '50%']);
    $response->addCommand($command);
    return $response;
  }

  public function closeModalDialog(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();
    $command = new CloseModalDialogCommand();
    $response->addCommand($command);
    unset($_SESSION['sedm']['subject']);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }


}

?>