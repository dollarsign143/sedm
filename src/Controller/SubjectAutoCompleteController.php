<?php

namespace Drupal\sedm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;

use Drupal\sedm\Database\CurriculumDatabaseOperations;

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
      // @todo: Apply logic for generating results based on typed_string and other
      // arguments passed.
      for ($i = 0; $i < count($subjects); $i++) {
        $results[] = [
          'value' => $subjects[$i]->subject_uid . ' '.$subjects[$i]->subject_code . ' - ' . $subjects[$i]->subject_desc,
          'label' => $subjects[$i]->subject_code . '- (' . $subjects[$i]->subject_desc . ')',
        ];
      }
    }

    return new JsonResponse($results);
  }

}