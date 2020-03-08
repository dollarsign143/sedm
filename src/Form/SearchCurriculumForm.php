<?php

namespace Drupal\sedm\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
// use Drupal\subject_evaluation\Database\DatabaseOperations;

/**
 * Our simple form class.
 */
class SearchCurriculumForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'subject_evaluation_search_curriculum_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['curriculum'] = [
      '#type' => 'fieldset',
      '#title' => 'Curriculum Info.'
    ];

    $form['curriculum']['notice'] = [
      '#type' => 'item',
      '#markup' => $this->t('<div id="noticeMessage"></div>'),
    ];


    $form['curriculum']['container'] = [
      '#type' => 'container',
      '#attributes' => array('class' => array('search-curr-select-container')),
    ];
    $form['curriculum']['container']['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Course'),
      '#options' => [
        '1' => $this->t('BSIT'),
        '2' => $this->t('BSEE'),
        '3' => $this->t('DCT'),
        '4' => $this->t('EET'),
      ],
      '#attributes' => array('class' => array('inline-select')),
    ];

    $form['curriculum']['container']['year'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Year'),
      '#options' => [
        '2015' => $this->t('2015'),
        '2016' => $this->t('2016'),
        '2017' => $this->t('2017'),
        '2018' => $this->t('2018'),
        '2019' => $this->t('2019'),
      ],
      '#attributes' => array('class' => array('inline-select')),
    ];

    $form['curriculum']['search'] = [
      '#type' => 'button',
      '#value' => $this->t('Search'),
      '#attributes' => array('id' => array('curr-search-button')),
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];

    // Subjects Available Sector
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



    // Alternative Subjects Sector
    $form['subjectsAlternative'] = [
      '#type' => 'details',
      '#title' => $this->t('Alternative Subjects'),
    ];

    $form['subjectsAlternative']['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('The subjects listed below are alternatives to enroll.'),
    ];

    $form['subjectsAlternative']['table'] = [
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
          <tbody class="subjectsAlternativeBody">
          </tbody>
        </table>
      </div>'),
    ];

    $form['#attached']['library'][] = 'subject_evaluation/curriculum.forms.styles';

    return $form;
  }

  public function setMessage(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $dbOperations = new DatabaseOperations();

    $response->addCommand(
      new InvokeCommand(
        '#noticeMessage',
        'css',
        array(['display' => 'none'])
      )
    );

    $response->addCommand(
      new HtmlCommand(
        '.subjectsAvailableBody',
        ''
      )
    );

    if($form_state->getValue('idNumber') == null){

      $response->addCommand(
        new HtmlCommand(
          '#noticeMessage',
          '<center><h3 style="color:white;">Error! Id Number is Empty</h3></center>'
        )
      );

      $response->addCommand(
        new InvokeCommand(
          '#noticeMessage',
          'css',
          array(['display' => 'block', 'background-color' => 'red', 'padding' => '1%'])
        )
      );
      
    }
    else {

      $subjects = $dbOperations->displaySubjects();
  
      $html = '';
  
  
        foreach($subjects as $subject){
          $html .= $this->t('
            <tr>
              <td>'.$subject->course_name.'</td>
              <td>'.$subject->course_abrev.'</td>
              <td>2.5</td>
            </tr>
          ');
        }

        $response->addCommand(
          new HtmlCommand(
            '.subjectsAvailableBody',
            $html
          )
        );

    }
    

    return $response;
  }

  /** 
   * Test method to rebuild form upon querying data
  */
  public function getSubjects(array &$form, FormStateInterface $form_state){

    $dbOperations = new DatabaseOperations();

    $subjects = $dbOperations->displaySubjects();

    foreach ($subjects as $subject) {

      $form['subjectsAvailable']['table'][$subject->course_uid]['label'] = array(
        '#plain_text' => $subject->course_name,
      );

    }

    return $form['subjectsAvailable']['table'];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

