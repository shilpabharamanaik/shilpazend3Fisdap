<?php

/**
 * ContextSwitcher helper
 *
 * Call as $this->contextSwitcher() in your layout script
 */
class Zend_View_Helper_ContextSwitcher extends Zend_View_Helper_Abstract
{

    public function contextSwitcher($currentContext)
    {
        $user = $currentContext->getUser();

        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/context-switcher.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/context-switcher.css");

        $html = "<div id='context-switcher'>" . $currentContext->getDescription();

        // if this user has multiple contexts, add the dropdown
        if (count($user->userContexts) > 1) {
            $html .= " <img class='context-switcher-trigger' src='/images/icons/arrow_down.svg'>";

            // menu
            $html .= "<div class='context-switcher-menu'>";
            $html .= "<img class='ticker' src='/images/ticker.png'>";
            $html .= "<ul>";
            foreach ($user->userContexts as $context) {
                // only show non-current contexts
                if ($context->getId() != $currentContext->getId()) {
                    $html .= "<li>";
                    $html .= "<h5 class='section-header no-border'>".$context->getDescription()."</h5>";
                    $html .= "<a class='switch-context' data-contextId='".$context->getId()."'>switch to this account</a>";
                    $html .= "</li>";
                }
            }
            $html .= "</ul></div>";
        }

        $html .= "</div>";

        return $html;
    }

}
