<?php
/**
 * Class Fisdap_Reports_Comments
 * This is the Comments Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Comments extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                                'selected' => array('sites' => array())
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one student',
            'options' =>  array(
                'mode' => 'single',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );

    public $styles = array("/css/library/Fisdap/Reports/comments.css");

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        $student_id = $this->config['student'];
        $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $student_id);
        $students = array($student_id);
        
        // clean up the site info
        $site_ids = $this->getSiteIds();
        
        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];
        
        // Run a query to get data.
        $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
        $data = $repo->getStudentCommentData($students, $site_ids, $start_date, $end_date);
        
        // make a table
        $commentTable = array(
            'title' => $student->getFullName() . "'s Shift Comments",
            'nullMsg' => "No Comments found.",
            'head' => array(
                '0' => array(
                    'Shift Info',
                    'Commenter',
                    'Instructor/Student',
                    'Date/Time',
                    'Comment',
                ),
            ),
            'body' => array(),
        );
        
        $shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
                
        // get the data for the chosen student
        if ($data[$student_id]) {
            foreach ($data[$student_id] as $id => $comment_info) {

                // format the shift info
                $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $comment_info['shift_id']);
                $summary_options = array('display_size' => 'large', 'sortable' => true);
                $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, null, $shift, $summary_options);
                
                // format the comment text
                $comment = $comment = \Fisdap\EntityUtils::getEntity('Comment', $id);
                            
                $role = ucfirst($comment->user->getCurrentRoleName());
                                
                // add the row
                $commentTable['body'][$id] = array(
                    array(
                        'data' => $shift_info,
                        'class' => 'shift_info',
                    ),
                                array(
                                    'data' => $comment->user->getName(),
                        'class' => 'commenter',
                ),
                                array(
                                    'data' => $role,
                        'class' => 'commenter_role',
                ),
                                array(
                                    'data' => $comment->updated->format('Y-m-d H:i:s'),
                        'class' => 'commenter_role',
                ),
                array(
                                    'data' => $comment->comment,
                        'class' => 'comment_text',
                )
                );
            }
        }
        
        // add the table to this report
        $this->data['comments'] = array("type" => "table",
                                          "content" => $commentTable);
    }
}
