<?php

interface MyFisdap_Widgets_iWidget
{
	public function render();
	
	public function loadData();
	
	public function saveData();
	
	public function getDefaultData();
}
