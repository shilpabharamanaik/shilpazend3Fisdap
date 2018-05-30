<?php
/**
 * Class to do stuff with attachments
 * @package Fisdap
 */
class AttachmentsController extends Fisdap_Controller_Base
{
    public function init()
    {
        parent::init();
    }

    /**
     * This action creates or saves an attachment based on the posted values
     */
    public function saveAttachmentAction()
    {
        $request = $this->getRequest();

        // make sure we got here via a posted form, otherwise you're in the wrong neighborhood, buddy
        if ($request->isPost()) {
            $formValues = $request->getPost();
            $attachmentType = $formValues['attachmentType'];
            $attachmentId = $formValues['attachmentId'];

            // figure out what form/info we need based on the attachment type
            if ($attachmentType == "shift") {
                $shiftAttachmentsGateway = $this->container->make('Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway');

                // get the info we need about the shift and the attachments count
                $shiftId = $formValues['shiftId'];
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

                $form = new \SkillsTracker_Form_ShiftAttachmentForm($shiftAttachmentsGateway, $shift, $attachmentId);
            }

            // did we get a file if we expected one?
            $file = null;
            $errorData = array("mode" => "error", "errors" => array());
            if ($form->upload) {
                $receivedFile = $form->upload->receive();
                if ($formValues['fileName'] == '' && !$receivedFile) {
                    // there should have been a file since we're uploading a brand new attachment
                    $errorData['errors']['uploadButton'][] = "Please choose a file to upload.";
                    $this->_helper->json($errorData);
                } else {
                    if ($receivedFile) {
                        // new file has been uploaded to replace the prior one in this entity
                        $file = $form->upload;
                    } else {
                        $file = null;
                    }
                }
            }

            $this->_helper->json($form->process($formValues, $file));
        } else {
            // go home, you are drunk
            $this->_redirect("/");
        }
    }

    /**
     * This action deletes an attachment
     */
    public function deleteAttachmentsAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $formValues = $request->getPost();
            $attachmentIds = $formValues['attachmentIds'];
            $gateway = $formValues['gateway'];
            $associatedEntityId = $formValues['associatedEntityId'];
            $attachmentsGateway = $this->container->make($gateway);

            $this->_helper->json($attachmentsGateway->delete($associatedEntityId, $attachmentIds));
        } else {
            // go home, you are drunk
            $this->_redirect("/");
        }
    }
}
