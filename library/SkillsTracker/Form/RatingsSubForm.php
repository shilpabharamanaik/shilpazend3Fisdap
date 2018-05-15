<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
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
 * Ratings SubForm
 */

/**
 * @package    SkillsTracker
 * @subpackage SubForms
 */
class SkillsTracker_Form_RatingsSubForm extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\Signoff
     */
    public $signoff;

    /**
     * @var array types of preceptor ratings raters
     */
    public $raterTypes;

    /**
     * @var array types of preceptor ratings
     */
    public $types;

    /**
     * @var boolean Should this subform be editable
     */
    public $readOnly;

    /**
     * @param null $signoffId
     * @param bool $readOnly
     * @param null $options
     */
    public function __construct($signoffId = null, $readOnly = true, $options = null)
    {
        $this->signoff = \Fisdap\EntityUtils::getEntity('PreceptorSignoff', $signoffId);
        $this->readOnly = $readOnly;

        $this->raterTypes = \Fisdap\Entity\PreceptorRatingRaterType::getFormOptions(false, false);
        $this->types = \Fisdap\Entity\PreceptorRatingType::getFormOptions(false, false);

        parent::__construct($options);
    }

    public function init()
    {
        foreach ($this->raterTypes as $raterId => $raterName) {
            foreach ($this->types as $typeId => $typeName) {
                $ratingElemName = $raterName . "_" . $typeId . "_" . $this->signoff->id;
                $element = new SkillsTracker_Form_Element_Rating($ratingElemName);
                $element->setLabel($typeName);
                $element->setDecorators(array('ViewHelper'));

                if ($this->readOnly) {
                    $element->setAttrib("disabled", "disabled");
                }

                $this->addElement($element);
            }
        }

        $this->setDecorators(array(
                array('ViewScript', array('viewScript' => "forms/ratingsSubForm.phtml")),
            ));

        //Set the defaults for this form
        if ($this->signoff->id) {
            $defaults = [];
            foreach ($this->signoff->ratings as $rating) {
                $elementName = $rating->rater_type->name . "_" . $rating->type->id . "_" . $this->signoff->id;
                $defaults[$elementName] = $rating->value;
            }

            $this->setDefaults($defaults);
        }
    }
}
