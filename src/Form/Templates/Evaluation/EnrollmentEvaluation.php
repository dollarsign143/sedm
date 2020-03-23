<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\StringTranslation\StringTranslationTrait;

class EnrollmentEvaluation {
    use StringTranslationTrait;

/**
 * @Public function getTemplForm : this method will return the initial form
 * of the calling tab
 */
public function getTemplForm(){

    $form['form-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="enrollment-eval-form-container-wrapper">',
        '#suffix' => '</div>',
    ];

    $form['form-container']['form-title'] = [
        '#type' => 'item',
        '#markup' => $this->t('<h2>Enrollment Evaluation</h2>'),
    ];

    $form['form-container']['student'] = [
        '#type' => 'fieldset',
        '#title' => 'Student Info.'
    ];

    $form['form-container']['student']['notice-container'] = [
        '#type' => 'container',
    ];

    $form['form-container']['student']['details-container'] = [
        '#type' => 'container'
    ];

    $form['form-container']['student']['details-container']['idNumber'] = [
        '#type' => 'textfield',
        '#title' => 'Id number',
        '#attributes' => [
            'class' => ['flat-input',],
            'placeholder' => $this->t('2015-0001'),
        ],
    ];

    $form['form-container']['student']['details-container']['select_container'] = [
        '#type' => 'container',
        '#attributes' => [
            'class' => ['inline-container-col2',],
        ],
    ];

    $form['form-container']['student']['details-container']['select_container']['yearLevel'] = [
        '#type' => 'select',
        '#title' => 'Year Level',
        '#options' => [
            '1' => 'First Year',
            '2' => 'Second Year',
            '3' => 'Third Year',
            '4' => 'Fourth Year',
            '5' => 'Fifth Year',
        ],
        '#attributes' => [
            'class' => ['flat-element',],
        ],
    ];

    $form['form-container']['student']['details-container']['select_container']['semester'] = [
        '#type' => 'select',
        '#title' => 'Select Semester',
        '#options' => [
            '1' => 'First Semester',
            '2' => 'Second Semester',
            '3' => 'Summer',
        ],
        '#attributes' => [
            'class' => ['flat-element',],
        ],
    ];

    $form['form-container']['student']['details-container']['evaluate'] = [
        '#type' => 'submit',
        '#value' => 'Evaluate Subjects',
        '#tabCaller' => 'enrollmentEval',
        '#attributes' => [
            'class' => ['flat-btn',],
        ],
        '#ajax' => [
            'callback' => '::searchAvailableSubjects',
            'wrapper' => 'enrollment-eval-form-container-wrapper', 
            'event' => 'click',
        ]
    ];

    $form['form-container']['subject-table-container'] = [
        '#type' => 'container',
        '#prefix' => '<div id="subj-table-container-wrapper">',
        '#suffix' => '</div>',
    ];

    return $form;
}

public function getSubjectSearchTemplResponse(){

    $form['subjectsAvailable'] = [
        '#type' => 'details',
        '#title' => $this->t('Advisable Subjects'),
        '#open' => TRUE,
    ];

    $form['subjectsAvailable']['description'] = [
        '#type' => 'item',
        '#markup' => $this->t('The subjects listed below are advisable to enroll.'),
    ];

    $form['subjectsAvailable']['table'] = [
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

    return $form;

}
    
}

?>