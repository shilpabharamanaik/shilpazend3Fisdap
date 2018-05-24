<?php namespace Fisdap\Logging;

use Doctrine\DBAL\Logging\SQLLogger;
use Log;


/**
 * Doctrine SQL logger for use with Monolog
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class MonologSQLLogger implements SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        Log::debug('Doctrine SQL: ' . $sql, ['params' => $params, 'types' => $types]);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
