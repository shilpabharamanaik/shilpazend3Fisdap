<?php namespace Fisdap\Data\MyFisdap;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineMyFisdapWidgetDataRepository
 *
 * @package Fisdap\Data\MyFisdap
 */
class DoctrineMyFisdapWidgetDataRepository extends DoctrineRepository implements MyFisdapWidgetDataRepository
{
    public function getWidgetsForSection($userId, $section, $widgetId = null)
    {
        $user = \Fisdap\EntityUtils::getEntity('User', $userId);
        $programId = $user->getProgramId();
        
        $qb = $this->_em->createQueryBuilder();
        
        // Not worrying about hidden widgets here- they
        // simply won't be displayed in the render.
        // If they get included, it causes issues in the case
        // that a user hid all widgets- we want to just not
        // show anything instead of reloading the defaults again.
        $query = "
			SELECT 
				data, def 
			FROM 
				\Fisdap\Entity\MyFisdapWidgetData data 
				INNER JOIN data.widget def 
			WHERE 
				data.section = ?1
				AND data.user = ?2
				AND data.program = ?3
		";
        
        if ($widgetId != null) {
            $query .= " AND data.widget = ?4 ";
        }
        
        $query .= "
			ORDER BY
				data.column_position ASC
		";
        
        $queryObj = $this->_em->createQuery($query);
        $queryObj->setParameter(1, $section);
        $queryObj->setParameter(2, $userId);
        $queryObj->setParameter(3, $programId);
        
        if ($widgetId != null) {
            $queryObj->setParameter(4, $widgetId);
        }
        
        $widgets = $queryObj->getResult();
        
        $existingWidgets = array();
        
        // Restructure the widgets a bit so that they can be more easily accessed...
        foreach ($widgets as $widget) {
            $existingWidgets[$widget->widget->id] = $widget;
        }
        
        // Here are the default widgets.
        $defaultWidgets = \Fisdap\EntityUtils::getRepository('MyFisdapSectionDefaults')->findBy(array('section' => $section), array('column_position' => 'asc'));
        
        usort($defaultWidgets, array('self', 'sortWidgetsByColumnPosition'));
        
        $currentWidgets = array();
        
        // Loop over the existing widgets to determine which of them should be shown- defer to the defaults,
        // since there is no current way for a user to modify what widgets are available for them.
        foreach ($defaultWidgets as $defaultWidget) {
            if (array_key_exists($defaultWidget->widget->id, $existingWidgets)) {
                $currentWidgets[] = $existingWidgets[$defaultWidget->widget->id];
            } else {
                $currentWidgets[] = $this->createNewWidgetData($userId, $section, $defaultWidget);
            }
        }


        
        return $currentWidgets;
    }
    
    public function createNewWidgetData($userId, $section, $defaultWidget)
    {
        $newWidget = new \Fisdap\Entity\MyFisdapWidgetData();
        
        $newWidget->is_hidden = false;
        $newWidget->is_collapsed = false;
        
        $newWidget->is_required = $defaultWidget->is_required;
        
        $newWidget->column_position = $defaultWidget->column_position;
        
        $newWidget->user = \Fisdap\EntityUtils::getEntity('User', $userId);
        
        $newWidget->program = $newWidget->user->getCurrentProgram();
        
        $newWidget->widget = $defaultWidget->widget;
        
        $newWidget->section = $section;
        
        if ($defaultWidget->widget->default_data != '') {
            $newWidget->data = unserialize($defaultWidget->widget->default_data);
        }
        
        $newWidget->save();
        
        return $newWidget;
    }
    
    /**
     * Helper function to sort the widgets by column position.
     * @param \Fisdap\Entity\MyFisdapSectionDefaults $a first widget to compare
     * @param \Fisdap\Entity\MyFisdapSectionDefaults $b second widget to compare
     *
     * @return 0 if equal, -1 if a < b, or 1 if a > b.
     */
    public function sortWidgetsByColumnPosition($a, $b)
    {
        if ($a->column_position == $b->column_position) {
            return 0;
        }
    
        return ($a->column_position < $b->column_position)?-1:1;
    }
    
    public function getAvailableWidgetsForSection($width)
    {
        $qb = $this->_em->createQueryBuilder();
    
        // Not worrying about hidden widgets here- they
        // simply won't be displayed in the render.
        // If they get included, it causes issues in the case
        // that a user hid all widgets- we want to just not
        // show anything instead of reloading the defaults again.
        $query = "
			SELECT
				def
			FROM
				\Fisdap\Entity\MyFisdapWidgetDefinition def
			ORDER BY
				def.display_title
		";
        
        //WHERE
        //def.minimum_container_width <= ?1
        
        $queryObj = $this->_em->createQuery($query);
        //$queryObj->setParameter(1, $width);
        
        $results = $queryObj->getResult();
        
        // Filter out any widgets that can't be displayed by this student...
        $filteredResults = array();
        
        foreach ($results as $widget) {
            $className = $widget->class_name;
            
            $classPath = realpath(APPLICATION_PATH . '/../library/') . '/' . implode('/', explode('_', $className));

            if (file_exists($classPath . ".php")) {
                if ($className::userCanUseWidget($widget->id)) {
                    $filteredResults[] = $widget;
                }
            }
        }
        return $filteredResults;
    }
    
    public function widgetAlreadyExistsInSection($userId, $section, $widgetDefId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $query = "
			SELECT
				data, def
			FROM
				\Fisdap\Entity\MyFisdapWidgetData data
				INNER JOIN data.widget def
			WHERE
				data.section = ?1
				AND data.user = ?2
				AND data.widget = ?3
				AND data.is_hidden = 0
		";
        
        $queryObj = $this->_em->createQuery($query);
        $queryObj->setParameter(1, $section);
        $queryObj->setParameter(2, $userId);
        $queryObj->setParameter(3, $widgetDefId);
        
        $widgets = $queryObj->getResult();
        
        if (count($widgets)>0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a multidimensional array of rendered HTML for widget sections for a given user
     * @param  array $sections The sections to render as the user
     * @param  int $userId   The user id to render the section as
     * @return array           Multidimensional array of widget html for the given user
     */
    public function getActiveWidgetsAsUser($sections, $userId, $options=array())
    {
        // The array of section ids that we use for building our array
        $widgets = array();
        $options = json_encode($options, JSON_FORCE_OBJECT);
        
        // Loop through each of the sections we've identified to include
        foreach ($sections as $section) {
            $widgets[$section] = array();
            
            // Get the widget data the given section
            $widgetsData = $this->getWidgetsForSection($userId, $section);
            
            // Loop through each widget in the section
            foreach ($widgetsData as $widget) {

                // Only add active widgets to section
                if (!$widget->is_hidden) {
                    $widgets[$section][$widget->id] = $widget->renderWidget($options);
                }
            }
        }
        return $widgets;
    }
}
