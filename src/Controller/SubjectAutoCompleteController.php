<?php

namespace Drupal\sedm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;

use Drupal\sedm\Database\CurriculumDatabaseOperations;
use Drupal\sedm\Database\EvaluationDatabaseOperations;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class SubjectAutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    
    $results = [];
    $CDO = new CurriculumDatabaseOperations();

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      $subjects = $CDO->getSubjectsByKeyword($typed_string);
      for ($i = 0; $i < count($subjects); $i++) {
        $results[] = [
          'value' => $subjects[$i]->subject_uid . ' '.$subjects[$i]->subject_code . ' - ' . $subjects[$i]->subject_desc,
          'label' => $subjects[$i]->subject_code . '- (' . $subjects[$i]->subject_desc . ')',
        ];
      }
    }

    return new JsonResponse($results);
  }

  public function handleAutocompleteRequestedSubject(Request $request) {
    
    $results = [];
    $additionalSubjs = [];
    if(isset($_SESSION['sedm']['studInfo'])){
      $info = $_SESSION['sedm']['studInfo'];
    }

    if(isset($_SESSION['sedm']['curri_uid'])){
      $curri_uid = $_SESSION['sedm']['curri_uid'];
    }

    $EDO = new EvaluationDatabaseOperations();
    // $availableSubjects = $EDO->getAvailableSubjectsByKeyword($info, $curri_uid);
    // $additionalSubjs[] = $this->getWithIssueAvailableSubjects($availableSubjects['regularSubjs']);
    // $additionalSubjs[] = $this->getAlternativeSubjects($availableSubjects['alternativeSubjs']);
    
    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      $availableSubjects = $EDO->getAvailableSubjectsByKeyword($info, $curri_uid, $typed_string);
      $additionalSubjs[] = $this->getWithIssueAvailableSubjects($availableSubjects['regularSubjs']);
      $additionalSubjs[] = $this->getAlternativeSubjects($availableSubjects['alternativeSubjs']);
      // var_dump($additionalSubjs);
      for ($i = 0; $i < count($additionalSubjs); $i++) {

        for($x = 0; $x < count($additionalSubjs[$i]); $x++){
          // var_dump($additionalSubjs[$i][]);
          $results[] = [
            'value' => $additionalSubjs[$i][$x]['subj_uid'] . ' '.$additionalSubjs[$i][$x]['subj_code'].' - '.$additionalSubjs[$i][$x]['subj_description'],
            'label' => $additionalSubjs[$i][$x]['subj_code'] . '- (' . $additionalSubjs[$i][$x]['subj_description'] . ')',
          ];
        }
       
      }
    }

    return new JsonResponse($results);
  }

  protected function getWithIssueAvailableSubjects($subjects){
      $nonAvailable = [];
      $result = array();
      if(empty($subjects)){
          $nonAvailable = NULL;
      }
      else {
          foreach($subjects as $subject => $key){
              // var_dump($key);
              
              if($key['reason'] == "ISSUES"){
                
                  $nonAvailable[] = [
                    'subj_uid' => $key['subj_uid'],
                    'subj_code' => $key['subj_code'],
                    'subj_description' => $key['subj_description'],
                  ];
                
              }
          }
      }

      return $nonAvailable;

  }

  protected function getAlternativeSubjects($subjects){
      $alternatives = [];
      
      if(empty($subjects)){
          $alternatives = NULL;
      }
      else {
          $i = 0;
          foreach($subjects as $subject => $key){
              // var_dump($key);

              $i += 1;
              if($key['reason'] == "OK" || $key['reason'] == "INCOMPLETE" || 
                  $key['reason'] == "FAILED" ) {
                  $alternatives[] = [
                    'subj_uid' => $key['subj_uid'],
                    'subj_code' => $key['subj_code'],
                    'subj_description' => $key['subj_description'],
                  ];
              }

          }
      }

      return $alternatives;
  }

}