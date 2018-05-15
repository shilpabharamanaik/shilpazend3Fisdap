<?php namespace Fisdap\Service\DataExport;

use Psr\Log\LoggerInterface;

/**
 * Provides PDF Generation using the wkhtmltopdf binary. This is a refactor of former class wkhtmltopdf_Pdf
 *
 * @package Fisdap\Service
 */
class WkhtmlPdfGenerator implements PdfGenerator
{
    /**
     * Largely utilizes open source code from the Drupal print project (print_pdf.module)
     * http://drupal.org/project/print
     *
     * Provides pdf generation via wkhtmltopdf library
     * http://code.google.com/p/wkhtmltopdf/
     */

    /**
     * @var LoggerInterface The logging engine
     */
    protected $logger;

    /**
     * @var string $pdfContent The content of the PDF, once generated
     */
    protected $pdfContent;

    /**
     * @var string Path to the wkhtmltopdf binary - assumes the wkhtmltopdf utility has been symlinked into the server system path
     */
    protected $libraryCommand = '/usr/local/bin/wkhtmltopdf';

    /**
     * @var string Default commandline options for the library
     */
    protected $defaultCommandOptions = "--footer-font-size 7 --footer-right '[page]'";

    /**
     * @var string Default paper size. See $this->paperSizeOptions for options
     */
    protected $defaultPaperSize = 'LETTER';

    /**
     * @var array Available paper size options
     */
    protected $paperSizeOptions = array('4A0' => '4A0', '2A0' => '2A0', 'A0' => 'A0',
        'A1' => 'A1', 'A2' => 'A2', 'A3' => 'A3', 'A4' => 'A4',
        'A5' => 'A5', 'A6' => 'A6', 'A7' => 'A7', 'A8' => 'A8',
        'A9' => 'A9', 'A10' => 'A10', 'B0' => 'B0', 'B1' => 'B1',
        'B2' => 'B2', 'B3' => 'B3', 'B4' => 'B4', 'B5' => 'B5',
        'B6' => 'B6', 'B7' => 'B7', 'B8' => 'B8', 'B9' => 'B9',
        'B10' => 'B10', 'C0' => 'C0', 'C1' => 'C1', 'C2' => 'C2',
        'C3' => 'C3', 'C4' => 'C4', 'C5' => 'C5', 'C6' => 'C6',
        'C7' => 'C7', 'C8' => 'C8', 'C9' => 'C9', 'C10' => 'C10',
        'RA0' => 'RA0', 'RA1' => 'RA1', 'RA2' => 'RA2',
        'RA3' => 'RA3', 'RA4' => 'RA4', 'SRA0' => 'SRA0',
        'SRA1' => 'SRA1', 'SRA2' => 'SRA2', 'SRA3' => 'SRA3',
        'SRA4' => 'SRA4', 'LETTER' => 'Letter', 'LEGAL' => 'Legal',
        'EXECUTIVE' => 'Executive', 'FOLIO' => 'Folio',
    );

    /**
     * @var string Default page orientation. See $this->pageOrientationOptions for options
     */
    protected $defaultPageOrientation = 'portrait';

    /**
     * @var array Available page orientation options
     */
    protected $pageOrientationOptions = array('portrait' => 'Portrait', 'landscape' => 'Landscape');

    /**
     * @var int DPI to use
     */
    protected $dpi = 96;

    /**
     * @var string Filename to be used if PDF is output to browser as a downloadable file
     */
    protected $filename;

    /**
     * @var string The paper size to use for PDF generation. See $this->paperSizeOptions for options.
     */
    protected $paperSize;

    /**
     * @var string The page orientation to use for PDF generation. See $this->pageOrientationOptions for options.
     */
    protected $pageOrientation;


    /**
     * Construct the class: set up a logger.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilename($pdfFilename)
    {
        $this->filename = $pdfFilename;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaperSize($paperSize)
    {
        $this->paperSize = $paperSize;
    }

    /**
     * {@inheritDoc}
     */
    public function setOrientation($orientation)
    {
        $this->pageOrientation = $orientation;
    }

    /**
     * Find out the version of the wkhtmltopdf binary
     *
     * @return mixed
     */
    public function libraryVersion()
    {
        $descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));

        $cmd = realpath($this->getBinaryCommand()) . ' --version';
        $process = proc_open($cmd, $descriptor, $pipes, null, null);
        if (is_resource($process)) {
            $content = stream_get_contents($pipes[1]);
            $out = preg_match('!.*?(\d+\.\d+\.\d+).*$!m', $content, $matches);
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $retval = proc_terminate($process);
        }

        return ($matches[1]);
    }

    /**
     * Generate the PDF file using wkhtmltopdf binary
     *
     * @todo - refactor this to use the Symfony Process component to manage process creation and output/error capture
     * {@inheritDoc}
     */
    public function generatePdfFromHtmlString($html, $isCompleteHtmlDocument = true, $separateHeadContent = '')
    {
        // make sure options are set
        $this->validatePdfOptionsAndSetIfAbsent();

        // If $html is not a complete HTML document, we need to wrap it properly
        if (!$isCompleteHtmlDocument) {
            $html = $this->wrapHtmlBodyContent($html, $separateHeadContent);
        }

        $commandOptions = '';
        // removing the clause that would disable local file access. Right now Fisdap code needs local file access to work.
        // 0.10.0 beta2 identifies itself as 0.9.9
        // $version = $this->libraryVersion();
        //if (version_compare($version, '0.9.9', '>=')) {
        //	$commandOptions = '--disable-local-file-access '. $this->defaultCommandOptions;
        //}

        $descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $cmd = realpath($this->getBinaryCommand()) . " --margin-bottom 7mm --margin-top 7mm --margin-left 5mm --margin-right 5mm --page-size {$this->paperSize} --orientation {$this->pageOrientation} --dpi {$this->dpi} {$commandOptions} - -";
        $this->logger->debug('About to attempt wkhtmltopdf command: ' . $cmd);
        $process = proc_open($cmd, $descriptor, $pipes, null, null);

        if (is_resource($process)) {
            fwrite($pipes[0], $html);
            fclose($pipes[0]);

            $pdf = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            stream_set_blocking($pipes[2], 0);
            $cliOutput = stream_get_contents($pipes[2]);
            if (!empty($cliOutput)) {
                // @todo real error here watchdog('print_pdf', 'wkhtmltopdf: '. $cliOutput);
                //throw new \Exception("PDF Error: " . print_r($cliOutput, true));
                //$this->logger->debug(print_r($cliOutput, true));
            }
            fclose($pipes[2]);

            $retval = proc_terminate($process);

            $this->pdfContent = $pdf;
        } else {
            throw new \Exception("Failed to open wkthmltopdf process to generate PDF content");
        }
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function outputPdfToBrowser()
    {
        if (headers_sent()) {
            throw new \Exception("Unable to stream pdf: headers already sent");
        }

        // Make sure PDF has been generated, throw exception if not.
        $this->verifyPdfIsGenerated();

        // Make sure we have a filename set
        $this->validateFilenameAndSetIfAbsent();

        // Send headers
        header("Cache-Control: private");
        // unsetting pragma header to avoid IE8 download problem per
        // http://mark.koli.ch/2009/10/internet-explorer-cant-open-file-via-https-try-removing-the-pragma-header.html
        header("Pragma: ");
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"$this->filename\"");

        echo $this->pdfContent;
        flush();
        return true;
    }


    /**
     * {@inheritDoc}
     */
    public function generatePdfFromHtmlFiles(array $fileList)
    {
        // make sure incoming options are sane. Don't want to pass arbitrary text to commandline
        $this->validatePdfOptionsAndSetIfAbsent();

        // Make sure PDF has been generated, throw exception if not.
        $this->verifyPdfIsGenerated();

        // Make sure a filename is set
        $this->validateFilenameAndSetIfAbsent();

        $version = $this->libraryVersion();

        // 0.10.0 beta2 identifies itself as 0.9.9
        if (version_compare($version, '0.9.9', '>=')) {
            $commandOptions = '--disable-local-file-access ' . $this->defaultCommandOptions;
        }

        $descriptor = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $cmd = realpath($this->getBinaryCommand()) . " --page-size $this->paperSize --orientation $this->pageOrientation --dpi $this->dpi $commandOptions ";

        foreach ($fileList as $file) {
            $cmd .= $file . " ";
        }

        $cmd .= " " . $this->filename;

        passthru($cmd);
    }

    /**
     * {@inheritDoc}
     */
    public function getPdfContent()
    {
        // Make sure PDF has been generated, throw exception if not.
        $this->verifyPdfIsGenerated();

        return $this->pdfContent;
    }

    /**
     * Determine whether to use the new or old library command
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getBinaryCommand()
    {
        if (! file_exists($this->libraryCommand)) {
            throw new \Exception("{$this->libraryCommand} could not be found on the system");
        }

        return $this->libraryCommand;
    }

    /**
     * @param int $length
     *
     * @return string
     * @todo refactor to use Str helper class from Illuminate\Support (no need to reinvent the wheel here)
     */
    private function generateRandomFilename($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Make sure PDF content is generated
     * @throws \Exception
     */
    private function verifyPdfIsGenerated()
    {
        if (!isset($this->pdfContent) || $this->pdfContent == '') {
            throw new \Exception("No PDF has been generated. Unable to output to the browser.");
        }
    }

    /**
     * make sure incoming options are sane. Don't want to pass arbitrary text to commandline. Set defaults
     * if bad or absent values exist
     */
    private function validatePdfOptionsAndSetIfAbsent()
    {
        if (!isset($this->paperSize) || !array_key_exists($this->paperSize, $this->paperSizeOptions)) {
            $this->paperSize = $this->defaultPaperSize;
        }
        if (!isset($this->pageOrientation) || !array_key_exists($this->pageOrientation, $this->pageOrientationOptions)) {
            $this->pageOrientation = $this->defaultPageOrientation;
        }
    }

    /**
     * Make sure that the filename is set, and if not, generate a random one.
     */
    private function validateFilenameAndSetIfAbsent()
    {
        if (!isset($this->filename) || $this->filename == '') {
            $this->filename = $this->generateRandomFilename();
        }
    }

    /**
     * Complete an HTML document around HTML that is simply BODY content, with optional injected
     * $headContent
     *
     * @param string $body The HTML BODY content to be wrapped
     * @param string $headContent Optional HTML HEAD content to be injected into the document
     * @return string A string representing a complete HTML document
     */
    private function wrapHtmlBodyContent($body, $headContent = '')
    {
        // Bulk of the HTML will be stored in here...
        $htmlDocument = "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http:// www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">" .
            $headContent .
            "<body style=\"background: white\">";
        $htmlDocument .= $body;
        $htmlDocument .= "</body></html>";

        return $htmlDocument;
    }
}
