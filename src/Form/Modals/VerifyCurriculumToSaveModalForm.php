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

class VerifyCurriculumToSaveModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sedm_menu_curriculum_default_tab_verify_curriculum_to_save_modal_form';
  }

      /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#tree'] = TRUE;
    $form['modal-form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="verify-curriculum-to-save-modal-container-wrapper">',
        '#suffix' => '</div>',
    ]; 

    $form['modal-form-container']['question'] = [
        '#type' => 'item',
        '#markup' => $this->t('Are you sure to save this curriculum?'),
    ];

    $form['modal-form-container']['actions'] = [
        '#type' => 'action',
    ];

    $form['modal-form-container']['actions']['submit-yes'] = [
        '#type' => 'button',
        '#value' => $this->t('Yes'),
        '#attributes' => [
          'class' => ['use-ajax',],
        ],
        '#ajax' => [
          'callback' => '::proceedSavingCurriculum',
          'event' => 'click',
          'url' => Url::fromRoute('sedm.menu.curriculum.default.tab.verify.curriculum.to.save.modal.form'),
          'options' => ['query' => ['ajax_form' => 1]],
         ],
    ];

    $form['modal-form-container']['actions']['submit-no'] = [
        '#type' => 'button',
        '#value' => $this->t('No'),
        '#attributes' => [
          'class' => ['use-ajax',],
        ],
        '#ajax' => [
          'callback' => '::cancelSavingCurriculum',
          'event' => 'click',
          'url' => Url::fromRoute('sedm.menu.curriculum.default.tab.verify.curriculum.to.save.modal.form'),
          'options' => ['query' => ['ajax_form' => 1]],
         ],
    ];

    return $form;

  }

  public function proceedSavingCurriculum(array &$form, FormStateInterface $form_state){

    $response = new AjaxResponse();
    $CDO = new CurriculumDatabaseOperations();
    
    $curr_infos = $_SESSION['sedm']['curr_infos']; 
    $curr_subjs = $_SESSION['sedm']['curr_subjs'];
    $isCurriculumSaved = $CDO->insertNewCurriculum($curr_infos, $curr_subjs);

    if($isCurriculumSaved) {
      $modal_form['message'] = [
        '#type' => 'item',
        '#markup' => $this->t('Curriculum has been saved successfully!'),
      ];
    }
    else {
      $modal_form['message'] = [
        '#type' => 'item',
        '#markup' => $this->t('Failed to save curriculum! Please try again!')
      ];
    }

    $command = new OpenModalDialogCommand($this->t('Sample'), $modal_form, ['width' => '50%']);
    $response->addCommand($command);

    return $response;

  }

  public function cancelSavingCurriculum(array &$form, FormStateInterface $form_state){

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