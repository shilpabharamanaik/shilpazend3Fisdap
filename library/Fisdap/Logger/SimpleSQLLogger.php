<?php


namespace Fisdap\Logger;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A SQL logger that logs to Zend_Log
 */
class SimpleSQLLogger implements SQLLogger
{
    public static $queryCount = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        self::$queryCount++;
        $query = "#" . self::$queryCount . " - " . $sql . PHP_EOL;

//        if ($params) {
//            $query .= print_r($params, true) . PHP_EOL;
//        }
//
//        if ($types) {
//            $query .= print_r($types, true) . PHP_EOL;
//        }

        $backtrace = debug_backtrace();
        foreach ($backtrace as $bt) {
            if (strpos($bt['file'], 'Fisdap') !== false) {
                $query .= "Backtrace: " . $bt['file'] . " line " . $bt['line'] . " (best guess)" . PHP_EOL;
                break;
            }
        }


        \Zend_Registry::get('logger')->debug($query);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
