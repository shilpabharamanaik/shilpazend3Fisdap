<?php

/**
 * This widget shows Lab Skills, Graduation Requirements, and Airway Management Goals
 * on various panels.  
 * 
 * It features the ability to render each goals area on a panel.  By default, the widget
 * will render the last panel that the user selected. If no previous panel has been selected
 * by the user, the Lab Skills Requirement panel will be displayed. Any page has the ability
 * to override this functionality by passing parameters to the renderContainer method. 
 * See the comments in the renderContainer method for details of these parameters.
 *
 * Each panel should live in a renderPanel method. So for airway, the method would be:
 * renderPanelAirway
 * @author jcummins
 */
class MyFisdap_Widgets_GoalsWidget extends MyFisdap_Widgets_Base implements MyFisdap_Widgets_iConfigurable
{

    /**
     * An ordered list of the available panels by their method name. If the user has 
     * not previously selected a panel, then the first in the list is displayed.
     * 
     * @var array
     */
    private $panels = array(
        array(method => 'renderPanelLab', title => 'Lab Goals'),
        array(method => 'renderPanelGraduation', title => 'Graduation Requirements'),
        array(method => 'renderPanelAirway', title=>'Airway Management')
    );

    /**
     * The allowed callbacks list. Stop arbitrary code from being executed
     * @var array
     */
    protected $registeredCallbacks = array('renderExplicitStudent');

    /**
     * Override the default to remove padding, pass arguments through to child methods
     * @param array $options Optional options for the rendering of the widget
     * @return String containing the HTML for the widget container.
     */
    public function renderContainer() {

        // Grab the optional arguments
        $args = func_get_args();

        // Take any arguments passed into the renderContainer method 
        // and pass them through to the render method
        $widgetContents = call_user_func_array(array($this,'render'), $args);

        // Take any arguments passed into the renderContainer method 
        // and pass them through to the renderHeader method
        $header = call_user_func_array(array($this,'renderHeader'), $args);


        $html = <<<EOF
            <div id='widget_{$this->widgetData->id}_container' class='widget-container widget-container-blank' data-widget-id='{$this->widgetData->id}'>
                <div class='widget-title-bar'>
                    $header
                </div>
                <div id='widget_{$this->widgetData->id}_render' class='widget-render'>
                    <div class="widget-viewport">
                        <div class="widget-overflow">
                            {$widgetContents}
                        </div>
                    </div>
                </div>
            </div>
EOF;
        
        return $html;
    }

    /**
     * Generates the HTML of the widget body
     *
     * @param array $options Optional options for the rendering of the widget in JSON object format
     * @return string HTML of the widget body
     */
    public function render(){
        $widgetSession = new \Zend_Session_Namespace("WidgetData");

        // Goals widget can take a long time to query MySQL. Allow for a longer timeout.
        $mysqlTimeout = 300; // 5 min
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = {$mysqlTimeout}");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = {$mysqlTimeout}");

        // Grab the widgetOptions, decode them from JSON
        $args = func_get_args();

        $widgetOptions = json_decode($args[0],TRUE);
        
        // Check if we have an explicit student id to use instead of the logged in user
        if($widgetOptions && isset($widgetOptions) && array_key_exists('explicitStudentId', $widgetOptions))
        {
            
            // Set the Session user id
            $widgetSession->user_id = \Fisdap\EntityUtils::getEntity('StudentLegacy', $widgetOptions['explicitStudentId'])->user->id;
        }
        $html = '';

        // Render the panels
        $html .= call_user_func_array(array($this, 'renderPanels'), $args);

        return $html;

    }

    /**
     * Render the panels
     *
     * @param array $options Optional options for the rendering of the widget
     * @return string HTML for all of the panels
     */
    public function renderPanels() {

        // Grab the optional arguments
        $args = func_get_args();
        $html = '';
        $html .= '
            <script type="text/javascript" src="/js/jquery.actual.min.js"></script>
            <script type="text/javascript" src="/js/library/MyFisdap/jquery.panelWidget.js"></script>
            <link type="text/css" rel="stylesheet" media="screen" href="/css/my-fisdap/widget-styles/goals-widget.css">
            <script type="text/javascript" src="/js/jquery.jqplot.min.js"></script>
            <script type="text/javascript" src="/js/jquery.eurekaGraph.js"></script>
            <script type="text/javascript" src="/js/jquery.printElement.min.js"></script>
            <script type="text/javascript" src="/js/library/SkillsTracker/View/Helper/eureka-modal.js"></script>
            <link type="text/css" rel="stylesheet" media="screen" href="/css/jquery.jqplot.min.css">
            <link type="text/css" rel="stylesheet" media="screen" href="/css/jquery.eurekaGraph.css">
            <script type="text/javascript" src="/js/skills-tracker/shifts/eureka_modal.js"></script>
            <script type="text/javascript" src="/js/jquery.chart.min.js"></script>
            <script type="text/javascript" src="/js/library/MyFisdap/airway-management-widget.js"></script>
            <script>
            $(function(){
                var widget = $(\'#widget_'.$this->widgetData->id.'_container\');
                widget.panelWidget();

                setTimeout(function(){

                    // now resize the panel widget since it apparently has issues :p
                    // Only resize if not minimized
                    var newViewportHeight = 0;
                    var current_panel_title = widget.find(".widget-title-text").text();

                    if(current_panel_title == "Skills Practice Goals"){
                        current_panel_title = "Lab Goals";
                    }
                    widget.find(".widget-panel").each(function(){

                        if($(this).attr("data-widget-panel-title") == current_panel_title){
                            newViewportHeight = $(this).actual("height");
                        }

                    });

                    if(newViewportHeight > 0) {
                      // Resize the widget to the new height
                      widget.find(".widget-render").animate({
                        height: newViewportHeight
                      }, 800, function() {

                      });
                    }

                 }, 200);

              });
            </script>

        ';

        // some panels may be in different positions depending on program/student data
        // this function will handle the new arrangement
        $widgetOptions = json_decode($args[0],TRUE);
        $panels = $this->arrangePanels($widgetOptions);

        // Loop through and render each panel's html
        foreach($panels as $panel)
        {
            $html .= '<div class="widget-panel" data-widget-panel-title="'.$panel['title'].'">';
            $html .= call_user_func_array(array($this, $panel['method']), $args);
            $html .= '</div>';
        }

        return $html;
    }

    public function calculateReversedShiftDistance($shiftToValue, $panels)
    {
	
        // If nothing else, assume no shift
        $distance = 0;

        // Calculate the distance needed to shift
        foreach ($panels as $key => $row)
        {
            // Check for a match
            if($row['method'] == $shiftToValue)
            {
                // Calculate the reverse shift less one, since we're already at that item
                $distance = count($panels)-$key-1;
                break;
            }

        }

        return $distance;
    }


    public function shiftToPanel($shiftToValue, $panels)
    {
        
        // Reverse the array so we can use array_pop
        $panels = array_reverse($panels); 

        // Calculate the distance we need to shift
        $shiftDistance = $this->calculateReversedShiftDistance($shiftToValue, $panels);

        // Array to hold the items that we shift over
        $remainder = array();

        // Loop through until we get to the panel we want to shift to
        for($i=0;$i<$shiftDistance;$i++)
        {
            // Hold on to the remainders so we can append them later
            array_push($remainder,array_pop($panels));
        }

        // Return a re-assembled array of panels in the new order
        return array_reverse(array_merge(array_reverse($remainder), $panels));
    }

    /**
     * Renders the Lab Skills panel
     *
     * @param array $options Optional options for the rendering of the widget
     * @return string HTML of the lab skills panel
     */
    public function renderPanelLab() {
        $args = func_get_args();
        $widgetOptions = json_decode($args[0],TRUE);
        $studentId = ($widgetOptions['explicitStudentId']) ? $widgetOptions['explicitStudentId'] : $this->widgetData->user->getCurrentRoleData()->id;

        $html = '';

        // Get the current stats for the lab goals...
        $labResults = $this->getLabGoalResults();
        
        $html .= "<div class='goals-widget-background'>";
        
        // Show the headers
        $html .= "
        <div class='grid_12 goals-column-headers'>
            <span class='grid_2 expand_link'><a href='#' class='expand-all'>expand all</a></span>
            <span class='grid_6 title_heading'>Overall progress</span>
            <span class='grid_2 title_heading'><img src='/images/icons/lab_skills_icon_peer_white.png' title='Peer Reviews'/></span>
            <span class='grid_2 title_heading'><img src='/images/icons/lab_skills_icon_instructor_white.png' title='Instructor Signoffs'/></span>
        </div>
        <div class='clear'></div>
        ";
        
        $count = 1;
        
        // Several layers of output here.  Loop through each category, storing output to be appended later
        foreach($labResults['category_data'] as $categoryId => $categoryData){

            // This is aggregate category data that we'll fill in on our way through the results
            $category = array(
                'html' => '',
                'peer' => array(
                    'actual' => 0,
                    'goal' => 0,
                ),
                'instructor' => array(
                    'actual' => 0,
                    'goal' => 0,
                )
            );
            // First, we loop over all of the active definitions for this category.
            // We need this data precalculated for each category but do not output it yet.
            // We will use this data to aggregate a total for that category 
            foreach($categoryData['definitions'] as $defId){
                $category['html'] .= "<div class='grid_12 goal-row'>";
                
                // Get the values from the lab results for this student...
                $stats = $this->getStudentStatistics($labResults, $categoryId, $defId);

                //Total up the goals data for the current category to be used later
                $category['peer']['goal'] += isset($stats['peer_goal']) ? $stats['peer_goal'] : 0;
                $category['instructor']['goal'] += isset($stats['instructor_goal']) ? $stats['instructor_goal'] : 0;

                //Total up the checkoffs data but don't over count the goals, to be used later
                if (isset($stats['peer_actual'])) {
                    $peerCheckoffsTowardGoal = $stats['peer_actual'] > $stats['peer_goal'] ? $stats['peer_goal'] : $stats['peer_actual'];
                    $category['peer']['actual'] += $peerCheckoffsTowardGoal;
                } else {
                    $peerCheckoffsTowardGoal = 0;
                }

                if (isset($stats['instructor_actual'])) {
                    $instructorCheckoffsTowardGoal = $stats['instructor_actual'] > $stats['instructor_goal'] ? $stats['instructor_goal'] : $stats['instructor_actual'];
                    $category['instructor']['actual'] += $instructorCheckoffsTowardGoal;
                } else {
                    $instructorCheckoffsTowardGoal = 0;
                }

                // Generate the Eureka goals image if we need it
                if($stats['eureka_goal'] > 0 && $stats['eureka_window'] > 0){
                    $metEurekaOuttext = ($stats['met_eureka']?'Met':'Not Met');
                    $metEurekaClass = ($stats['met_eureka']?'eureka-goal-met':'eureka-goal-notmet');
                    $category['html'] .= "
                        <div class='grid_1 centered-txt eureka-container'>
                            <a href='/reports/lab-practice' class='open_eureka {$metEurekaClass}' data-defId='{$defId}' data-studentId='" . $this->getUser()->getCurrentRoleData()->id . "'>{$metEurekaOuttext}</a>
                        </div>
                    ";
                }else{
                    $category['html'] .= "<div class='grid_1 centered-txt'>&nbsp;</div>";
                }
                
                // Generate the html for the current result's name
                $category['html'] .= "<div class='grid_3 goal_heading'>" . $labResults['definition_data'][$defId]['definition_name'] . "</div>";
                
                // Safely grab the percent
                $percent = $this->getPercent($peerCheckoffsTowardGoal+$instructorCheckoffsTowardGoal, $stats['peer_goal']+$stats['instructor_goal'], array('lower'=>0, 'upper'=>100));
                
                // Get a scaled version of the percent
                $scaledPercent = $this->getScaledPercent($percent, 2.3);
                
                // Get the percent class
                $percentClass = $this->getPercentClass($percent);
                
                // Generate the checkbox html if we need it
                if($percent == 100){
                    $checkImage = "<div class='goal-complete-checkmark'><img src='/images/icons/checkmark-dark-gray.png'/></div>";
                }else{
                    $checkImage = '';
                }

                $category['html'] .= "
                        <div class='grid_4'>
                            <div class='$percentClass percent_bar' style='width: {$scaledPercent}%'>
                                $checkImage
                            </div>
                            <span class='percent_font percent_float_text'>
                                {$percent}%
                            </span>
                        </div>
                    ";

                // Generate the html for the peer goals, if needed
                if($stats['peer_goal'] > 0){

                    $category['html'] .= "
                        <div class='grid_2'>
                            <span class='percent_subtext'>
                                {$stats['peer_actual']} of {$stats['peer_goal']}
                            </span>
                        </div>
                    ";
                }else{
                    $category['html'] .= "<div class='grid_2 no-goal'><span class='grid_2 no-peer-goal percent_subtext'>N/A</span></div>";
                }
                
                // Generate the html for the instructor goals
                if($stats['instructor_goal'] > 0){
                    $category['html'] .= "<div class='grid_2 category_heading'><span class='percent_subtext'>" . "{$stats['instructor_actual']} of {$stats['instructor_goal']}" . "</span></div>";
                }else{
                    $category['html'] .= "<div class='grid_2'><span class='percent_subtext'>N/A</span></div>";
                }
                
                // Clean up, on to the next row
                $category['html'] .= "</div>";
                $category['html'] .= "<div class='clear'></div>";
            }

            // Safely grab the percent
            $categoryPercent = $this->getPercent($category['peer']['actual']+$category['instructor']['actual'], $category['peer']['goal']+$category['instructor']['goal'], array('lower'=>0, 'upper'=>100));

            // Get a scaled version of the percent
            $categoryScaledPercent = $this->getScaledPercent($categoryPercent, 2.3);
            
            // Get the percent class
            $categoryPercentClass = $this->getPercentClass($categoryPercent);

            // Generate the checkbox html if we need it            
            $categoryCheckImage = '';
            if($categoryPercent == 100){
                $categoryCheckImage = "<div class='goal-complete-checkmark'><img src='/images/icons/checkmark-dark-gray.png'/></div>";
            }             

            // Generate the peer goals html for this category
            $categoryPeerHtml = '';
            if($category['peer']['goal'] > 0) {
                $categoryPeerHtml = "
                    <div class='grid_2'>
                        <span class='percent_subtext'>
                            {$category['peer']['actual']} of {$category['peer']['goal']}
                        </span>
                    </div>
                ";
            } else {
                $categoryPeerHtml = "
                    <div class='grid_2 percent_subtext no-goal'><span class='grid_2 no-peer-goal'>N/A</span></div>
                ";
            }

            // Generate the instructor goals html for this category
            $categoryInstructorHtml = '';
            if($category['instructor']['goal'] > 0){
                $categoryInstructorHtml .= "<div class='grid_2 category_heading'><span class='percent_subtext'>" . "{$category['instructor']['actual']} of {$category['instructor']['goal']}" . "</span></div>";
            }else{
                $categoryInstructorHtml .= "<div class='grid_2'><span class='percent_subtext'>N/A</span></div>";
            }

            // Render the category headers with minimize links and their aggregate data that we looped through
            $html .="
                <div class='goals-widget-header grid_12 goals-widget-header-collapsed'>
                    <script>
                    $(function() {
                        $('#{$categoryId}_maximized').hide();
                    });
                    </script>
                    <div class='grid_1 caret-container'>
                        <span class='category-minimize-container'>
                            <img id='{$categoryId}_minimized' src='/images/icons/contracted_arrow_white.png' class='category-minimize'/>
                            <img id='{$categoryId}_maximized' src='/images/icons/expanded_arrow_white.png' class='category-maximize'/>
                        </span>
                    </div>
                    <div class='grid_3'>
                        {$categoryData['category_name']}
                    </div>
                    <div class='grid_4'>
                        <div class='$categoryPercentClass percent_bar' style='width: {$categoryScaledPercent}%'>
                            $categoryCheckImage
                        </div>
                        <span class='percent_font percent_float_text'>
                            {$categoryPercent}%
                        </span>
                    </div>
                    {$categoryPeerHtml}
                    {$categoryInstructorHtml}
                </div>
                <span class='clear'></span>
                <div class='goals-widget-category-container' id='{$categoryId}_category-container'>
                    {$category['html']}
                </div>
                <span class='clear'></span>
            ";

        }
        

        
        $html .= "</div>";
        
        $html .= "<div class='goal_link_text grid_6'>
                        <a href='#' class='launch-report-config' data-studentid='{$studentId}' data-reporttype='LabPracticeGoals'>
                            View the full Lab Skills report >>
                        </a>
                     </div>";
        
        // Do stuff for the eureka modal
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $html .= $view->eurekaModal();
        
        return $html;
        
    }

    /**
     * Renders the Graduation Requirements panel
     *
     * @param array $options Optional options for the rendering of the widget
     * @return string HTML of the graduation requirements panel
     */
    public function renderPanelGraduation(){
        $args = func_get_args();
        $widgetOptions = json_decode($args[0],TRUE);
        $studentId = ($widgetOptions['explicitStudentId']) ? $widgetOptions['explicitStudentId'] : $this->widgetData->user->getCurrentRoleData()->id;
        $goalData = $this->getGoalSetData();
        $html = '';
        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $this->data['goalSetId']);

        if(!$goalData){
            if($this->widgetData->user->isInstructor()){
                return "You are currently logged in with an Instructor account- unable to display goals for you.";
            }else{
                return "We're sorry, we were unable to fetch goals for the \"{$goalSet->name}\" goal set.  Please check with your instructor to make sure this goal is configured correctly.";
            }
        }else{
            $html .= "<div class='goals-widget-background'>";
            
            // $html .= "<div class='grid_12'><span class='grid_4'>&nbsp;</span><span class='grid_6 percent_font title_heading'>Performed</span><span class='grid_2 observed_heading'>Observed</span></div>";
            
            // Show the headers
            $html .= "
                <div class='grid_12 gradreq-panel-title'>
                    {$goalSet->name} {$this->renderConfigLink($args)}
                </div>
                <div class='grid_12 goals-column-headers'>
                    <span class='grid_2 expand_link'><a href='#' class='expand-all'>expand all</a></span>
                    <span class='grid_6 title_heading'>Performed</span>
                    <span class='grid_1 title_heading'>&nbsp;</span>
                    <span class='grid_3 title_heading observed_title_heading'>Observed</span>
                </div>
                <div class='clear'></div>
            ";

            //foreach($goalData as $key => $goals){
            $keys = array_keys($goalData);

            // Loop through each category
            for($i=0; $i<count($goalData); $i++){

                $category = array(
                    'html' => '',
                    'performed' => 0,
                    'required' => 0,
                    'observed' => 0,
                );

                // Get the plaintext key
                $key = $keys[$i];

                // Get an alphanumeric key for html,css,js
                $key_alpha = preg_replace("/[^A-Za-z0-9]/", '', $key);

                // Get goal data for the key
                $goals = $goalData[$key];

                if($key == "Airway Management"){
                    // do things a bit differently for Airway Management
                    $cat_performed = 0;
                    $cat_required = 0;
                    $cat_observed = 0;
                    $am_required = $goals[3];
                    krsort($goals);

                    foreach($goals as $goal_key => $am_goal_data){

                        if($goal_key == 0){
                            // coa success goal
                            $performed = $am_goal_data['success_count'];
                            $hidden_performed = $performed;
                            $percent_done = $am_goal_data['success_rate'];
                            $name = "Success (over last 20 attempts)";
                            $req =  $am_goal_data['window'];
                            $observed = "N/A";
                        }
                        else if($goal_key == 1){
                            // et success goal
                            $performed = $am_goal_data['success_count'];
                            $hidden_performed = $performed;
                            $percent_done = $am_goal_data['success_rate'];
                            $name = "ET Success (over last 10 attempts)";
                            $req =  $am_goal_data['window'];
                            $observed = "N/A";
                        }
                        else if($goal_key == 2){
                            // attempts goal
                            $percent_done = $am_goal_data['goal_percent'];
                            $performed = $am_goal_data['performed'];
                            $hidden_performed = ($performed > $am_required) ? $am_required : $performed;
                            $name = "Attempts";
                            $req = $am_required;
                            $observed = $am_goal_data['observed'];

                            $category['observed'] = $observed;
                        }

                        if($goal_key != 3) {
                            $cat_performed = $cat_performed + $hidden_performed;
                            $cat_required = $cat_required + $req;

                            $percent = $percent_done;
                            $scaledPercent = $this->getScaledPercent($percent, 2);
                            $percentClass = $this->getPercentClass($percent);

                            // run a partial for our goal row
                            $view_params = array("percent" => $percent, "goal_name" => $name, "percent_class" => $percentClass, "scaled_percent" => $scaledPercent,
                                                 "performed" => $performed, "required" => $req, "observed" => $observed);
                            $category['html'] .= $this->getViewForPartials()->partial("widgets/goals-widget-grad-panel-goal-row.phtml", array("params" => $view_params));
                        }

                    }

                    $category['performed'] += $cat_performed;
                    $category['required'] += $cat_required;
                }
                else {

                    // Loops through items in that category
                    foreach ($goals as $goalKey => $goal) {

                        if ($goal->requirementDesc > 0) {

                            $percent = floor(($goal->percentDone * 100));
                            $scaledPercent = $this->getScaledPercent($percent, 2);

                            $percentClass = $this->getPercentClass($percent);

                            // Increment the category totals for later use
			    // If they performed higher than the goal, don't count extras toward total completion
                            if ($goal->performedCount > $goal->requirementDesc) {
                                $category['performed'] += $goal->requirementDesc;
                            } else {
                                $category['performed'] += $goal->performedCount;
                            }
                            $category['required'] += $goal->requirementDesc;
                            $category['observed'] += $goal->observedCount;

                            // run a partial for our goal row
                            $view_params = array("percent" => $percent, "goal_name" => $goal->goal->name, "percent_class" => $percentClass, "scaled_percent" => $scaledPercent, "performed" => $goal->performedCount, "required" => $goal->requirementDesc, "observed" => $goal->observedCount);
                            $category['html'] .= $this->getViewForPartials()->partial("widgets/goals-widget-grad-panel-goal-row.phtml", array("params" => $view_params));
                        }
                    }

                }



                $categoryPercent = $this->getPercent($category['performed'], $category['required'], array('lower'=>0, 'upper'=>100));
                $categoryPercentClass = $this->getPercentClass($categoryPercent);
                $categoryScaledPercent = $this->getScaledPercent($categoryPercent,2);
                
                // Generate the checkbox html if we need it            
                $categoryCheckImage = '';
                if($categoryPercent == 100){
                    $categoryCheckImage = "<div class='goal-complete-checkmark'><img src='/images/icons/checkmark-dark-gray.png'/></div>";
                }

                // Generate the instructor goals html for this category
                $categoryObservedHtml = "
                    <div class='grid_1 observed-container'>
                        <span class='observed_font'>{$category['observed']}</span>
                    </div>
                    <div class='grid_1'>&nbsp;</div>
                ";

                // We're at the end of a category, actually output the html
                $html .= "
                    <div class='goals-widget-header grid_12 goals-widget-header-collapsed'>
                        <script>
                        $(function() {
                            $('#{$key_alpha}_maximized').hide();
                        });
                        </script>
                        <div class='grid_1 caret-container'>
                            <span class='category-minimize-container'>
                                <img id='{$key_alpha}_minimized' src='/images/icons/contracted_arrow_white.png' class='category-minimize'/>
                                <img id='{$key_alpha}_maximized' src='/images/icons/expanded_arrow_white.png' class='category-maximize'/>
                            </span>
                        </div>
                        <div class='grid_4'>
                            {$key}
                        </div>
                        <div class='grid_5'>
                            <div class='$categoryPercentClass percent_bar' style='width: {$categoryScaledPercent}%'>
                                $categoryCheckImage
                            </div>
                            <span class='percent_font percent_float_text'>
                                {$categoryPercent}%
                            </span>
                        </div>
                        {$categoryObservedHtml}
                    </div>
                    <span class='clear'></span>
                    <div class='goals-widget-category-container' id='{$key_alpha}_category-container'>
                        {$category['html']}
                    </div>
                    <span class='clear'></span>
                ";
            }


            // let the user know what shift and subject types we're looking at
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->widgetData->user->getProgramId());
            $settings = $program->program_settings;

            $subjectTypeMessage = \Fisdap\Entity\Subject::getSubjectTypeDescription($settings->subject_types_in_mygoals);

            $shiftTypes = array();
            if($settings->include_lab_in_mygoals){
                $shiftTypes[] = 'lab';
            }
            
            if($settings->include_clinical_in_mygoals){
                $shiftTypes[] = 'clinical';
            }
            
            if($settings->include_field_in_mygoals){
                $shiftTypes[] = 'field';
            }
            $shiftTypeMessage = \Util_String::addAndToList(implode(", ", $shiftTypes)) . " " . \Util_String::pluralize("setting", count($shiftTypes));

            $html .= "<div class='goal_footer_text grid_6'>This report includes all skills and assessments performed on $subjectTypeMessage patients in the $shiftTypeMessage.</div>";
            $html .= "<div class='goal_link_text grid_6'>
                        <a href='#' class='launch-report-config' data-studentid='{$studentId}' data-goalsetid='{$goalSet->id}' data-reporttype='GraduationRequirements'>
                            View the full Graduation Requirements report >>
                        </a>
                     </div>";
            
            $html .= "</div>";
            
            return $html; 
        }
    }

    /**
     * Renders the Airway Management panel
     *
     * @param array $options Optional options for the rendering of the widget
     * @return string HTML of the airway management panel
     */
    public function renderPanelAirway()
    {
        // set up/get some values
        $args = func_get_args();
        
        $widgetOptions = json_decode($args[0],TRUE);
        $studentId = ($widgetOptions['explicitStudentId']) ? $widgetOptions['explicitStudentId'] : $this->widgetData->user->getCurrentRoleData()->id;
        
        $goal_set = \Fisdap\EntityUtils::getEntity('GoalSet', $this->data['goalSetId']);
        $am_required = $goal_set->getAirwayManagementNumberRequired();


        // go get our data
        $repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
        $totals = $repo->getTotals($studentId, $goal_set);
        $et_totals = $repo->getEtTotals($studentId, $goal_set);
        $total_attempts = $totals['total'];
        $total_sims = $totals['sims'];
        $total_patients = $totals['patients'];
        $eureka_attempts = $totals['eureka_attempts'];
        $eureka_dates = $totals['eureka_dates'];

        // calculate overall goal
        $percent = $this->getPercent($total_attempts, $am_required, array('lower'=>0, 'upper'=>100));
        
        // generates the HTML for the eureka graph for ALL attempts
        $all_eureka = $this->generateAirwayEureka($studentId, $eureka_attempts, $eureka_dates);
        $coa_eureka = $this->generateAirwayCoaEureka($studentId, $eureka_attempts, $eureka_dates);
        
        // set up the colors for our pie charts
        $live_patients_colors = array("#5267b0", "#ad7ab4", "#ff8757", "#ffc974", "#4fbc71");
        $sim_colors = array("#5267b0", "#ad7ab4", "#ff8757", "#ffc974", "#4fbc71");
        //$sim_colors = array("#cccccc", "#999999", "#666666", "#4d4d4d", "#1a1a1a", "#00a699");
        
        // set up the variables for displaying hte HMTL
        
        // overall progress bar
        $overall_progress = array();
        $overall_progress['width'] = $this->getScaledPercent($percent, 2.5);
        $overall_progress['class'] = $this->getPercentClass($percent);
        $overall_progress['percent'] = $percent;
        $overall_progress['attempt_count'] = $total_attempts;
        $overall_progress['goal'] = $am_required;

        // et success progress bar
        $et_success_progress = array();

        $et_success_precent = $this->getPercent($et_totals['success_count'],10);
        
        $et_success_progress['percent'] = $et_success_precent;
        $et_success_progress['width'] = $this->getScaledPercent($et_success_precent, 2.5);
        $et_success_progress['class'] = $this->getPercentClass($et_success_precent);

        // coa success progress bar
        $coa_success_progress = array();
        
        $coa_success_precent = $this->getPercent($coa_eureka['count'],20);
        
        $coa_success_progress['percent'] = $coa_success_precent;
        $coa_success_progress['width'] = $this->getScaledPercent($coa_success_precent, 2.5);
        $coa_success_progress['class'] = $this->getPercentClass($coa_success_precent);
        
        // the rest of our parameters
        $view_params = array();
        $view_params['overall_progress'] = $overall_progress;
        $view_params['et_success_progress'] = $et_success_progress;
        $view_params['coa_success_progress'] = $coa_success_progress;
        $view_params['live_patients_pie_chart'] = $this->getPieChartHTML("Live patients", $live_patients_colors, $total_patients);
        $view_params['simulations_pie_chart'] = $this->getPieChartHTML("Simulations", $sim_colors, $total_sims, true, $totals['total_scenarios']);
        $view_params['coa_eureka'] = $coa_eureka['html'];
        $view_params['all_eureka'] = $all_eureka;
        $view_params['student_id'] = $studentId;
        $view_params['goal_set_id'] = $this->data['goalSetId'];
        
        // run a partial on our airway management panel HTML
        $view = $this->getViewForPartials();
        $html = $view->partial("widgets/airway-management-panel.phtml", array("params" => $view_params));
        
        return $html;
    }
    
    private function generateAirwayEureka($student_id, $eureka_attempts, $eureka_dates)
    {
        $eureka_helper = new Fisdap_View_Helper_EurekaGraph();
        $all_graph_id = "airway_management_eureka_all_attempts_" . $student_id;
        $all_eureka_msg = "All attempts eureka";
        return $eureka_helper->eurekaGraph($eureka_attempts, $eureka_dates, 20, 20, $all_graph_id, false, $all_eureka_msg);
    }
    
    private function generateAirwayCoaEureka($student_id, $eureka_attempts, $eureka_dates)
    {
        // now deal with the CoA attempts
        $total_attempts = count($eureka_attempts);
		$starting_point = $total_attempts - 20;
		
		$coa_attempts = array();
		$coa_dates = array();
        $coa_success_count = 0;
		
		if($starting_point < 0){
			$coa_attempts = $eureka_attempts;
			$coa_dates = $eureka_dates;
            if($eureka_attempts){
                foreach($eureka_attempts as $attempt){
                    if($attempt === 1){
                        $coa_success_count++;
                    }
                }
            }
		}
		else {
			for($i = $starting_point; $i < $total_attempts; $i++){
				if(isset($eureka_attempts[$i])){
					$coa_attempts[] = $eureka_attempts[$i];
					$coa_dates[] = $eureka_dates[$i];
                    
                    if($eureka_attempts[$i]){
                        $coa_success_count++;
                    }
				}
			}
		}
        
        $helper = new Fisdap_View_Helper_EurekaGraph();
        $id = "airway_management_eureka_coa_attempts_" . $student_id;
        $msg = "";
        $html = $helper->eurekaGraph($coa_attempts, $coa_dates, 20, 20, $id, true, $msg);
        return array("html" => $html, "count" => $coa_success_count);
    }
    
    private function getPieChartHTML($title, $colors, $data, $sims = false, $scenarios_count = null)
    {
        $total = 0;
        $percents = array();
        $html = "";

        
        if($data){
            foreach($data as $count){
                $total = $total + $count;
            }
            
            $i = 0;
            foreach($data as $type => $count){
                $percents[$type] = array("count" => $count, "percent" => $this->getPercent($count, $total));
                $i++;
            }
            
        }
        
        $opacity_style = ($total == 0) ? "opacity:0.3;" : "";
        $extra_top_margin_style = ($sims) ? "" : "margin-top:0.5em;";
        
        $html .= "<div class='live_patients_pie_chart'>";
        $html .=    "<h4>" . $title . ": " . $total . "</h4>";
        $html .=    ($sims) ? "<h5>Scenarios: " . $scenarios_count . "</h5>" : "<h5></h5>";
        $html .=    "<canvas id='pie_chart' width='145' height='145' style='" . $opacity_style . " " . $extra_top_margin_style . "'></canvas>";
        $html .=    "<div class='pie_chart_legend' style='" . $extra_top_margin_style . "'>";
        
        if($percents){
            $count = 0;
            foreach($percents as $type => $tally){
                $html .=    "<div class='pic_chart_legend_color_box' data-value='" . $tally['percent'] . "' data-color='" . $colors[$count] . "' style='background-color:" . $colors[$count] . "'></div>";
                $html .=    "<div class='pic_chart_data_legend_label'>" . $type . " <span class='legend_percent'>(" . $tally['count'] . ")</span></div>";
                $count++;
            }
        }
        
        $html .=     "</div>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Determine the user to target in this widget's data
     * @return \Fisdap\Entity\User The user that the widget's data should reflect
     */
    private function getUser(){
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        // In this case, pull the user from the session...
        if($user->isInstructor()){

            // This gets set in the ShiftsController.  Kind of gross, but it works.
            $widgetSession = new \Zend_Session_Namespace("WidgetData");

            // Only use the session if we locate a user_id
            if($widgetSession->user_id) {
                return \Fisdap\EntityUtils::getEntity('User', $widgetSession->user_id);
            } else {
                return $user;
            }

        }else{
            return $user;
        }
    }

    /**
     * Helper function to fetch and sort the Graduation Requirements goal set data.
     * 
     * @return array A full goal set for the student, sorted alphabetically by goal type, and then
     * by percent done descending by skills performed.
     */
    public function getGoalSetData(){
        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $this->data['goalSetId']);
        
        // Patch this in here, too.  Need to be able to pull up the default goal set correctly
        // in case it hasn't been set yet, or set incorrectly (there were 2 goal sets named 
        // "National Standard Curriculum", so we cant search by that any more.
        if (!$goalSet->id) {
            $goalSet = $this->getDefaultGoalset();
            $this->data['goalSetId'] = $goalSet->id;
            
        }
        
        // Filter the available shift types to only include ones the program has configured
        $shiftTypes = array();
        
        $settings = $this->getWidgetProgram()->program_settings;
        
        if($settings->include_lab_in_mygoals){
            $shiftTypes[] = 'lab';
        }
        
        if($settings->include_clinical_in_mygoals){
            $shiftTypes[] = 'clinical';
        }
        
        if($settings->include_field_in_mygoals){
            $shiftTypes[] = 'field';
        }

        //We have to be sure that 'shiftTypes' is sent keyed as 'shiftSites' because that is what the goals backend expects
        $dataOptions = array(
            'startDate' => new \DateTime('2000-01-01'),
            'endDate' => new \DateTime(),
            'subjectTypes' => $settings->subject_types_in_mygoals,
            'shiftSites' => $shiftTypes,
        );
        
        // if($this->widgetData->user->isInstructor()){
            // return false;
        // }
        
        $student = $this->getUser()->getCurrentRoleData();
        
        $goals = new \Fisdap\Goals($student->id, $goalSet, $dataOptions, $this->getUser()->getName());

        $goalData = $goals->getGoalsResults(null, true, false);

        if(is_array($goalData)){
            // Sort and clean up the goals report a little...
            foreach($goalData as $key => $goals){
                usort($goalData[$key], array('self', 'sortGoalDataByScore'));
            }
            // Sort by keys to make them alphabetical...
            ksort($goalData);
        }

        return $goalData;
    }

    /**
     * Get the default Graduation Requirements goal set for the user's program'
     * @return array The default graduation requirements goal set for the program and certification level
     */
    private function getDefaultGoalset(){

        $goalSet = $this->getUser()->getCurrentRoleData()->getGoalSet();

        return $goalSet;
    }

    /**
     * Helper function to sort the Graduation Requirements goal data by score.
     * @param \Fisdap\Entity\Goal $a first goal to compare
     * @param \Fisdap\Entity\Goal $b second goal to compare
     * 
     * @return 0 if equal, -1 if a > b,  or 1 if a < b.
     */
    public function sortGoalDataByScore($a, $b){
        // Weird bug- if this function throws any sort of exception, it breaks saying
        // usort(): Array was modified by the user comparison function.
        // Just making sure we have objects before we start comparing them.
        if(is_object($a) && is_object($b)){
            if($a->percentDone == $b->percentDone){
                return 0;
            }
            
            return ($a->percentDone > $b->percentDone)?-1:1;
        }
        
        return 0;
    }

    /**
     * Override the configuration form id for the Graduation Requirements
     * @return [type] [description]
     */
    public function getConfigurationFormId(){
        return $this->getNamespacedName('goals-report-config-form');
    }
    
    /**
     * [getConfigurationForm description]
     * @return [type] [description]
     */
    public function getConfigurationForm(){
        $programSets = \Fisdap\EntityUtils::getRepository('Goal')->getProgramGoalSets($this->getWidgetProgram()->id, true);
        
        $form = "
            <form id='{$this->getConfigurationFormId()}' class='goals-config-content'>
                <input type='hidden' name='wid' value='{$this->widgetData->id}' />
                <div class='inline-label'>Goal Set:</div><select name='goalSetId' id='goalSetId' class='chzn-select'>
        ";
        
        foreach($programSets as $ps){
            if($ps->id == $this->data['goalSetId']){
                $form .= "<option value='{$ps->id}' SELECTED='SELECTED'>{$ps->name}</option>";
            }else{
                $form .= "<option value='{$ps->id}'>{$ps->name}</option>";
            }
        }
        
        $form .= "
                </select>
            </form>
        ";
    
        return $form;
    }

    /**
     * This callback that sets the studentId
     */
    public function renderExplicitStudent()
    {
        $args = func_get_args();

        return $this->render(json_encode($args, JSON_FORCE_OBJECT));
    }
    
    /**
     * This function calculates and returns the necessary values for the lab skills goal progress lines.
     * 
     * @param unknown_type $labResults
     * @param unknown_type $categoryId
     * @param unknown_type $defId
     * 
     * @return Array containing the percentage complete, goal total, instructor signoff count,
     * total signoff count, and whether or not the eureka point has been hit.
     */
    private function getStudentStatistics(&$labResults, $categoryId, $defId){
        $user = $this->getUser();
        
        $items = $labResults['item_data'][$categoryId][$defId][$user->getCurrentRoleData()->id];
        $defRecord = $labResults['definition_data'][$defId];
        
        $returnValues = array();
        
        $returnValues['peer_goal'] = $defRecord['peer_goal'];
        $returnValues['peer_actual'] = 0;

        $returnValues['instructor_goal'] = $defRecord['instructor_goal'];
        $returnValues['instructor_actual'] = 0;
        
        if(is_array($items)){
            $itemList = array();
            
            foreach($items as $itemId => $item){
                if($item['evaluator_type_id'] == 2){
                    if($item['passed']){
                        $returnValues['peer_actual']++;
                        array_push($itemList, 1);
                    }
                    else {
                        array_push($itemList, 0);
                    }

                }
                else if($item['evaluator_type_id'] == 1 && $item['confirmed']){
                    if($item['passed']){
                        $returnValues['instructor_actual']++;
                        array_push($itemList, 1);
                    }
                    else {
                        array_push($itemList, 0);
                    }
                }
            }
            
            $practiceItemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
            
            $returnValues['met_eureka'] = $practiceItemRepo->hasEureka($itemList, $defRecord['eureka_goal'], $defRecord['eureka_window']);
        }
        
        $returnValues['eureka_window'] = $defRecord['eureka_window'];
        $returnValues['eureka_goal'] = $defRecord['eureka_goal'];
        
        return $returnValues;
    }
    
    /**
     * This function gets the lab skills results pracice items for the user
     * 
     * @return array Lab skills practice item results
     */
    private function getLabGoalResults(){
        $itemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');
        
        $user = $this->getUser();
        
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $user->getProgramId());
        
        $studentIDArray = array($user->getCurrentRoleData()->id);
        $cert = ($user->getCurrentRoleName() == "student") ? $user->getCurrentRoleData()->getCertification() : "Instructor";
        $labResults = $itemRepo->getItems($program, $cert, $studentIDArray);
        
        return $labResults;
    }
    
    /**
     * Render the left and right links to flip through different widget pages
     * 
     * @return string The html of the left-right links in the header
     */
    protected function renderRotateLinks() {
        $html = <<<EOF
            <span class='widget-rotate'>
                <img src='/images/icons/arrow_rotate_left_white.png' class='widget-rotate-left' alt='Previous'> <a href="javascript:void(0);" class='widget-rotate-left'>Previous</a><span class="separator-vertical"> | </span><a href="javascript:void(0);" class='widget-rotate-right'>Next</a> <img src='/images/icons/arrow_rotate_right_white.png' class='widget-rotate-right' alt='Next'>
            </span>
EOF;
        return $html;
    }

    /**
     * Renders the widget header. Overridden from parent class to add rotate buttons.
     * 
     * @return string HTML of the header
     */
    protected function renderHeader(){

        // Grab the widgetOptions, decode them from JSON
        $args = func_get_args();

        $widgetOptions = json_decode($args[0],TRUE);

        // Check we want this not to be minimizable
        if($widgetOptions && isset($widgetOptions) && array_key_exists('allowMinimize', $widgetOptions) && $widgetOptions['allowMinimize']===FALSE)
        {
            $minimizeLink = "";
        } else {
            $minimizeLink = $this->renderMinimizeLink();
        }
        
        $title = $this->renderTitle($widgetOptions);
        
        $deleteLink = $this->renderRemoveLink();
        // $configLink = $this->renderConfigLink($args);
        $rotateLinks = $this->renderRotateLinks();
        $buttonEffects = $this->renderButtonEffects();
        
        $html = "
            $minimizeLink
            $title
            $deleteLink
            $rotateLinks
            $buttonEffects
        ";
        
        return $html;
    }
    
    public function renderTitle($widgetOptions = null)
    {
        $titleText = $this->renderTitleText($widgetOptions);
        return "<span class='widget-title'>Goals: <span class='widget-title-text'>".$titleText."</span></span>";
    }

    public function renderTitleText($widgetOptions)
    {
        $panels = $this->arrangePanels($widgetOptions);
        $titleText = $panels[0]['title'];

        if($titleText == "Lab Goals") {
            $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
            $titleText = $program->hasSkillsPractice() ? "Skills Practice Goals" : "Lab Goals";
        }

        return $titleText;
    }


    public function arrangePanels($widgetOptions)
    {
        $studentId = ($widgetOptions['explicitStudentId']) ? $widgetOptions['explicitStudentId'] : $this->widgetData->user->getCurrentRoleData()->id;
        $explicitPanelName = (isset($widgetOptions['explicitPanelName'])) ? $widgetOptions['explicitPanelName'] : null;

        // Check to see if the student has and LPI entries to determine if this widget should show on the dash...
        $practiceItemRepo = \Fisdap\EntityUtils::getRepository('PracticeItem');

        $lpis = $practiceItemRepo->findBy(array('student' => $studentId));

        // If there are no LPI's in existence for this user yet, don't show the widget.  Only show it if
        // there is at least one LPI instance in the DB for the student.

        // If we have an explicit panel name, use that
        if($explicitPanelName !== null)
        {
            $this->panels = $this->shiftToPanel('renderPanel'.$explicitPanelName,$this->panels);
        }
        else
        {
            if(count($lpis) > 0){
                // If student has LPIs, use lab skills
                $this->panels = $this->shiftToPanel('renderPanelLab',$this->panels);
            } else {
                // Otherwise use grad reqs
                $this->panels = $this->shiftToPanel('renderPanelGraduation',$this->panels);
            }
        }

        return $this->panels;
    }

    /**
     * This function returns the CSS class for the given percent.
     * 
     * @param integer $percent to find the class for.
     */
    private function getPercentClass($percent){
        if($percent >= 0 && $percent < 15){
            $percentClass = 'percent_0-14';
        }elseif($percent >= 15 && $percent < 30){
            $percentClass = 'percent_15-30';
        }elseif($percent >= 30 && $percent < 45){
            $percentClass = 'percent_30-44';
        }elseif($percent >= 45 && $percent < 65){
            $percentClass = 'percent_45-64';
        }elseif($percent >= 65 && $percent < 85){
            $percentClass = 'percent_65-84';
        }elseif($percent >= 85 && $percent < 100){
            $percentClass = 'percent_85-99';
        }elseif($percent >= 100){
            $percentClass = 'percent_100';
        }
        
        return $percentClass;
    }
    /**
     * This function returns a safe percentage given an actual and a goal
     * @return integer Percentage of actual out of goal
     */
    public function getPercent($actual, $goal, $options=array())
    {
        // Calculate the percentage
        if($goal == 0) {
            $percent =  100;
        } else {
            $percent =  floor(($actual / $goal) * 100);
        }

        // Apply upper and lower bounds to the percentage
        if(isset($options['lower'])) 
        {
            $percent = max($percent, $options['lower']);
        }
        if(isset($options['upper'])) {
            $percent = min($percent, $options['upper']);
        }
        
        return $percent;
    }

    /**
     * Get a scaled percent
     * @param  mixed $percent     The percentage value to scale
     * @param  float $scalar A scalar value use to transform the percent
     * @return float              Scaled percentage
     */
    public function getScaledPercent($percent, $scalar)
    {
        if($scalar == 0){
            return 100;
        } else {
            return ($percent / $scalar > 1) ? $percent / $scalar : 1;
        }
    }

    /**
     * This function wraps the config options in a modal and displays that modal on clicking 
     * the config icon.
     */
    protected function renderConfigLink(){
        if($this->widgetData->widget->has_configuration && ($this instanceof MyFisdap_Widgets_iConfigurable)){

            $args = func_get_args();

            $widgetOptions = json_encode($args[0][0], JSON_FORCE_OBJECT);

            $configId = $this->getNamespacedName('configure_widget');
            
            $imgLink = "<img class='widget-config' src='/images/icons/gear_white.png' id='$configId'/>";
            
            $formContents = "<div style='display: none'>" . $this->getConfigurationForm() . "</div>";
            
            $formId = $this->getConfigurationFormId();
            
            $script = "
                <script>
                    $(function(){
                        modalData_{$this->widgetData->id} = {
                            modal: true,
                            resizable: false,
                            draggable: false,
                            width: 500,
                            title: '{$this->widgetData->widget->display_title} Configuration',
                            buttons:{
                                'Cancel' : function() {
                                    $(this).dialog('close');
                                },
                                'Save' : function() {
                                    containerRef = $(this);
                                    
                                    form = $('#{$formId}').first();
                                    
                                    formData = form.serializeArray();
                                    
                                    dataObj = {};
                                    
                                    for(e in formData){
                                        dataObj[formData[e].name] = formData[e].value;
                                    }
                                    var throbber = $('<img src=\"/images/throbber_small.gif\" style=\"height:12px;width:12px;position:relative;top:-7px\">');
                                    throbber.appendTo(form);
                                    
                                    blockUi(true);

                                    saveWidgetData({$this->widgetData->id}, dataObj, function(){
                                        form.remove();
                                        reloadWidget({$this->widgetData->id},{$widgetOptions});
                                        //containerRef.dialog('destroy');
                                        blockUi(false);
                                    });
                                }
                            },
                            open: function (e, ui) {
                                $('.chzn-select').chosen();
                                $('form.goals-config-content').css('overflow, visible');
                            }
                        };
                        
                        $('#{$configId}').click(function(){
                            $('#{$formId}').attr('onSubmit', function(){ return false; });
                            $('#{$formId}').dialog(modalData_{$this->widgetData->id});
                        });
                    });
                </script>
            ";
            
            return $imgLink . $formContents . $script;
        }else{
            return '';
        }
    }
    
    /**
     * Only allow students.
     * @param integer $widgetId ID of the widget data entry, used to pull back the user assigned to that widget instance.
     * @return boolean True if the user can view this widget, false if it shouldn't show up.
     */
    public static function userCanUseWidget($widgetId){
        
        $widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId);
        
        $user = $widgetData->user;
// var_dump($user);die;
        if($widgetData->section == 'lab-skills-widgets') {
            return true;
        } elseif($widgetData->section == 'goals-widget' ){
            return true;
        } else {
            if(!$user->isInstructor()) {
                return true;
            } else {
                return false;
            }
            return false;
        }
    }

    public function getViewForPartials()
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        $script_pieces = explode("application", $view->getScriptPaths()[0]);

        // we need to make sure our current view can find our PHTML files for our partials
        $path_required = $script_pieces[0] . "/application/modules/my-fisdap/views/scripts";
        $add_path = true;

        if($view->getScriptPaths()){
            foreach($view->getScriptPaths() as $path){
                if($path == $path_required){
                    $add_path = false;
                }
            }
        }

        if($add_path){
            $view->addScriptPath($path_required);
        }

        return $view;
    }
}
