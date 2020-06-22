<?php

namespace Drupal\sedm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Url;
use Drupal\Core\Link;

use Drupal\sedm\Database\CurriculumDatabaseOperations;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class EnrollmentEvaluationModalController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function verifyAddingSubject(Request $request) {
    
    $response = new AjaxResponse();
    $selectedSubj = $form_state->getValue(['form-container','subject-table-container','subjectsAvailable','selectSubject']);
    // var_dump($selectedSubj);
    $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\VerifyCurriculumToSaveModalForm');
    $command = new OpenModalDialogCommand($this->t('Add subject'), $modal_form, ['width' => '50%']);

    $response->addCommand($command);

    return $response;
  }


}