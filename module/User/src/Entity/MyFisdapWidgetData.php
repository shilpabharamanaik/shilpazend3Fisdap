<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * MyFisdapWidgetData
 *
 * @Entity(repositoryClass="Fisdap\Data\MyFisdap\DoctrineMyFisdapWidgetDataRepository")
 * @Table(name="fisdap2_my_fisdap_widget_data")
 * @HasLifecycleCallbacks
 */
class MyFisdapWidgetData extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="MyFisdapWidgetDefinition")
     */
    protected $widget;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_hidden = false;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_collapsed = false;
    
    /**
     * @Column(type="string")
     */
    protected $section;
    
    /**
     * @Column(type="integer")
     */
    protected $column_position;
    
    /**
     * @Column(type="text", nullable=true)
     */
    protected $data;
    
    /**
     * @Column(type="boolean")
     */
    protected $is_required = false;
    
    public function getWidgetClassInstance()
    {
        return new $this->widget->class_name($this->id);
    }
    
    /**
     * Renders this widget's content, via the widget class instance's renderContainer() method
     * or displays a warning message if that widget class is no longer found.
     *
     * @throws \Exception when the widget class cannot be loaded
     * @return string HTML representation of the widget
     */
    public function renderWidget()
    {
        $className = $this->widget->class_name;
        $args = func_get_args();

        // Hackish, but we're handling this below, so for not that'll have to do
        // For more info, see: https://bugs.php.net/bug.php?id=52339
        try {
            class_exists($className);

            if ($className::userCanUseWidget($this->id)) {
                $widget = $this->getWidgetClassInstance();
            }
        } catch (\Exception $e) {
            return "<div id='widget_{$this->id}_container' style='border: 1px solid #FF5555; background-color: #FFCCCC; padding: 7px;'>
			Sorry, it appears that the " . $this->widget->display_title . " widget is not
			supported.  Please contact customer support to delete this widget.</div>";
        }

        //If the widget variable is an actual widget, return the rendered widget, otherwise, nothing
        if ($widget instanceof \MyFisdap_Widgets_Base) {
            return $widget->renderContainer($args[0]);
        } else {
            return "";
        }
    }
}
