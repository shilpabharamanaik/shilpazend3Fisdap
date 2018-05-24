<?php namespace Fisdap\Data\DoctrineMigrations;

/**
 * Class PerconaOnlineSchemaChange
 *
 * WARNING: this tool does not support the migrations --dry-run flag!!!
 * (it doesn't seem like it can)
 *
 * A trait to be used by a Doctrine migration class that wants its schema changes to be
 * performed by the pt-online-schema-change tool instead of the native doctrine migrations
 * logic (which just makes regular SQL queries against hte DB.
 *
 * An example:
 * In your up() method:
 *          $this->alterTableWithOnlineSchemaChange('ShiftData', 'ADD COLUMN c1 INT');
 *
 * In your down() method:
 *          $this->alterTableWithOnlineSchemaChange('ShiftData', 'DROP COLUMN c1');
 *
 * @author jmortenson
 */
trait PerconaOnlineSchemaChange {

    /**
     * Alter a table using Percona Toolkit Online Schema Change. Call this from your up() / down() methods
     *
     * @param string $table Name of the table to be altered
     * @param string $alterSql Partial alter statement. See percona docs for pt-online-schema-change. Like "DROP COLUMN c1"
     */
    function alterTableWithOnlineSchemaChange($table, $alterSql) {
        $this->handleOnlineSchemaChangeResult(
            $this->execOnlineSchemaChange($table, $alterSql)
        );
    }

    /**
     * Write Migration output depending on the result of the schema change command.
     *
     * @param array $result Resulting output from the online schema change command
     */
    function handleOnlineSchemaChangeResult($result) {
        if ($result['success'] == TRUE) {
            $this->write($result['message']);
        } else {
            $this->write('Online schema change failed. Error given: ' . $result['message']);
            $this->write($result['log']);
        }
    }

    /**
     * Make associate array of database credentials, using the Connecction object obtained from the Migration class
     * @return array
     */
    function obtainDBCredentials() {
        $dbParams = $this->connection->getParams();

        return array(
            'username' => $dbParams['user'],
            'password' => $dbParams['password'],
            'host' => $dbParams['host'],
            'port' => 3306,
            'name' => $dbParams['dbname'],
        );
    }

    /**
     * Actually execute the pt-online-schema-change command
     *
     * @param string $table Name of the table to be altered
     * @param string $alterSql Alter SQL in a format that pt-online-schema-change can handle
     * @param bool $execute Should the alter be executed? Right now this is always true.
     * @return array Output from the command
     */
    function execOnlineSchemaChange($table, $alterSql, $execute = TRUE) {
        $creds = $this->obtainDBCredentials();
        $dsn = "D={$creds['name']},t={$table},u={$creds['username']},p='{$creds['password']}',h={$creds['host']},P={$creds['port']}";
        $cmdBase = "pt-online-schema-change --alter '{$alterSql}' --no-check-alter --alter-foreign-keys-method=auto";
        $executeFlag = '--execute';
        if ($execute) {
            $cmd = array($cmdBase, $dsn, $executeFlag);
        } else {
            $cmd = array($cmdBase, $dsn);
        }
        $this->write(implode(' ', $cmd));
        //return array('success' => TRUE);
        $output = shell_exec(implode(' ', $cmd));
        $outputLines = explode('\n', $output);
        // Possible relevant messages are like:
        // Successfully altered `FISDAP`.`ShiftData`.
        // `FISDAP`.`ShiftData` was not altered.
        foreach(array_reverse($outputLines) as $line) {
            if (stripos($line, 'Successfully') !== FALSE) {
                // alter ran successfully
                return [
                    'success' => TRUE,
                    'message' => $line,
                    'log' => $output,
                ];
            } else if (stripos($line, 'Error') !== FALSE) {
                // alter failed, error
                return [
                    'success' => FALSE,
                    'message' => $line,
                    'error' => $line,
                    'log' => $output,
                ];
            }
        }

    }

}