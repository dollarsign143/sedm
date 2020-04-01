<?php

namespace Drupal\sedm\Form\Hidden;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

use Drupal\sedm\Database\CurriculumDatabaseOperations;

class EditCurriculumForm extends FormBase {


    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_menu_curriculum_default_tab_edit_curriculum';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        if(isset($_SESSION['sedm']['curr_id'])){
            $curri_uid = $_SESSION['sedm']['curr_id'];
            $form['curri_uid'] = [
                '#type' => 'item',
                '#markup' => $curri_uid,
            ];
        }
        else {
            $form['curri_uid'] = [
                '#type' => 'item',
                '#markup' => $this->t('ERROR: Access Denied. Please contact the administrator!'),
            ];
        }

        unset($_SESSION['sedm']['curr_id']);
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

    }

}

?>