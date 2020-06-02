<?php

namespace Drupal\sedm\Form\Temporary;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\TemporaryDatabaseOperations;

class InputStudentGradesForm extends FormBase {
        /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_temporary_menu_input_student_grades';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => $this->t('<div id="input-student-grades-container-wrapper">'),
            '#suffix' => $this->t('</div>')
        ];

        $form['form-container']['message-container'] = [
            '#type' => 'container'
        ];

        $form['form-container']['subject-info'] = [
            '#type' => 'container',
            '#attributes' => [
                'class' => ['inline-container-col2',],
            ],
        ];

        $form['form-container']['subject-info']['stud_idNumber'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Student Id Number'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $subject_opt = $this->buildSubjectOptData();
        $form['form-container']['subject-info']['subject'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Subject'),
            '#autocomplete_route_name' => 'sedm.autocomplete.subjects',
            '#placeholder' => $this->t('Input subject code or description'),
            '#attributes' => [
                'class' => ['flat-element', ],
            ],
        ];

        $form['form-container']['subject-info']['remarks'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Remarks'),
            '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['subject-info']['final-remarks'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Final Remarks'),
            // '#required' => TRUE,
            '#attributes' => [
                'class' => ['flat-input',],
            ],
        ];

        $form['form-container']['action'] = [
            '#type' => 'action'
        ];

        $form['form-container']['action']['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
            '#ajax' => [
                'callback' => '::insertSubjectGrade',
                'wrapper' => 'input-student-grades-container-wrapper', 
                'event' => 'click',
            ]
        ];

        // Add the curriculum forms css styles
        $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

        // Add the core AJAX library.
        // Important for ajax features
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }



    public function buildSubjectOptData(){
    
        
        $TDO = new TemporaryDatabaseOperations();
        $subj_cats = $TDO->getSubjectCategories();
        $subj_opt = array();
        $subj_opt['Default']['none'] = 'None';
        foreach($subj_cats as $subj_cat){
        $subjectsByCat = $TDO->getSubjectByCategory($subj_cat->subjCat_uid);

        foreach($subjectsByCat as $subjectByCat){
            $subj_opt[$subj_cat->subjCat_name][$subjectByCat->subject_uid] = $subjectByCat->subject_code.' - '.$subjectByCat->subject_desc;
        }
        }

        return $subj_opt;

    }

    public function insertSubjectGrade(array &$form, FormStateInterface $form_state){

        $TDO = new TemporaryDatabaseOperations();
        $response = new AjaxResponse();

        if($form_state->getErrors()){
            $form['form-container']['message-container']['status'] = [
                '#type' => 'status_messages'
            ];
            
            return $form['form-container'];
        }
        else {
            $stud_info['id_number'] = $form_state->getValue(['form-container','subject-info','stud_idNumber']);
            $stud_info['subject_uid'] = $form_state->getValue(['form-container','subject-info','subject']);
            $stud_info['remarks'] = $form_state->getValue(['form-container','subject-info','remarks']);
            $stud_info['final_remarks'] = $form_state->getValue(['form-container','subject-info','final-remarks']);

            $isStudentRegistered = $TDO->isStudentAlreadyRegistered($stud_info['id_number']);
            if($isStudentRegistered){
                $_SESSION['sedm']['temp_subj_info'] = $stud_info;
                $modal_form = \Drupal::formBuilder()->getForm('Drupal\sedm\Form\Modals\Temporary\VerifyInsertSubjectGradeModalForm', $stud_info);
            }
            else {
                $modal_form['message'] = [
                    '#type' => 'item',
                    '#markup' => $this->t('Student is not yet registered!'),
                ];
            }

            $command = new OpenModalDialogCommand($this->t('Insert Student Grades'), $modal_form, ['width' => '50%']);

            $response->addCommand($command);
      
            return $response;
        }
    }

        /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }
}

?>