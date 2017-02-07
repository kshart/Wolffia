<?php
use core\Connect;
class Admin extends core\Module {
	public function __construct() {
		$this->name = 'standart/Admin';
		$this->description = 'Модуль для администрирования сайта.';
		$this->version = '0.1a';
	}
	public function install() {
		
		core\PathManager::addEventListener('admin', 'Admin', 'mainDispatcher', false);
	}
	public function uninstall() {
		
	}
	public function mainDispatcher($event) {
		if (!core\ModuleManager::getModule('Users')->userCheckPermission(['PM_ALL'])) return;
		$event->end = true;
		switch ($event->overURL) {
			case '/module':
				$moduleName = file_get_contents('php://input', '', NULL, 0, 128);
				$module = core\ModuleManager::getModule($moduleName);
				if (isset($module) && isset($module->adminPanel)) {
					include($_SERVER['DOCUMENT_ROOT']."/modules/".$moduleName.'/'.$module->adminPanel);
				}
				return;
			case '/uninstallModule':
				
				$name = Connect::getResource('name', Connect::A09_STR, Connect::GET);
				core\ModuleManager::uninstall($name);
				header('Location: /admin');
				break;
			case '/installModule':
				$name = Connect::getResource('name', Connect::A09_STR, Connect::GET);
				core\ModuleManager::install($name);
				header('Location: /admin');
				break;
			case '/reinstallModule':
				$name = Connect::getResource('name', Connect::A09_STR, Connect::GET);
				core\ModuleManager::uninstall($name);
				core\ModuleManager::install($name);
				header('Location: /admin');
				break;
			case '/registratePage':
				$url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_STRING);
				$file = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING);
				$equal = filter_input(INPUT_GET, 'equal', FILTER_SANITIZE_STRING)==='on'?true:false;
				if (!file_exists($_SERVER['DOCUMENT_ROOT'].$file)) break;
				core\PathManager::addEventListener($url, 'Admin', 'pageDispatcher', $equal, $file);
				header('Location: /admin');
				break;
			case '/unregistratePage':
				$url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_STRING);
				$equal = filter_input(INPUT_GET, 'equal', FILTER_SANITIZE_STRING)==='1'?true:false;
				core\PathManager::removeEventListener($url, 'Admin', 'pageDispatcher', $equal);
				header('Location: /admin');
				break;
		}
		include 'tpl/admin.tpl.php';
	}
	public function pageDispatcher($event) {
		if (!file_exists($_SERVER['DOCUMENT_ROOT'].$event->options)) return;
		$event->end = true;
		include $_SERVER['DOCUMENT_ROOT'].$event->options;
	}
}
