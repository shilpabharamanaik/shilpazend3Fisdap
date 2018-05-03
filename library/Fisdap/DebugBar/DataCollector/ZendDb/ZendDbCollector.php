<?php
namespace Fisdap\DebugBar\DataCollector\ZendDb;



/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 11/11/14
 * Time: 3:11 PM
 */

class ZendDbCollector extends \DebugBar\DataCollector\PDO\PDOCollector
{
    public function getWidgets()
    {
        return array(
            "zendDB" => array(
                "icon" => "inbox",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => "pdo",
                "default" => "[]"
            ),
            "zendDB:badge" => array(
                "map" => "pdo.nb_statements",
                "default" => 0
            )
        );
    }
}

