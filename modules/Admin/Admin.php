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
		$event->end = true;
		switch ($event->overURL) {
			case '/uninstallModule':
				
				$name = Connect::getResource('name', Connect::A09_STR, Connect::GET);
				var_dump($name);//core.Module.UserData
				$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
				core\ModuleManager::uninstall($name);
				header('Location: /admin');
				break;
			case '/installModule':
				$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
				core\ModuleManager::install($name);
				echo $name;
				header('Location: /admin');
				break;
			case '/reinstallModule':
				$name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
				core\ModuleManager::uninstall($name);
				core\ModuleManager::install($name);
				header('Location: /admin');
				echo $name;
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
