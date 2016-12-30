<?php
class UI extends core\Module {
	public function install() {
		core\PathManager::addEventListener('ui', 'UI', 'mainDispatcher', false);
	}
	public function uninstall() {
		
	}
	public function mainDispatcher($event) {
		$event->end = true;
		include 'tpl/mainpage.tpl.php';
	}
	
}
