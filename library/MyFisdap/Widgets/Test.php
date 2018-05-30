<?php

class MyFisdap_Widgets_Test extends MyFisdap_Widgets_Base
{
    protected $registeredCallbacks = array('ajaxRerouteTest');
    
    public function render()
    {
        $html = <<<EOF
			<input type='text' id='{$this->getNamespacedName('test-dummy')}' value='{$this->data['value']}' />
			<input type='button' id='{$this->getNamespacedName('test-save')}' value='Update string' />
			<input type='button' id='{$this->getNamespacedName('test-ajax')}' value='Set string to "something"' />
			
			<script>
				$('#{$this->getNamespacedName('test-save')}').click(function(){
					data = {
						value: $('#{$this->getNamespacedName('test-dummy')}').val()
					};
					
					saveWidgetData({$this->widgetData->id}, data);
				});
				
				$('#{$this->getNamespacedName('test-ajax')}').click(function(){
					data = {
						newValue: 'something'
					};
					
					callback = function(returnData){
						if(returnData){
							$('#{$this->getNamespacedName('test-dummy')}').val('something');
						}
					}
					
					routeAjaxRequest({$this->widgetData->id}, 'ajaxRerouteTest', data, callback);
				});
			</script>
EOF;
        return $html;
    }
    
    public function getDefaultData()
    {
        return array('value' => 'something');
    }
    
    public function ajaxRerouteTest($data)
    {
        $this->data['value'] = $data['newValue'];
        
        $this->saveData();
        
        return true;
    }
}
