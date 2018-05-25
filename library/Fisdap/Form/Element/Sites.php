<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Custom Zend_Form_Element_Select for displaying sites
 */

/**
 * @package Fisdap
 * @todo figure out how to get the user's current program_id
 */
class Fisdap_Form_Element_Sites extends Zend_Form_Element_Select
{
	/**
	 * @var mixed string | array containing the types of site to filter by
	 */
	protected $types;
	
	protected $allSites;

    protected $opt_groups;
	
	public function __construct($spec, $types = null, $allSites = null, $opt_groups = false, $options = null)
	{
		$this->types = $types;
		$this->allSites = $allSites;
        $this->opt_groups = $opt_groups;
		
		parent::__construct($spec, $options);
	}
	
	public function init()
	{
		//jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
		
		//add js file to populate the base list
        $this->_view->headScript()->appendFile("/js/library/Fisdap/Form/Element/sites.js");

		$user = \Fisdap\Entity\User::getLoggedInUser();
		$sites = \Fisdap\Entity\SiteLegacy::getSites($user->getProgramId(), $this->types);

		if (count($sites) == 0) {

			$this->setMultiOptions(array('-1' => 'No sites available.'));

		} else {

			$this->setMultiOptions(\Fisdap\Entity\SiteLegacy::getSites($user->getProgramId(), $this->types, true, $this->opt_groups));

		}
		$this->setAttrib('class', 'sites');
	}
}
