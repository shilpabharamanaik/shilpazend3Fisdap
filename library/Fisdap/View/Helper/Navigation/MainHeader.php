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

/**
 * This is a view helper for the main site header.  Effectively renders itself,
 * and includes the required CSS/JS files.
 */
class Fisdap_View_Helper_Navigation_MainHeader extends Zend_View_Helper_Abstract
{
    /**
     * Default entry point for this class.  Returns the HTML to be used for the
     * primary navigation panel.  This panel shouldn't really vary too much
     * overall between pages, but will need to take into account active tabs.
     *
     * @return string Fisdap_View_Helper_Pattern_PersonFinder instance.
     */
    public function mainHeader()
    {
        // Load up the navigation config item.  This might make more sense in
        // the bootstrapper, but for now keep it here in case different
        // sections need different navigation menus.
        $config = $this->getXmlConfig();

        $container = new Zend_Navigation($config);

        $this->view->navigation($container);
        $this->view->navigation()->menu()->setUlClass('main_navigation');

        $user = \Fisdap\Entity\User::getLoggedInUser();
        $currentContext = $user->getCurrentUserContext();

        if ($user !== null) {
            $this->selectActiveNodes();
        }

        if ($currentContext->id) {
            $profession = $currentContext->getProgram()->profession->name;
        } else {
            $profession = "EMS";
        }

        if (RELEASE_STAGE != 'Prod') {
            $releaseStageHtml = ' <span style="font-weight: bold; color: white; background-color: red;">&nbsp;' . strtoupper(RELEASE_STAGE) . '&nbsp;</span>';
        } else {
            $releaseStageHtml = null;
        }

        $html = "
			<div id='main_header' class='main_header'>
				<div id='header_nav' class='header_nav'>
					<div id='header_upper_nav'>
						<a href='https://www.fisdap.net'><div id='site-title'>FISDAP</div></a>
						<div id='user_info' class='user_info'>" . $this->view->profileLink() . "</div>
						<div class='clear'></div>
						<div id='subtitle'> Online Tools for $profession Education${releaseStageHtml}</div><div class='clear'></div></div>";

        $html .= $this->view->navigation()->menu()->setMaxDepth(1)->render() . "
				</div>
				<div id='header_subnav' class='header_subnav'></div>
				<div style='clear: both;'></div>
			</div>
		";

        if ($user->isStaff()) {
            $html .= $this->view->partial("staffLinks.phtml", array("legacyUrl" => Util_HandyServerUtils::get_fisdap_members1_url_root()));
        }

        return $html;
    }

    /**
     * This function sets the active flag on the current tab the request is for.
     *
     * @return    Array containing two elements- the active tab (under the index
     *            'active_tab'), and the active page (under the index
     *            'active_page').  Both of these elements are instances of
     *            Zend_Navigation_Page.
     */
    private function selectActiveNodes()
    {
        // Determine which element is active based on the current request
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $params = $request->getParams();

        // Set the main tab...
        //Hack to set the active tab for scheduler pages
        if (!($params['module'] == "scheduler" && $params['controller'] == "index" && $params['action'] == "index") && $params['module'] == "scheduler") {
            $params['module'] = "skills-tracker";
        }


        $activeTab = $this->view->navigation()->findBy('module', $params['module']);
        if ($activeTab) {
            $activeTab->active = true;
        } else {
            // Just pick the first one if no tab is active...
            $kids = $this->view->navigation()->getPages();
            $firstKid = array_shift($kids);
            $firstKid->active = true;
        }

        // Set the sub-nav link...
        $fullURI = "/" . $params['module'] . '/' . $params['controller'] . '/' . $params['action'];
        $activePage = $this->view->navigation()->findBy('uri', $fullURI);
        if ($activePage) {
            $activePage->active = true;
        }
    }

    /**
     * This function returns a Zend_Config_Xml representing the available
     * navigation bits, depending on the logged in users access.
     *
     * @return Zend_Config_Xml containing the navigation parts.
     */
    private function getXmlConfig()
    {
        // Get the logged in person's current context...
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        $currentContext = $loggedInUser->getCurrentUserContext();

        $configFileContents = file_get_contents(APPLICATION_PATH . '/configs/navigation.xml');

        // If the user has a serial number tied to it, use its configuration
        // field to alter the navigation.  Otherwise,
        if (!$currentContext) {
            // Redirect them back to the login page- they aren't logged in and
            // we hate them and want them Not To Be Happy anymore.
            return null;
        } else {
            // First, figure out which tabs should be included.
            $includedTabs = $currentContext->getIncludedTabs();

            $filteredXML = $this->filterTabs($configFileContents, $includedTabs);
            return new Zend_Config_Xml($filteredXML, 'nav');
        }
    }

    /**
     * This function returns an XML document that only contains the allowed
     * navigation parts.
     *
     * @param String $xml the XML navigation config file contents
     * @param array $includedTabs The listing of included tabs and sub-nav links,
     * generated based on the logged in users serial number.
     *
     * @return String containing the filtered XML.
     */
    private function filterTabs($xml, $includedTabs)
    {
        $originalDom = new DOMDocument();

        $originalDom->loadXML($xml);

        $navBlock = $originalDom->getElementsByTagName('nav')->item(0);

        $xpath = new DOMXPath($originalDom);

        // Now, start moving stuff from the original dom into the filtered one...
        $mainTabs = $xpath->query("//configdata/nav/*");
        for ($i = 0; $i < $mainTabs->length; $i++) {
            $tabName = $mainTabs->item($i)->nodeName;
            $mainTabTag = $xpath->query("//configdata/nav/" . $tabName)->item(0);

            if (array_key_exists($mainTabs->item($i)->nodeName, $includedTabs)) {
                $value = $includedTabs[$tabName];

                // Remove the ones that the user shouldn't see...
                if (is_array($value)) {
                    $pagesParentTag = $xpath->query("//configdata/nav/" . $tabName . "/pages")->item(0);

                    $pages = $xpath->query("//configdata/nav/" . $tabName . "/pages/*");

                    foreach ($pages as $page) {
                        if (!in_array($page->nodeName, $value)) {
                            $pagesParentTag->removeChild($page);
                        }
                    }

                    // Do a check to see if any of the tabs only have one sub-nav
                    // link left.  Any that do need to be removed, and have the main
                    // tab uri changed to the sub-nav one.
                    $afterPages = $xpath->query("//configdata/nav/" . $tabName . "/pages/*");

                    if ($afterPages->length == 1) {
                        $pageName = $afterPages->item(0)->tagName;

                        $this->swapNodeValues($xpath, $tabName, $pageName, "module");
                        $this->swapNodeValues($xpath, $tabName, $pageName, "controller");
                        $this->swapNodeValues($xpath, $tabName, $pageName, "action");
                        $this->swapNodeValues($xpath, $tabName, $pageName, "uri");

                        $mainTabTag->removeChild($pagesParentTag);
                    }
                }

                $navBlock->appendChild($mainTabTag);
            } else {
                $navBlock->removeChild($mainTabTag);
            }
        }

        return $originalDom->saveXML();
    }

    /**
     * This is just a helper that swaps out values from one node to another.
     *
     * @param DOMXPath $xpath object containing the original DOM.
     * @param $tabName
     * @param $pageName
     * @param string $nodeName Name of the property to copy over.
     */
    private function swapNodeValues($xpath, $tabName, $pageName, $nodeName)
    {
        $sourceNode = $xpath->query("//configdata/nav/$tabName/pages/$pageName/$nodeName")->item(0);
        $targetNode = $xpath->query("//configdata/nav/$tabName/$nodeName")->item(0);

        $targetNode->nodeValue = $sourceNode->nodeValue;
    }
}
