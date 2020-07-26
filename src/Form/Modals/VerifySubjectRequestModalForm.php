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
use Drupal\Core\Logger\LoggerChannelTrait;

use Drupal\sedm\Database\TemporaryDatabaseOperations;

class VerifySubjectRequestModalForm extends FormBase {
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
    public function buildForm(array $form, FormStateInterface $form_state, $requestInfo = array()) {

        $logger = $this->getLogger('sedm');
        
        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => $this->t('<div id="request-subject-container-wrapper">'),
            '#suffix' => $this->t('</div>'),
        ];

        $form['form-container']['message'] = [
          '#type' => 'item',
          '#markup' => 'Are you sure to allow the subject request?'
        ];

        $form['form-container']['action'] = [
          '#type' => 'action'
        ];

        $form['form-container']['action']['proceed'] = [
          '#type' => 'button',
          '#value' => $this->t('Yes'),
          '#attributes' => [
            'class' => ['use-ajax',],
          ],
          '#ajax' => [
            'callback' => '::allowSubjectRequest',
            'event' => 'click',
            'url' => Url::fromRoute('sedm.enrollment.evaluation.subject.request'),
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
            'url' => Url::fromRoute('sedm.enrollment.evaluation.subject.request'),
            'options' => ['query' => ['ajax_form' => 1]],
           ],
        ];

        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
        $form['#attached']['library'][] = 'sedm/curriculum.forms.styles';

        return $form;
    }

    public function allowSubjectRequest(array &$form, FormStateInterface $form_state){

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
        unset($_SESSION['sedm']['req_subj_info']);
        return $response;
      }

    /**
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }
}

?>