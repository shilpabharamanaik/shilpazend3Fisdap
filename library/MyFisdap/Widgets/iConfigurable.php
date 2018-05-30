<?php

/**
 * This interface defines the methods that need to be implemented for a widget that
 * has a configuration screen.
 *
 * @author astevenson
 *
 */
interface MyFisdap_Widgets_iConfigurable
{
    /**
     * This is called to fetch the contents for the form that will be displayed in a modal
     * when the user clicks the configuration link on the widget.
     *
     * You should NOT include a submit button, as one will be added in the modal.  The HTML
     * should be a fully formed HTML form element.
     *
     * @return String containing the HTML for the form.
     */
    public function getConfigurationForm();
    
    /**
     * This function is used to pull out the ID of the form element returned in getConfigurationForm.
     * This form should be named using the getNamespacedName() method to avoid conflicts.
     *
     * @return String containing the HTML form ID.
     */
    public function getConfigurationFormId();
}
