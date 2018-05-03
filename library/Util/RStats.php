<?php
/**
 * User: jmortenson
 * Date: 3/21/14
 * Time: 12:01 PM
 */
namespace Util;

/**
 * Client for running statistics calculations through R statistics app
 * by using the Rserve server
 * Fisdap wrapper class for the Rserve php client library
 */
class RStats {
    /**
     * @var null|Rserve_Connection Connection to an Rserve server
     */
    private $rserveConnection = NULL;

    public function __construct($host) {
        require_once 'rserve/Connection.php';

        // try connecting to Rserve (R server)
        try {
            $this->rserveConnection = new \Rserve_Connection($host);
        } catch (Rserve_Exception $e) {
            throw new Exception('Unable to connect to Rserve server at host ' . $host . '. Unable to complete report.');
        }
    }

    /**
     * Run an R script (string) and return results as simple array
     *
     * @param $rScript string rScript code to run
     * @return array Results of calculation
     */
    public function runRScript($rScript = '') {
        $rResult = $this->rserveConnection->evalString($rScript);

        return $rResult;
    }

    /**
     * Create the R script that will create a data frame in R for a provided table of vectors
     *
     * @param $name String Variable name for the data frame
     * @param $table Array An array of named vectors to be added to the data frame
     *
     * @return string R Script string
     */
    public static function makeRDataFrameText($name, $table) {
        $vectors = array();
        foreach($table as $vectorName => $vector) {
            $vectorString = self::sanitizeRVariableName($vectorName) . ' <- c(';
            // wrap any non-numeric values with quotes, so they are handled as strings
            foreach($vector as $key => $element) {
                if (!is_numeric($element)) {
                    $vector[$key] = '"' . $element . '"';
                }
            }
            $vectorString .= implode(',', $vector);
            $vectorString .= ')';
            $vectors[$vectorName] = $vectorString;
        }
        $rString = implode("\n", $vectors);
        $rString .= "\n" . $name .  " <- data.frame(";
        foreach($vectors as $vectorName => $data) {
            $vectors[$vectorName] = $vectorName . '=' . self::sanitizeRVariableName($vectorName);
        }
        $rString .= implode(',', $vectors);
        $rString .= ')';

        return $rString;
    }

    /**
     * Simple utility to sanitize R variable names to avoid R syntax errors
     *
     * @param $name Name of the proposed R variable
     * @return string Sanitized name of the proposed R variable
     */
    public static function sanitizeRVariableName($name) {
        return strtolower(preg_replace('/[^a-z]+/i', '', $name));
    }
}