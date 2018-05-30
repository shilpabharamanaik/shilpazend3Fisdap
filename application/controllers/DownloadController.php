<?php
/**
 * Class to download various files
 * @package Fisdap
 */
class DownloadController extends Fisdap_Controller_Base
{
    /**
     * This action creates a csv document based on the posted values
     * Sends HTML headers for file to download (prompts download dialog)
     */
    public function downloadAttachmentAction()
    {
        $attachmentId = $this->_getParam('attachment');
        $attachmentType = $this->_getParam('attachmentType');
        $associatedEntityId = $this->_getParam('associatedEntityId');

        // use the api client to get the attachment
        switch ($attachmentType) {
            case "shift":
                $attachmentsGateway = $this->container->make('Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway');
                break;
            default:
                // if we have an unsupported or undefined attachment type, we dunno what to do with it
                throw new \Exception("You must define a supported attachment type.");
        }

        $attachment = $attachmentsGateway->getOne($associatedEntityId, $attachmentId);

        // figure out where to redirect to
        if ($attachment->processed) {
            // redirect to original file
            header("Location: ".$attachment->originalUrl);

            // disable layout and view
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
        } else {
            $file = urldecode($attachment->tempUrl);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_URL, str_replace(' ', '%20', $file));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $file_content = curl_exec($ch);
            curl_close($ch);

            header('Content-Description: File Transfer');
            header('Content-Type: '.$attachment->mimeType);
            header('Content-Disposition: attachment; filename='.urlencode($attachment->fileName));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.$attachment->size);

            echo $file_content;
            exit;
        }
    }
}
