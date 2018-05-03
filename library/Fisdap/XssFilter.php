<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

namespace Fisdap;

/**
 * XSS filter utilities taken from Drupal project
 *
 * @author jmortenson
 */
class XssFilter
{
    protected $inputString = '';
    protected $allowedTags = array();
    
    public function __construct($string = '', $allowedTags = array('a', 'em', 'strong', 'cite', 'blockquote', 'code', 'ul', 'ol', 'li', 'dl', 'dt', 'dd')) {
        $this->inputString = $string;
        $this->allowedTags = $allowedTags;
    }
    
    public function setInputString($string) {
        $this->inputString = $string;
    }
    
    public function setAllowedTags($allowedTags = array()) {
        if (is_array($allowedTags)) {
            $this->allowedTags = $allowedTags;
        }
    }
    
    /**
     * Filter an incoming string against a list of allowed HTML tags.
     * See http://api.drupal.org/api/drupal/includes!common.inc/function/filter_xss/7
     *
     * @param string $string The text to filter
     * @param array $allowed_tags Array of tags (no greater than/less than characters) that are OK to leave in the string
     *
     * @return string The filtered string
     */
    public function filter() {
        $string = $this->inputString; // translation to Drupal function
        $allowed_tags = $this->allowedTags; // translation to Drupal function
        
        // Only operate on valid UTF-8 strings. This is necessary to prevent cross
        // site scripting issues on Internet Explorer 6.
        if (!$this->drupal_validate_utf8()) {
          return '';
        }
        // Store the text format.
        $this->_filter_xss_split($allowed_tags, TRUE);
        // Remove NULL characters (ignored by some browsers).
        $string = str_replace(chr(0), '', $string);
        // Remove Netscape 4 JS entities.
        $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
        
        // Defuse all HTML entities.
        $string = str_replace('&', '&amp;', $string);
        // Change back only well-formed entities in our whitelist:
        // Decimal numeric entities.
        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        // Hexadecimal numeric entities.
        $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
        // Named entities.
        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
        
        return preg_replace_callback('%
            (
            <(?=[^a-zA-Z!/])  # a lone <
            |                 # or
            <!--.*?-->        # a comment
            |                 # or
            <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
            |                 # or
            >                 # just a >
            )%x', array($this, '_filter_xss_split'), $string);
    }
    
    // see http://api.drupal.org/api/drupal/includes!common.inc/function/_filter_xss_split/7
    public function _filter_xss_split($m, $store = FALSE) {
        static $allowed_html;
        
        if ($store) {
            $allowed_html = array_flip($m);
            return;
        }
        
        $string = $m[1];
        
        if (substr($string, 0, 1) != '<') {
            // We matched a lone ">" character.
            return '&gt;';
        }
        elseif (strlen($string) == 1) {
            // We matched a lone "<" character.
            return '&lt;';
        }
        
        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
            // Seriously malformed.
            return '';
        }
        
        $slash = trim($matches[1]);
        $elem = &$matches[2];
        $attrlist = &$matches[3];
        $comment = &$matches[4];
        
        if ($comment) {
            $elem = '!--';
        }
        
        if (!isset($allowed_html[strtolower($elem)])) {
            // Disallowed HTML element.
            return '';
        }
        
        if ($comment) {
            return $comment;
        }
        
        if ($slash != '') {
            return "</$elem>";
        }
        
        // Is there a closing XHTML slash at the end of the attributes?
        $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
        $xhtml_slash = $count ? ' /' : '';
        
        // Clean up attributes.
        $attr2 = implode(' ', _filter_xss_attributes($attrlist));
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';
        
        return "<$elem$attr2$xhtml_slash>";
    }

    // see http://api.drupal.org/api/drupal/includes!bootstrap.inc/function/drupal_validate_utf8/7
    private function drupal_validate_utf8() {
        if (strlen($this->inputString) == 0) {
          return TRUE;
        }
        // With the PCRE_UTF8 modifier 'u', preg_match() fails silently on strings
        // containing invalid UTF-8 byte sequences. It does not reject character
        // codes above U+10FFFF (represented by 4 or more octets), though.
        return (preg_match('/^./us', $this->inputString) == 1);
    }
    
    // see http://api.drupal.org/api/drupal/includes!bootstrap.inc/function/check_plain/7
    static function checkPlain($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}