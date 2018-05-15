<?php

class Fisdap_TemplateMailer extends Zend_Mail
{
    const FROM_NAME = "Fisdap Robot";
    const FROM_EMAIL = "fisdap-robot@fisdap.net";


    /**
     * @var Zend_View
     */
    public static $_defaultView;

    /**
     * current instance of our Zend_View
     *
     * @var Zend_View
     */
    protected $_view;

    /**
     * @var string
     */
    protected $_signature;


    /**
     * @param string $charset
     */
    public function __construct($charset = 'iso-8859-1')
    {
        parent::__construct($charset);
        $this->setDefaultFrom(self::FROM_EMAIL, self::FROM_NAME);
        $this->_view = self::getDefaultView();
    }


    /**
     * @return Zend_View
     */
    protected static function getDefaultView()
    {
        if (self::$_defaultView === null) {
            self::$_defaultView = new Zend_View();
            self::$_defaultView->setScriptPath(
                APPLICATION_PATH .
                '/views/scripts/email-templates'
            );
        }

        return self::$_defaultView;
    }


    /**
     * @param string $template
     * @param string $encoding
     */
    public function sendHtmlTemplate($template, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $html = $this->_view->render($template);

        if (isset($this->_signature)) {
            $html .= $this->_signature;
        } else {
            $html .= $this->getDefaultSignature("<br>");
        }

        $this->setBodyHtml($html, $this->getCharset(), $encoding);
        $this->send();
    }


    /**
     * @param string $template
     * @param string $encoding
     *
     * @return false|string|Zend_Mime_Part
     */
    public function getHtmlTemplateBody($template, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        $html = $this->_view->render($template);

        if (isset($this->_signature)) {
            $html .= $this->_signature;
        } else {
            $html .= $this->getDefaultSignature("<br>");
        }

        $this->setBodyHtml($html, $this->getCharset(), $encoding);

        return $this->getBodyHtml(true);
    }


    /**
     * @param string $template
     *
     * @return string
     */
    public function getTemplateBody($template)
    {
        $html = $this->_view->render($template);

        return $html;
    }


    /**
     * @param string $template
     */
    public function sendTextTemplate($template)
    {
        $text = $this->_view->render($template);

        if (isset($this->_signature)) {
            $text .= $this->_signature;
        } else {
            $text .= $this->getDefaultSignature();
        }

        $this->setBodyText($text);
        $this->send();
    }


    /**
     * @param string $property
     * @param mixed  $value
     *
     * @return $this
     * @throws Zend_View_Exception
     */
    public function setViewParam($property, $value)
    {
        $this->_view->__set($property, $value);

        return $this;
    }


    /**
     * @param array $params
     *
     * @return $this
     * @throws Zend_View_Exception
     */
    public function setViewParams($params)
    {
        foreach ($params as $property => $value) {
            $this->_view->__set($property, $value);
        }

        return $this;
    }


    /**
     * @param $sig
     */
    public function setSignature($sig)
    {
        $this->_signature = $sig;
    }


    /**
     * @param string $lineBreak
     *
     * @return string
     */
    public function getDefaultSignature($lineBreak = "\n")
    {
        $sig = $lineBreak . $lineBreak . "Stay safe." . $lineBreak . $lineBreak
            . "Fisdap Robot" . $lineBreak
            . "Director of Automated Communications" . $lineBreak
            . "fisdap-robot@fisdap.net" . $lineBreak
            . "651-690-9241" . $lineBreak . $lineBreak
            . "Please do not reply to this email.";

        return $sig;
    }


    /**
     * @param null $transport
     *
     * @return void|Zend_Mail
     * @throws Zend_Exception
     */
    public function send($transport = null)
    {
        try {
            parent::send($transport);
        } catch (\Exception $e) {
            /** @var ExceptionLogger $exceptionLogger */
            $exceptionLogger = \Zend_Registry::get('exceptionLogger');
            $exceptionLogger->log($e);
        }
    }
}
