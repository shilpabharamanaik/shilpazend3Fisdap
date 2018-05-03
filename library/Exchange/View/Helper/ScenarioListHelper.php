<?php

class Exchange_View_Helper_ScenarioListHelper extends Zend_View_Helper_Abstract 
{ 
    function scenarioListHelper($scenarioList, $isUserlist, $isStaff=false) { 
        $html = "";
        
        if($isUserlist){
        	$html .= "<h2 class='page-sub-title'>Your scenarios</h2>";
        }else{
        	$html .= "<h2 class='page-sub-title'>Available for review</h2>";
        }
        
        $html .= "
        	<div class='grid_12'>
        		<table class='scenario_table'>
        			<thead>
        				<td><a id='sort_by'>Status</a></td>
        				<td><a id='sort_by_title'>Title</a></td>
        				<td>Description</td>
        				<td>Tools</td>
        			</thead>
        ";
        
        foreach($scenarioList as $scenario){
        	$html .= "<tr>";
        
        	$validIcon = "pending";
        
        	switch($scenario->state->name){
        		case "Valid":
        			$validIcon = "valid";
        			break;
        		case "Invalid":
        			$validIcon = "invalid";
        			break;
        	}
        
        	$html .= "<td><img src='/images/icons/scenario-" . $validIcon . ".png' /></td>";
        	$html .= "<td>" . $scenario->title . "</td>";
        	
        	$description = $scenario->getDescription();
        	
        	$html .= "<td>" . $description . "</td>";
        	
        	$html .= "<td>";
        	
        	$editImage = "/images/icons/edit.png";
        	$editAlttext = "Edit";
        	
        	if(!$isUserlist){
        		$editImage = "/images/icons/scores.png";
        		
        		if($isStaff){
        			// Show the counts of all reviews...
	        		if(count($scenario->reviews) > 0){
	        			$editImage = "/images/icons/scores_black.png";
	        			$editAlttext = count($scenario->reviews) . " Review(s)";
	        		}
        		}else{
        			$repo = \Fisdap\EntityUtils::getRepository('ScenarioReview');
        			$review = $repo->findOneBy(array('reviewer' => \Fisdap\Entity\User::getLoggedInUser(), 'scenario' => $scenario));
        			
        			if($review){
        				$editImage = "/images/icons/scores_black.png";
        				$editAlttext = "Edit Review";
        			}else{
        				$editAlttext = "Add Review";
        			}
        		}
        	}
        	
        	$html .= "<a id='edit_button' class='edit_button' data-scenarioid='" . $scenario->id . "' href='#'><img src='{$editImage}' class='tool_icon' alt='{$editAlttext}' title='{$editAlttext}' /></a>";
        	
        	if($isStaff){
        		$html .= " <a id='delete_button' class='delete_button' data-scenarioid='" . $scenario->id . "' href='#'><img src='/images/icons/delete.png' class='tool_icon' alt='Delete' title='Delete' /></a>";
        	}
        	
        	$html .= "</td>";
        	
        	$html .= "</tr>";
        }
        
        $html .= "
        		</table>
        	</div>
        	<div class='clear'></div>
        ";
        
        return $html;
    } 
}