<?php
/**
 * This class acts as the base class for a widget.  Implements most of the heavy lifting
 * such as saving state and rendering the widget headers.
 *
 * @author astevenson
 */
abstract class MyFisdap_Widgets_Base implements MyFisdap_Widgets_iWidget
{
    protected $widgetData;

    protected $data;

    protected $registeredCallbacks = array();

    protected $view;

    /**
     * Default constructor.  Just initializes the widget by saving down the userId and
     * section name, then loading up the config data for the widget.
     *
     * @param integer $widgetId ID of the MyFisdapWidgetData to load up and use.
     */
    public function __construct($widgetId)
    {
        $this->view = Zend_Layout::getMvcInstance()->getView();

        $this->widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId);

        $this->loadData();
    }

    /**
     * This function loads up the data for the widget.
     */
    public function loadData()
    {
        // Check to see if the widget data actually has any data stored in it.  If it does not,
        // return the result of getDefaultData().
        if ($this->widgetData->data == '') {
            $this->data = $this->getDefaultData();
            // Save the default data down as soon as we get it
            $this->saveData();
        } else {
            $this->data = unserialize($this->widgetData->data);
        }
    }

    /**
     * This function saves down the data currently stored in $this->data.
     */
    public function saveData()
    {
        $widgetData = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $this->widgetId);

        $previousData = unserialize($this->widgetData->data);

        if (!is_array($previousData)) {
            $previousData = $this->getDefaultData();
        }

        // Array_merge them together so that new fields from the new data overwrite
        // old fields, while keeping any un-posted items the same.
        $this->widgetData->data = serialize(array_merge($previousData, $this->data));

        $this->widgetData->save();
    }

    /**
     * This is a simple predicate that is called when rerouting an ajax request- it
     * just checks to make sure that the callback function is stored in the allowed
     * method list ($this->registeredCallbacks).  Used to protect our code from being
     * arbitrarily executed by a clever user.
     *
     * @param string $callbackName Method to check to see if it is a registered callback or not.
     *
     * @retun Boolean true if the callback is registered, false if not.
     */
    public function callbackIsRegistered($callbackName)
    {
        if (in_array($callbackName, $this->registeredCallbacks)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method renders the widget container- specifically the title bar and all
     * available tools.
     *
     * @return String containing the HTML for the widget container.
     */
    public function renderContainer()
    {
        $widgetContents = $this->render();

        $header = $this->renderHeader();

        $html = <<<EOF
			<div id='widget_{$this->widgetData->id}_container' class='widget-container' data-widget-id='{$this->widgetData->id}'>
				<div class='widget-title-bar'>
					$header
				</div>
				<div id='widget_{$this->widgetData->id}_render' class='widget-render'>
					{$widgetContents}
				</div>
			</div>
EOF;

        return $html;
    }

    /**
     * This function renders just the header.  This function should be overridden in the child
     * class if the widget requires a custom header.
     */
    protected function renderHeader()
    {
        $minimizeLink = $this->renderMinimizeLink();

        $title = $this->renderTitle();

        $deleteLink = $this->renderRemoveLink();
        $configLink = $this->renderConfigLink();
        $buttonEffects = $this->renderButtonEffects();

        $html = "
			$minimizeLink
			$title
			$deleteLink
			$configLink
			$buttonEffects
		";

        return $html;
    }

    protected function renderTitle()
    {
        return "<span class='widget-title'>{$this->widgetData->widget->display_title}</span>";
    }

    /**
     * This function is used to render the Remove link for the widget.
     *
     * @return String containing the HTML for the remove link, if appropriate.  If no remove link
     * is necessary, an empty string is returned.
     */
    protected function renderRemoveLink()
    {
        // Removal of widgets is disabled in Phase 1.
        return '';

        if (!$this->widgetData->is_required) {
            $imgLink = "<img class='widget-remove' src='/images/icons/delete_white.png' id='delete_widget_{$this->widgetData->id}'/>";
            $imgLinkScript = "
				<script>
					$('#delete_widget_{$this->widgetData->id}').click(function(){
						deleteWidget({$this->widgetData->id});
					});
				</script>
			";
            $html = "<div class='widget-remove-container'>{$imgLink}{$imgLinkScript}</div>";

            return $html;
        } else {
            return '';
        }
    }

    /**
     * This function renders the minimize/maximize links, if available.
     *
     * @param String containing the HTML and javascript necessary for the minimize/maximize links,
     * or an empty string if one is not allowed.
     */
    protected function renderMinimizeLink()
    {
        if ($this->widgetData->widget->is_minimizable) {
            $toHide = ($this->widgetData->is_collapsed)?"maximized":"minimized";

            $linkId = "minimize_widget_{$this->widgetData->id}";

            $minimizeLink = "<span class='widget-minimize-container'>";
            $minimizeLink .= "<img id='{$linkId}_minimized' src='/images/icons/contracted_arrow_white.png' class='widget-minimize'/>";
            $minimizeLink .= "<img id='{$linkId}_maximized' src='/images/icons/expanded_arrow_white.png' class='widget-maximize'/>";
            $minimizeLink .= "</span>";

            $minimizeLinkScript = "<script>";

            if ($this->widgetData->is_collapsed) {
                // Hide the appropriate arrow link, and hide the content too.
                $minimizeLinkScript .= "
					$('#{$linkId}_maximized').hide();
					$('#widget_{$this->widgetData->id}_render').hide()
				";
            } else {
                $minimizeLinkScript .= "
					$('#{$linkId}_minimized').hide()
				";
            }

            $minimizeLinkScript .= "
				$('#{$linkId}_{$toHide}').hide();
				
				$('#{$linkId}_maximized, #{$linkId}_minimized').click(function(){
					toggleMinMax({$this->widgetData->id});
				});
				
				$('#widget_{$this->widgetData->id}_container').find('.widget-title').addClass('clickable').click(function() {
					toggleMinMax({$this->widgetData->id});
				});
			</script>";

            return $minimizeLink . $minimizeLinkScript;
        } else {
            return "";
        }
    }

    /**
     * This function wraps the config options in a modal and displays that modal on clicking
     * the config icon.
     */
    protected function renderConfigLink()
    {
        if ($this->widgetData->widget->has_configuration && ($this instanceof MyFisdap_Widgets_iConfigurable)) {
            $configId = $this->getNamespacedName('configure_widget');

            $imgLink = "<img class='widget-config' src='/images/icons/gear_white.png' id='$configId'/>";

            $formContents = "<div style='display: none'>" . $this->getConfigurationForm() . "</div>";

            $formId = $this->getConfigurationFormId();

            $script = "
				<script>
					$(function(){
						modalData_{$this->widgetData->id} = {
							modal: true,
							resizable: false,
							draggable: false,
							width: 500,
							title: '{$this->widgetData->widget->display_title} Configuration',
							buttons:{
								'Cancel' : function() {
									$(this).dialog('close');
								},
								'Save' : function() {
									containerRef = $(this);
									
									form = $('#{$formId}').first();
									
									formData = form.serializeArray();
									
									dataObj = {};
									
									for(e in formData){
										dataObj[formData[e].name] = formData[e].value;
									}
									
									saveWidgetData({$this->widgetData->id}, dataObj, function(){
										form.remove(); 
										reloadWidget({$this->widgetData->id});
										//containerRef.dialog('destroy');
									});
								}
							}
						};
						
						$('#{$configId}').click(function(){
							$('#{$formId}').attr('onSubmit', function(){ return false; });
							$('#{$formId}').dialog(modalData_{$this->widgetData->id});
						});
					});
				</script>
			";

            return $imgLink . $formContents . $script;
        } else {
            return '';
        }
    }

    /**
     * Generate jQuery for rendering the showing and hiding of widget buttons
     *
     * @return string the html containg <script></script>
     */
    protected function renderButtonEffects()
    {
        //determine which buttons we need to add hover effects
        $buttons = array();
        if (!$this->widgetData->is_required) {
            $buttons[] = 'widget-remove';
        }
        if ($this->widgetData->widget->has_configuration) {
            $buttons[] = 'widget-config';
        }

        if (count($buttons) > 0) {
            $hoverScript = "
				<script>
					$('#widget_{$this->widgetData->id}_container').hover(
						function(){
			";
            foreach ($buttons as $buttonClass) {
                $hoverScript .= "$(this).find('.$buttonClass').stop().animate({opacity:1}, 500);";
            }
            $hoverScript .= "
						},
						function(){
			";
            foreach ($buttons as $buttonClass) {
                $hoverScript .= "$(this).find('.$buttonClass').stop().animate({opacity:0}, 500);";
            }
            $hoverScript .= "
						}
					);
				</script>
			";

            return $hoverScript;
        }
        return "";
    }

    protected function getWidgetUser()
    {
        return $this->widgetData->user;
    }

    protected function getWidgetProgram()
    {
        return $this->widgetData->program;
    }

    /**
     * This function is just a helper to be used to uniquify HTML ids- it takes in a base and
     * appends a '-{widgetId}' to it.  This makes it so that multiples of the same type of widget
     * can coexist on a single page and not collide with one another.
     *
     * @param String $base Base ID to use.
     *
     * @return String containing the base name with the unique widget instance ID appended to the end.
     */
    protected function getNamespacedName($base)
    {
        return $base . '-' . $this->widgetData->id;
    }

    /**
     * This method should be overridden if a widget should only appear to specific roles
     * or account types.
     *
     * @return boolean
     */
    public static function userCanUseWidget($widgetId)
    {
        return true;
    }

    public function getDefaultData()
    {
        return array();
    }
}
