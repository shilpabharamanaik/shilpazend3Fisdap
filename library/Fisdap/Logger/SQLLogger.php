<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

namespace Fisdap\Logger;

/**
 * Description of SQLLogger
 *
 * @author astevenson
 */
class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
	static $queries = array();
	
	static $uniqueQueries = array();
	
	static $queryStartTime = null;
	static $queryTotalTime = 0;
	
	static $timeSpent = 0;
	
	public function startQuery($sql, array $params = null, array $types = null){
		self::$queryStartTime = microtime(true);
		$startTime = microtime(true);
		
		$atom = array();
		$atom['query'] = $sql;
		
		if($params){
			$atom['params'] = $params;
		}
		
		if($types){
			$atom['types'] = $types;
		}
		
		$atom['backtrace'] = debug_backtrace();
		
		
		if(strpos($sql, 'indow') !== false){
			//echo $sql;
		}
		
		self::$queries[] = $atom;
		
		self::$timeSpent += (microtime(true) - $startTime);
	}

	static function getQueries(){
		return self::$queries;
	}
	
    public function stopQuery(){
		$queryTime = (microtime(true) - self::$queryStartTime);
		
		$previousElementNumber = count(self::$queries)-1;
		
		self::$queries[$previousElementNumber]['time'] = $queryTime;
		
		self::$uniqueQueries[self::$queries[$previousElementNumber]['query']]['time'] += $queryTime;
		self::$uniqueQueries[self::$queries[$previousElementNumber]['query']]['count']++;
		
		self::$queryTotalTime += $queryTime;
		self::$queryStartTime = null;
	}
	
	static function echoQueries(){
		$startTime = microtime(true);
		
		$qs = self::$queries;
		
		$html = "<div><a href='#' id='show-queries'>Show/hide queries</a></div>";
		
		$html .= "<div id='sql-entries' style='display: none'>";
		
		for($i = 0; $i<count($qs); $i++){
			$q = $qs[$i];
			
			$paramsOut = "";
			if(isset($q['params'])){
				$paramsOut = "Params: (";
				foreach($q['params'] as $param){
					if($param instanceof \DateTime){
						$paramsOut .= $param->format("[Y-m-d H:i:s] ");
					}elseif(is_object($param)){
						$paramsOut .= "[Instance of " . get_class($param) . "] ";
					}else{
						$paramsOut .= "[" . $param . "]";
					}
				}
				$paramsOut .= ")<br/>";
				//try{
					//$paramText = "Params: (" . implode(', ', $q['params']) . ") <br />";
				//}catch(Exception $e){
					//$paramText = count($params) . " params found, could not convert to string. ";
				//}
			}else{
				$paramsOut = "";
			}
			
			//Haaaaaack.
			// Loop until we find a Fisdap Entity doing something...
			$trace = null;
			foreach($q['backtrace'] as $bt){
				if(strpos($bt['file'], 'Fisdap') !== false){
					$trace = $bt;
					break;
				}
			}
			
			$html .=  "#" . $i . ": " . $q['query'] . "<br />";
			$html .= $paramsOut;
			
			//if($trace){
			//	$html .= "Backtrace: " . $trace['file'] . " line " . $trace['line'] . " (best guess)<br />";
			//}
			foreach ($q['backtrace'] as $bt) {
				$html .= $bt['file'] . " line " . $bt['line'] . "<br />";
			}
			
			if($q['time'] > 1){
				$html .= "Time: <b>SLOW QUERY</b>" . $q['time'] . "<br />";
			}else{
				$html .= "Time: " . $q['time'] . "<br />";
			}
			
			$html .= "<br />";
		}
		
		$html .= "</div>";
		$html .= "<br />"; 
		$html .= "<div><a href='#' id='show-query-times'>Show/hide query time summary</a></div>";
		$html .= "<div id='sql-times' style='display: none'>";
		
		foreach(self::$uniqueQueries as $queryText => $queryData){
			$html .= $queryText . "<br />" . "Run: <b>" . $queryData['count'] . "</b> times; Total time: <b>" . $queryData['time'] . "</b><br /><br />"; 
		}
				
		$html .= "</div>";
		
		$html .= "
				<script>
					$(function(){
						$('#sql-entries').hide();
						$('#show-queries').click(function(){ $('#sql-entries').toggle(); return false; } );
						
						$('#sql-times').hide();
						$('#show-query-times').click(function(){ $('#sql-times').toggle(); return false; } );
						
					});
				</script>
		";
		
		self::$timeSpent += (microtime(true) - $startTime);
		
		$html .= "<br />";
		$html .= "<div class='grid_12'>Debugging took " . self::$timeSpent . "s.</div>";
		$html .= "<div class='grid_12'>Total time in Doctrine2 Queryland: " . self::$queryTotalTime . "s.</div>";
		
		return "<div class='debugging'>" . $html . "</div>";
	}
}
