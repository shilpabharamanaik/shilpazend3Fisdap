<?php

/**
 * Class Zend_Session_SaveHandler_Couchbase
 * @author Ben Getsug (bgetsug@fisdap.net)
 */
class Zend_Session_SaveHandler_Couchbase implements Zend_Session_SaveHandler_Interface
{

    /**
     * Session save path
     *
     * @var string
     */
    protected $_sessionSavePath;

    /**
     * Session name
     *
     * @var string
     */
    protected $_sessionName;


    /**
     * Couchbase Client
     *
     * @var Couchbase
     */
    protected $_couchbase;

    /**
     * Session lifetime
     *
     * @var int
     */
    protected $_sessionLifetime;


    /**
     * @param $config
     *
     * @throws Zend_Session_SaveHandler_Exception
     */
    public function __construct($config)
    {
        if (!extension_loaded('couchbase')) {
            throw new Zend_Session_SaveHandler_Exception('The couchbase extension must be loaded for using this session save handler!');
        }

        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            throw new Zend_Session_SaveHandler_Exception(
                '$config must be an instance of Zend_Config or array of key/value pairs containing '
                . 'configuration options for Zend_Session_SaveHandler_Couchbase.'
            );
        }

        // Couchbase configuration
        $hosts      = isset($config['hosts'])      ? $config['hosts']      : array('localhost');
        $user       = isset($config['user'])       ? $config['user']       : '';
        $password   = isset($config['password'])   ? $config['password']   : '';
        $bucket     = isset($config['bucket'])     ? $config['bucket']     : 'default';
        $persistent = isset($config['persistent']) ? $config['persistent'] : true;

        //$this->_couchbase = new \Couchbase($hosts, $user, $password, $bucket, $persistent);
        //print_r($config); exit;
        //$bucketName = "beer-sample";
        //echo Zend_Version::getLatest(); exit;
        // Establish username and password for bucket-access
        $authenticator = new \Couchbase\PasswordAuthenticator();
        $authenticator->username('Administrator')->password('Fisdap123');
        
        // Connect to Couchbase Server
        $cluster = new CouchbaseCluster('couchbase://127.0.0.1');

        // Authenticate, then open bucket
        $cluster->authenticate($authenticator);
        $this->_couchbase = $cluster->openBucket($bucket);

        $this->_sessionLifetime = isset($config['session']['lifetime']) ? $config['session']['lifetime'] : 28800;
    }


    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }


    /**
     * {@inheritDoc}
     */
    public function open($save_path, $name)
    {
        $this->_sessionSavePath = $save_path;
        $this->_sessionName     = $name;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function read($id)
    {
        return $this->_couchbase->get($id);
    }

    /**
     * @param string $id
     * @param string $data
     *
     * @return bool
     * @throws Zend_Exception
     */
    public function write($id, $data)
    {
        if ($id === null) {
            return false;
        }

        try {
            // must cast $data as string
            $this->_couchbase->set($id, (string) $data, $this->_sessionLifetime);
            return true;
        } catch (Exception $e) {
            if (Zend_Registry::isRegistered('exceptionLogger')) {
                Zend_Registry::get('exceptionLogger')->log($e);
            }

            return false;
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     * @throws Zend_Exception
     */
    public function destroy($id)
    {
        try {
            $this->_couchbase->delete($id);
            return true;
        } catch (Exception $e) {
            if (Zend_Registry::isRegistered('exceptionLogger')) {
                Zend_Registry::get('exceptionLogger')->log($e);
            }

            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }


    /**
     * @return Couchbase
     */
    public function getCouchbase()
    {
        return $this->_couchbase;
    }
}
