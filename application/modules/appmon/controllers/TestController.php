<?php

use Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway;
use Psr\Log\LoggerInterface;

/**
 * Class Appmon_TestController
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Appmon_TestController extends Zend_Controller_Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    public function indexAction()
    {
        $this->_helper->json('test index', true);
    }


    /**
     * @param ShiftAttachmentsGateway $shiftAttachmentsGateway
     */
    public function mrapiAction(ShiftAttachmentsGateway $shiftAttachmentsGateway)
    {
        $time_start = microtime(true);

        $shiftAttachmentCategories = $shiftAttachmentsGateway->getCategories();

        $time_end = microtime(true);

        $this->_helper->json(
            [
                'shiftAttachmentCategories' => $shiftAttachmentCategories,
                'responseTime' => $time_end - $time_start
            ],
            true
        );
    }


    public function jsonAction()
    {
        $foo = 'bar';
        $this->logger->debug('$foo: ' . print_r($foo, true));
        $this->_helper->json($foo, true);
    }


    public function fatalAction()
    {
        throw_a_fatal_error();
    }


    public function diffFatalAction()
    {
        throw_a_different_fatal_error();
    }


    public function oomAction()
    {
        $data = '';

        while (true) {
            $data .= str_repeat('#', PHP_INT_MAX);
        }
    }


    public function exceptionAction()
    {
        throw new Exception('d\'oh!');
    }


    public function userErrorAction()
    {
        trigger_error("This is an error!", E_USER_ERROR);

        $this->_helper->json('Check the logs for an error', true);
    }


    public function userWarningAction()
    {
        trigger_error('This is a warning!', E_USER_WARNING);

        $this->_helper->json('Check the logs for a warning', true);
    }


    public function userNoticeAction()
    {
        trigger_error("This is a notice!");

        $this->_helper->json("Check the logs for a notice, if they're not filtered out", true);
    }


    public function eNoticeAction()
    {
        $aArray = array();
        $aArray['someKey'];
        $this->_helper->json("Check the logs for a notice, if they're not filtered out", true);
    }


    public function eWarningAction()
    {
        $this->_expectsArray(null);

        $this->_helper->json('Check the logs for a warning', true);
    }


    protected function _expectsArray($array)
    {
        $keys = array_keys($array);
    }
}
