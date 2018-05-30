<?php

use Fisdap\Members\Scheduler\SchedulerHelper;

/**
 * Class to wrap up the PDF generation functionality.
 */
class PdfController extends Zend_Controller_Action
{
    /**
     * @var Illuminate\Container\Container;
     */
    protected $container;

    public function init()
    {
        $this->container = \Zend_Registry::get('container');
    }

    /**
     * This action creates a wkhtmltopdf document based on the posted HTML
     * Sends HTML headers for file to download (prompts download dialog)
     */
    public function createPdfAction()
    {
        $formValues = $this->getAllParams();
        list($options, $pdfName, $incomingBody, $incomingHead) = $this->getPdfOptionsFromParams($formValues);

        $pdfGenerator = $this->getPDFGeneratorFromContainer();
        $pdfGenerator->setFilename($pdfName);
        $pdfGenerator->setOrientation($options['pageOrientation']);
        $pdfGenerator->generatePdfFromHtmlString($incomingBody, false, $incomingHead);
        $pdfGenerator->outputPdfToBrowser();

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        return;
    }
    
    /**
     * This action creates a wkhtmltopdf document based on the posted HTML
     * and then emails the resulting pdf
     */
    public function emailPdfAction()
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();

        $formValues = $this->getAllParams();
        list($options, $pdfName, $incomingBody, $incomingHead) = $this->getPdfOptionsFromParams($formValues);

        $pdfGenerator = $this->getPDFGeneratorFromContainer();
        $pdfGenerator->setFilename($pdfName);
        $pdfGenerator->setOrientation($options['pageOrientation']);
        $pdfGenerator->generatePdfFromHtmlString($incomingBody, false, $incomingHead);

        $schedulerHelper = new SchedulerHelper();
        $schedulerHelper->emailPdf($pdfGenerator->getPdfContent(), $user, $formValues);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        return;
    }
    
    public function createPdfFromHtmlAction($decode = false)
    {
        $pdfName = ($decode) ? urldecode($this->_getParam('pdfName')) : $this->_getParam('pdfName');

        $pdfGenerator = $this->getPDFGeneratorFromContainer();
        $pdfGenerator->setFilename($pdfName);
        $pdfGenerator->generatePdfFromHtmlString(urldecode($this->_getParam('pdfContents')));
        $pdfGenerator->outputPdfToBrowser();

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        return;
    }

    /**
     * Generate an image resource to replace a CANVAS signature, because wkhtmltopdf cannot handle inline IMG SRC="data:..." images
     *
     *
     * @param array $options OPTIONAL; the options for image creation
     * imageSize => array(width, height)
     * bgColour => array(red, green, blue)
     * penWidth => int
     * penColour => array(red, green, blue)
     */
    public function sigImageAction()
    {
        $signatureId = $this->_getParam('signatureId');
        if (is_numeric($signatureId) && $signatureId > 0) {
            $signature = \Fisdap\EntityUtils::getEntity('Signature', $signatureId);
        }
        // if we successfully loaded a signature...
        if ($signature->id) {
            require_once 'ThomasJBradley/signature-to-image.php';
            $json = $signature->signature_string;
            
            // get options
            $options = array();
            $height = $this->_getParam('height');
            if (!is_numeric($height) || $height <= 0) {
                $height = false;
            }
            $width = $this->_getParam('width');
            if (!is_numeric($width) || $width <= 0) {
                $width = false;
            }
            if ($height && $width) {
                $options['imageSize'] = array($width, $height);
            }

            // generate image
            $image = sigJsonToImage($json, $options);

            // output the image to the visitor
            header('Content-Type: image/png');
            imagepng($image);
            imagedestroy($image);
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        } else {
            header("HTTP/1.0 404 Not Found");
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            return;
        }
    }

    /**
     * @param $formValues
     * @return array
     */
    private function getPdfOptionsFromParams($formValues)
    {
        // check if the incoming content is HTML encoded
        if ($formValues['contentEncoded']) {
            $decode = true;
        } else {
            $decode = false;
        }

        // get all the options, too
        $options = array();
        if ($formValues['orientation']) {
            $options['pageOrientation'] = lcfirst($formValues['orientation']);
        }

        // This will just be the name the PDF will output as...
        $pdfName = ($decode) ? urldecode($formValues['pdfName']) : $formValues['pdfName'];

        // Styles will be stored in here...
        $incomingHead = ($decode) ? urldecode($formValues['htmlHead']) : $formValues['htmlHead'];

        // Body content
        $incomingBody = ($decode) ? urldecode($formValues['pdfContents']) : $formValues['pdfContents'];

        return array($options, $pdfName, $incomingBody, $incomingHead);
    }

    /**
     * @return mixed
     * @throws Zend_Exception
     */
    private function getPDFGeneratorFromContainer()
    {
        $pdfGenerator = $this->container->make('Fisdap\Service\DataExport\PdfGenerator', array(\Zend_Registry::get('logger')));
        return $pdfGenerator;
    }
}
