<?php

namespace Drupal\sedm\Form\Modals\Temporary;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\TemporaryDatabaseOperations;

class VerifyInsertSubjectGradeModalForm extends FormBase {
  use LoggerChannelTrait;

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_temporary_menu_verify_insert_subject_grade';
    }

        /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $stud_info = array()) {

        $logger = $this->getLogger('sedm');
        
        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => $this->t('<div id="subject-grades-container-wrapper">'),
            '#suffix' => $this->t('</div>'),
        ];

        $subj_info = $_SESSION['sedm']['temp_subj_info'];
        preg_match('/(?P<digit>\d+)/', $subj_info['subject_uid'], $subject_uid);
        
        $TDO = new TemporaryDatabaseOperations();
        $subject_info = $TDO->getSubjectInfo($subject_uid[0]);

        $form['form-container']['subject_description'] = [
          '#type' => 'textfield',
          '#title' => 'Subject Description',
          '#default_value' => $subject_info[0]->subject_code.' - '.$subject_info[0]->subject_desc,
          '#disabled' => TRUE,
          '#attributes' => [
            'class' => ['flat-input',],
          ],
        ];  
        
        $form['form-container']['grade-container'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['inline-container-col2',],
          ],
        ];

        $form['form-container']['grade-container']['remarks'] = [
          '#type' => 'textfield',
          '#title' => 'Remarks',
          '#default_value' => $stud_info['remarks'],
          '#disabled' => TRUE,
          '#attributes' => [
            'class' => ['flat-input',],
          ],
        ];

        $form['form-container']['grade-container']['final-remarks'] = [
          '#type' => 'textfield',
          '#title' => 'Final Remarks',
          '#default_value' => $stud_info['final_remarks'],
          '#disabled' => TRUE,
          '#attributes' => [
            'class' => ['flat-input',],
          ],
        ];

        $form['form-container']['action'] = [
          '#type' => 'action'
        ];

        $form['form-container']['action']['proceed'] = [
          '#type' => 'button',
          '#value' => $this->t('Proceed'),
          '#attributes' => [
            'class' => ['use-ajax',],
          ],
          '#ajax' => [
            'callback' => '::proceedAddingSubject',
            'event' => 'click',
            'url' => Url::fromRoute('sedm.menu.temporary.verify.insert.subject.grade.modal.form'),
            'options' => ['query' => ['ajax_form' => 1]],
           ],
        ];

        $form['form-container']['action']['cancel'] = [
          '#type' => 'button',
          '#value' => $this->t('No'),
          '#attributes' => [
            'class' => ['use-ajax',],
          ],
          '#ajax' => [
            'callback' => '::closeModalDialog',
            'event' => 'click',
            'url' => Url::fromRoute('sedm.menu.temporary.verify.insert.subject.grade.modal.form'),
            'options' => ['query' => ['ajax_form' => 1]],
           ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
        $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

        return $form;
    }

    public function proceedAddingSubject(array &$form, FormStateInterface $form_state){

        $logger = $this->getLogger('sedm');
        $response = new AjaxResponse();
        $command = new CloseModalDialogCommand();
        $response->addCommand($command);
    
        $TDO = new TemporaryDatabaseOperations(); // instantiate DatabaseOperations Class
        $subj_info = $_SESSION['sedm']['temp_subj_info'];
        $stud_info = $TDO->getStudentInfo($subj_info['id_number']);
        $isSubjectAlreadyHaveGrade = $TDO->checkSubjectOnStudentSubjects($stud_info[0]->student_uid, $subj_info['subject_uid']);

        if($isSubjectAlreadyHaveGrade){
            $logger->info('subject has grade');
            $result = $TDO->updateStudentSubjectGrade($subj_info, $stud_info[0]->student_uid);
            if($result){
              $content['message'] = [
                '#type' => 'item',
                '#markup' => $this->t('Subject grade has been updated successfully!'),
              ];
              unset($_SESSION['sedm']['temp_subj_info']);
            }
            else {
              $content['message'] = [
                '#type' => 'item',
                '#markup' => $this->t('ERROR! Failed to insert the grade. Please check the error logs!'),
              ];
              unset($_SESSION['sedm']['temp_subj_info']);
            }
        }
        else {
            
            $logger->info('subject has no grade');
            $result = $TDO->insertStudentSubjectGrade($subj_info, $stud_info[0]->student_uid);
            if($result){
              $content['message'] = [
                '#type' => 'item',
                '#markup' => $this->t('Subject grade has been inserted successfully!'),
              ];
              unset($_SESSION['sedm']['temp_subj_info']);
            }
            else {
              $content['message'] = [
                '#type' => 'item',
                '#markup' => $this->t('ERROR! Failed to insert the grade. Please check the error logs!'),
              ];
              unset($_SESSION['sedm']['temp_subj_info']);
            }
        }        
        
        $command = new OpenDialogCommand('#success-adding-subject', $this->t('Successful'), $content, ['width' => '50%']);
        $response->addCommand($command);
        return $response;
      }
    
      public function closeModalDialog(array &$form, FormStateInterface $form_state){
    
        $response = new AjaxResponse();
        $command = new CloseModalDialogCommand();
        $response->addCommand($command);
        unset($_SESSION['sedm']['temp_subj_info']);
        return $response;
      }

    /**
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}

?>