<?php

namespace Drupal\sedm\Form\Templates\Curriculum\SubjectsTab;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\sedm\Database\DatabaseOperations;


class AddNewSubjects {
    use StringTranslationTrait;

    /**
     * @Public function getTemplForm : this method will return the initial form
     * of the calling tab
     * returns $form
     */
    public function getTemplForm(){

        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="add-subject-form-container-wrapper">',
            '#suffix' => '</div>',
          ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Add New Subject</h2>'),
        ];

        $form['form-container']['subject-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Subject Info.'
        ];

        $form['form-container']['subject-details-container']['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('Fill out all the required details.'),
        ];

        /**
         * @RenderElement container : container is the wrapper 
         * of the college and department select render elements
         */

        $form['form-container']['subject-details-container']['select-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="subj-details-select-container-wrapper">',
            '#suffix' => '</div>',
        ];
  
      /**
       * @Variable $dbOperations = object to hold DatabaseOperations class
       * @Variable $colleges = object to hold the result of the query
       * @Variable array $collegeOpt : holds the custom layout of every college
       *      for select render element
       */
      $dbOperations = new DatabaseOperations(); // instantiate DatabaseOperations Class
      $colleges = $dbOperations->getColleges(); // get colleges
      $collegeOpt = array();
  
      foreach ($colleges as $college) {
  
        $collegeOpt[$college->college_uid] = $college->college_abbrev.' - '.$college->college_name;
  
      }
  
      /**
       * @RenderElement select: this element will trigger the 
       * selection of department
       */
      $form['form-container']['subject-details-container']['select-container']['college'] = [
        '#type' => 'select',
        '#title' => $this->t('College'),
        '#options' => $collegeOpt,
        '#required' => TRUE,
        '#attributes' => array('class' => array('flat-input')),
        '#ajax' => [
          'callback' => '::buildDepartment',
          'wrapper' => 'subj-details-select-container-wrapper',
        ],
      ];

          /**
     * @RenderElement container : this is the container of
     * all the render elements that are inline in style
     * ::subj-code
     * ::subj-units
     * ::subj-lecture-hrs
     * ::subj-lab-hrs
     */

    $form['form-container']['subject-details-container']['inline-container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['inline-container-col2'],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-code'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject Code'),
        '#maxlength' => 10,
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Eng1',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Subject Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-lecture-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Lecture Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-lab-units'] = [
        '#type' => 'number',
        '#title' => $this->t('Laboratory Units'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-lecture-hrs'] = [
        '#type' => 'number',
        '#title' => $this->t('Lecture Hours'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['subj-lab-hrs'] = [
        '#type' => 'number',
        '#title' => $this->t('Laboratory Hours'),
        '#required' => TRUE,
        '#attributes' => [
          'placeholder' => 'Ex. 3',
          'class' => ['flat-input', ],
        ],
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['isElective'] = [
        '#type' => 'checkbox',
        '#title' => 'Set as Elective',
        '#return_value' => 'elective',
      ];
  
      $form['form-container']['subject-details-container']['inline-container']['isActive'] = [
        '#type' => 'checkbox',
        '#title' => 'Set as Active',
        '#return_value' => 'active',
      ];

      $form['form-container']['subject-details-container']['subj-description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject Description'),
        '#size' => 60,
        '#maxlength' => 100,
        '#required' => TRUE,
        '#attributes' => [
            'class' => ['flat-input',],
            'placeholder' => 'Subject Description',
        ],
      ];
  
  
      // Group submit handlers in an actions element with a key of "actions" so
      // that it gets styled correctly, and so that other modules may add actions
      // to the form.
      // $form['form-container']['actions'] = [
      //   '#type' => 'actions',
      // ];
  
      // // Add a submit button that handles the submission of the form.
      $form['form-container']['actions']['submit'] = [
        '#type' => 'button',
        '#value' => $this->t('Submit'),
        '#ajax' => [
          'callback' => '::verifySubject',
          'event' => 'click',
        ],
      ];
  

      return $form;

    }

    public function addSubject($subject){

      $dbOperations = new DatabaseOperations(); // instantiate DatabaseOperations Class

      // if($dbOperations->addNewSubject($subject)){
      //   return true;
      // } 
      // else {
      //   return false;
      // }

      $result = $dbOperations->addNewSubject($subject);

      return $result ? true : false;

    }


}

?>

