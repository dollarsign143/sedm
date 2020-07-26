<?php

namespace Drupal\sedm\Form\Templates\Evaluation;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Url;

use Drupal\sedm\Database\EvaluationDatabaseOperations;


class EvaluationForGraduation extends FormBase {
    use LoggerChannelTrait;
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'sedm_evaluation_menu_eval_for_graduation';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['#tree'] = TRUE;
        $form['form-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-for-grad-form-container-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['form-container']['form-title'] = [
            '#type' => 'item',
            '#markup' => $this->t('<h2>Evaluation for Graduating Students</h2>'),
        ];

        // $form['form-container']['printButton'] = [
        //     '#type' => 'button',
        //     '#value' => 'Print',
        //     '#attributes' => [
        //         'id' => 'print-button'
        //     ],
        //     '#ajax' =>  [
        //         'callback' => '::printButton',
        //         'url' => Url::fromRoute('sedm.menu.evaluation.enrollment'),
        //         'options' => ['query' => ['ajax_form' => 1]],
        //         'event' => 'click',
        //     ],
        // ];

        $form['form-container']['student-details-container'] = [
            '#type' => 'fieldset',
            '#title' => 'Fill out some info.',
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

        $form['form-container']['student-details-container']['button-container']['evaluateSubjs'] = [
            '#type' => 'submit',
            '#value' => $this->t('Evaluate Subjects'),
            '#ajax' =>  [
                'callback' => '::evaluateStudent',
                'wrapper' => 'eval-for-grad-form-container-wrapper', 
                'event' => 'click',
            ],
            '#attributes' => [
                'class' => ['flat-btn',],
            ],
        ];

        $form['form-container']['stud-info-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="stud-info-container-wrapper">',
            '#suffix' => '</div>',
            // '#attributes' => [
            //     'class' => ['print-only'],
            // ],
            '#weight' => 2
        ];

        $form['form-container']['eval-sheet-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="eval-sheet-container-wrapper">',
            '#suffix' => '</div>',
            // '#attributes' => [
            //     'class' => ['print-only'],
            // ],
            '#weight' => 3
        ];

        $form['form-container']['category-units-container'] = [
            '#type' => 'container',
            '#prefix' => '<div id="category-units-container-wrapper">',
            '#suffix' => '</div>',
            '#attributes' => [
                'class' => ['print-only'],
            ],
            '#weight' => 4
        ];


        return $form;

    }


    public function evaluateStudent(array &$form, FormStateInterface $form_state){

        // this condition will return errors to the form if there are
        if($form_state->getErrors()){
    
            $form['form-container']['student-details-container']
            ['notice-container']['status_messages'] = [
                '#type' => 'status_messages',
            ];

            return $form['form-container'];
    
        }
        else {
    
            $studIdNumber = $form_state->getValue(['form-container','student-details-container','id-container','id-number']);
        
            $EDO = new EvaluationDatabaseOperations();
            $stud_info = $EDO->getStudentInfo($studIdNumber);
            
            if(empty($stud_info)){
                $response = new AjaxResponse();
                $content['form-container']['notice-container']['message'] = [
                    '#type' => 'item',
                    '#markup' => $this->t('Can\'t find the ID number!'),
                ];
                $command = new OpenModalDialogCommand($this->t('Error!'), $content, ['width' => '50%',]);

                $response->addCommand($command);
            
                return $response;

            }
            else {
                $curri_uid = $stud_info[0]->curriculum_uid;
                $stud_uid = $stud_info[0]->student_uid;

                $form['form-container']['stud-info-container']['student-info-fieldset'] = [
                    '#type' => 'fieldset',
                    '#title' => 'Student Info.',
                    '#weight' => 2,
                    '#attributes' => [
                        'class' => ['print-only'],
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container'] = [
                    '#type' => 'container',
                    '#attributes' => [
                        'class' => ['inline-container-col3',],
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container']['last-name'] = [
                    '#type' => 'textfield',
                    '#title' => $this->t('Last Name'),
                    '#value' => ucwords($stud_info[0]->studProf_lname),
                    '#attributes' => [
                        'class' => ['flat-input',],
                        'disabled' => TRUE,
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container']['first-name'] = [
                    '#type' => 'textfield',
                    '#title' => $this->t('First Name'),
                    '#value' => ucwords($stud_info[0]->studProf_fname),
                    '#attributes' => [
                        'class' => ['flat-input',],
                        'disabled' => TRUE,
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container']['middle-name'] = [
                    '#type' => 'textfield',
                    '#title' => $this->t('Middle Name'),
                    '#value' => ucwords($stud_info[0]->studProf_mname),
                    '#attributes' => [
                        'class' => ['flat-input',],
                        'disabled' => TRUE,
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container']['college'] = [
                    '#type' => 'textfield',
                    '#title' => $this->t('College'),
                    '#value' => ucwords($stud_info[0]->college_abbrev),
                    '#attributes' => [
                        'class' => ['flat-input',],
                        'disabled' => TRUE,
                    ],
                ];
    
                $form['form-container']['stud-info-container']['student-info-fieldset']['info-container']['program'] = [
                    '#type' => 'textfield',
                    '#title' => $this->t('Program'),
                    '#value' => ucwords($stud_info[0]->program_abbrev),
                    '#attributes' => [
                        'class' => ['flat-input',],
                        'disabled' => TRUE,
                    ],
                ];

                $form['form-container']['eval-sheet-container']['eval-sheet'] = [
                    '#type' => 'fieldset',
                    '#title' => $this->t('Evaluated Subjects'),
                    '#attributes' => [
                        'class' => ['print-only', 'page-break-before', 'page-break-after'],
                    ],
                ];
                // ALGORITHM
                // #1: get subject categories
                $categories = $EDO->getSubjectCategories();
                
                // #2: for each category get subjects
                foreach($categories as $category){
                    $data = NULL;
                    $category_total_units = 0;
                    $categorySummary = [];
                    $categorySummaryUnits = 0;

                    if($category->subjCat_uid == 10 || $category->subjCat_name == 'ELECTIVE SUBJECT'){
                        $category_subjs = $EDO->getCurriculumElectiveSubjects($curri_uid);
                    }
                    else {
                        // #2.1: get the subject of the specified category
                        $category_subjs = $EDO->getCurriculumSubjectsByCategory($curri_uid, $category->subjCat_uid);
                    }

                    $categorySummary[$category->subjCat_name] = [
                        'categoryName' => $category->subjCat_name,
                        'totalunits' => $category_total_units,
                        'totalAcquiredUnits' => $categorySummaryUnits
                    ];
                    
                    // #3: for each subject check the remarks on the student
                    if(empty($category_subjs)){
                        $data .= '<tr>
                            <td>NONE</td>
                            <td>NONE</td>
                            <td>NONE</td>
                            <td>NONE</td>
                        </tr>';
                    }
                    else {
                        foreach($category_subjs as $subj){
                            $units = $subj->curricSubj_labUnits + $subj->curricSubj_lecUnits;
                            $category_total_units += $units;
                            $categorySummary[$category->subjCat_name] = [
                                'categoryName' => $category->subjCat_name,
                                'totalunits' => $category_total_units,
                                'totalAcquiredUnits' => $categorySummaryUnits
                            ];
                            $remarks = $EDO->getStudSubjectRemarks($stud_uid, $subj->subject_uid);
                            if(empty($remarks)){
                                $categorySummaryUnits += 0;
                                $categorySummary[$category->subjCat_name] = [
                                    'categoryName' => $category->subjCat_name,
                                    'totalunits' => $category_total_units,
                                    'totalAcquiredUnits' => $categorySummaryUnits
                                ];
                                
                                $data .= '<tr>
                                    <td>'.$subj->subject_code.'</td>
                                    <td>'.$subj->subject_desc.'</td>
                                    <td>'.$units.'</td>
                                    </tr>';
                            }
                            else {
                                
                                if(empty($remarks[0]->studSubj_finalRemarks)){
                                    $final_remarks = $remarks[0]->studSubj_remarks;
                                }
                                else {
                                    $final_remarks = $remarks[0]->studSubj_finalRemarks;
                                }
    
                                if($final_remarks == 'INC' || $final_remarks == 'FAILED' || 
                                $final_remarks == 'DRP' || $final_remarks == 'DROP' || 
                                $final_remarks > 3){
                                    $categorySummaryUnits += 0;
                                    $categorySummary[$category->subjCat_name] = [
                                        'categoryName' => $category->subjCat_name,
                                        'totalunits' => $category_total_units,
                                        'totalAcquiredUnits' => $categorySummaryUnits
                                    ];
                                }
                                else{
                                    $categorySummaryUnits += ($subj->curricSubj_labUnits + $subj->curricSubj_lecUnits);
                                    $categorySummary[$category->subjCat_name] = [
                                        'categoryName' => $category->subjCat_name,
                                        'totalunits' => $category_total_units,
                                        'totalAcquiredUnits' => $categorySummaryUnits
                                    ];
                                }
    
                                $data .= '<tr>
                                <td>'.$subj->subject_code.'</td>
                                <td>'.$subj->subject_desc.'</td>
                                <td>'.($subj->curricSubj_labUnits + $subj->curricSubj_lecUnits).'</td>
                                <td>'.$final_remarks.'</td>
                                </tr>';
                            }
                            
                        }
                    }


                    $form['form-container']['eval-sheet-container']['eval-sheet'][$category->subjCat_uid] = [
                        '#type' => 'details',
                        '#title' => $this->t($category->subjCat_name),
                        '#open' => TRUE,
                    ];

                    $form['form-container']['eval-sheet-container']['eval-sheet'][$category->subjCat_uid]['description'] = [
                        '#type' => 'item',
                        '#markup' => $this->t('The subjects listed below have been evaluated.'),
                    ];

                    $form['form-container']['eval-sheet-container']['eval-sheet'][$category->subjCat_uid]['table'] = [
                        '#type' => 'markup',
                        '#markup' => $this->t('
                        <div>
                            <table>
                            <thead>
                                <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Units</th>
                                <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="subjectsAvailableBody">
                            '.$data.'
                            </tbody>
                            </table>
                        </div>'),
                    ];


                    $form['form-container']['category-units-container']['category-units-fieldset'] = [
                        '#type' => 'fieldset',
                        '#title' => 'Summary',
                        '#weight' => 4,
                    ];
                    // var_dump($categorySummary);
                    // $summaryData = NULL;
                    foreach($categorySummary as $summary){
                        // var_dump($summary);
                        $summaryData .= '<tr>
                            <td>'.$summary['categoryName'].'</td>
                            <td>'.$summary['totalunits'].'</td>
                            <td>'.$summary['totalAcquiredUnits'].'</td>
                            </tr>';

                    }

                    
                    $form['form-container']['category-units-container']['category-units-fieldset'] = [
                        '#type' => 'markup',
                        '#markup' => $this->t('
                        <div>
                            <table>
                            <thead>
                                <tr>
                                <th>Category Name</th>
                                <th>Total Units</th>
                                <th>Total Acquired Units</th>
                                </tr>
                            </thead>
                            <tbody class="categoryUnitsSummaryBody">
                            '.$summaryData.'
                            </tbody>
                            </table>
                        </div>'),
                    ];
                }

                return $form['form-container'];
            }

        }
    
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        $idNumber = $form_state->getValue(['form-container','student-details-container','id-container','id-number']);

        if(empty($idNumber)){
            $form_state->setError($form, $this->t('ID number is empty!'));
        }

    }

    public function printButton(array &$form, FormStateInterface $form_state){
        $options = array(
            'target_id' => 'eval-for-grad-form-container-wrapper',
            'button_id' => 'print-button',
            'value' => 'Print',
            'type' => 'button',
            );
          
          print area_print_form($options);
    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
    }


}
?>