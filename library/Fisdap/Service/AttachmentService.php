<?php
/**
 * Created by PhpStorm.
 * User: khanson
 * Date: 4/22/15
 * Time: 2:05 PM
 */

namespace Fisdap\Service;

/**
 * Provides transformation and evaluation methods for working with attachments
 *
 * @package Fisdap\Service
 */
class AttachmentService {
    /**
     * Gets the preview image source and class for a given attachment
     *
     * @param $attachment
     * @return array
     */
    public function getPreview($attachment) {
        $iconClasses = "stock-icon";
        switch ($attachment->mimeType) {

            // image files get the generated thumbnail if there is one, otherwise they get the temp url
            case "image/jpeg":
            case "image/jpg":
            case 'image/png':
            case 'image/gif':
            case 'image/svg':
                $src = ($attachment->variationUrls->thumbnail) ? $attachment->variationUrls->thumbnail : urldecode($attachment->tempUrl);
                $class = "preview";
                $type = "image";
                break;

            // excel spreadsheets
            case "application/vnd.ms-excel":
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                $src = "/images/icons/excel.svg";
                $class = $iconClasses;
                $type = "spreadsheet";
                break;

            // pdfs
            case "application/pdf":
                $src = "/images/icons/pdf.svg";
                $class = $iconClasses;
                $type = "pdf";
                break;

            // plain text
            case 'text/plain':
                $src = "/images/icons/text.svg";
                $class = $iconClasses;
                $type = "text";
                break;

            // word docs
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                $src = "/images/icons/word.svg";
                $class = $iconClasses;
                $type = "doc";
                break;

            // mp3s
            case 'audio/mp3':
            case 'audio/mpeg':
                $src = "/images/icons/MP3.svg";
                $class = $iconClasses;
                $type = "mp3";
                break;

            // mov
            case 'video/quicktime':
                $src = "/images/icons/MOV.svg";
                $class = $iconClasses;
                $type = "mov";
                break;

            // mp4
            case 'video/mp4':
                $src = "/images/icons/MP4.svg";
                $class = $iconClasses;
                $type = "mp4";
                break;

            // everything else gets the default
            default:
                $src = "/images/icons/default-attachment-file.svg";
                $class = $iconClasses . " default";
                $type = "other";
                break;
        }
        return array('src' => $src, "class" => $class, 'type' => $type);
    }

    public function getCheckboxRows(array $attachments, $class = null) {
        $attachment_info_display_helper = new \Fisdap_View_Helper_AttachmentInfo();
        $rows = array();

        foreach ($attachments as $attachment){
            $content = array();
            $content[] = '<img src="' . $this->getPreview($attachment)['src'] . '" class="thumbnail" />';
            $content[] = $attachment_info_display_helper->attachmentInfo($attachment, $class);

            $rows[] = array('value' => $attachment->id, 'content' => $content);
        }

        return $rows;
    }
}