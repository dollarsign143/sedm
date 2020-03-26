<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\sedm\Database\DatabaseOperations;


class EvaluationForGraduation {
    use StringTranslationTrait;


        /**
     * @Public function getTemplForm : this method will return the initial form
     * of the calling tab
     * returns $form
     */
    public function getTemplForm(){

        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-for-grad-form-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Evaluation for Graduating Students</h2>'),
        ];

        $form['form-container']['student-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Student Info.'
        ];

        $form['form-container']['student-details-container']['notice-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['student-details-container']['id-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['student-details-container']['id-container']['id-number'] = [
            '#type' => 'textfield',
            '#title' => $this->t('ID Number'),
            '#attributes' => [
                'class' => ['flat-input',],
                'placeholder' => $this->t('2015-0001'),
            ],
        ];

        $form['form-container']['student-details-container']['button-container']['evaluate'] = [
            '#type' => 'submit',
            '#value' => $this->t('Evaluate'),
            '#tabCaller' => 'evalForGrad',
            '#ajax' =>  [
                'callback' => '::evaluateStudent',
                'wrapper' => 'eval-for-grad-form-container-wrapper', 
                'event' => 'click',
            ],
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
        ];

        $form['form-container']['eval-sheet-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-sheet-container-wrapper">',
            '#suffix' => '</div>',
        ];

        return $form;

    }

    public function getStudentEvaluatedSubjects($studID){

        $form['eval-sheet'] = [
          '#type' => 'details',
          '#title' => $this->t('Student\'s Subjects'),
          '#open' => TRUE,
        ];
    
        $form['eval-sheet']['description'] = [
            '#type' => 'item',
            '#markup' => $this->t('The subjects listed below are evaluated.'),
        ];
    
        $form['eval-sheet']['table'] = [
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
        
    }


}
?>