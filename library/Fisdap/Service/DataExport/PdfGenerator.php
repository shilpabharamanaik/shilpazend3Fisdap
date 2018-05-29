<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 12/26/14
 * Time: 4:11 PM
 */


namespace Fisdap\Service\DataExport;

/**
 * Provides PDF Generation. Set options, generate the PDF, then either retrieve the PDF content or
 * output it to the browser.
 *
 * Interface PdfGenerator
 * @package Fisdap\Service
 */
interface PdfGenerator {

    /**
     * Generate PDF content from a string of HTML content
     *
     * @param string $htmlContent A string containing HTML content that is to be converted to a PDF
     * @param boolean $isCompleteHtmlDocument Is $htmlContent a complete HTML document, or just BODY content?
     * @param string $separateHeadContent A string containing HTML suitable for being put in the HEAD tag. Useful when $isCompleteHtmlDocument = FALSE
     */
    public function generatePdfFromHtmlString($htmlContent, $isCompleteHtmlDocument = TRUE, $separateHeadContent = '');

    /**
     * Generate PDF content from one or more HTML files on the system
     *
     * @param array $fileList An array of paths to files on the system
     */
    public function generatePdfFromHtmlFiles(array $fileList);

    /**
     * Return a string representing the PDF content, once it has been generated
     *
     * @return string The content of a PDF
     */
    public function getPdfContent();

    /**
     * Output the PDF as a file to the client browser. Sends appropriate headers.
     */
    public function outputPdfToBrowser();

    /**
     * Set the desired filename for the PDF to be generated.
     *
     * @param string $pdfFilename Desired filename of the PDF
     */
    public function setFilename($pdfFilename);

    /**
     * Set the desired paper size for the PDF to be generated.
     *
     * @param string $paperSize Set the paper size to be used for generation: LETTER, LEGAL, A4, etc.
     */
    public function setPaperSize($paperSize);

    /**
     * Set the desired orientation for the PDF to be generated
     *
     * @param string $orientation Set the orientation to bused for generation: landscape or portrait
     */
    public function setOrientation($orientation);
}