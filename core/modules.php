<?php

namespace core {
	class Module {
		public $name;
		public $description;
		public $version = '1.0';
		public $visible = true;
		//public function install();
		//public function uninstall();

	}
	class ModuleManager {
		static private $modules = [];
		static public function load() {
			$result = Database::query('SELECT * FROM modules;');
			foreach($result as &$moduleCFG) {
				try {
					if ($moduleCFG->alwaysLoad) { 
						include_once('modules/'.$moduleCFG->name.'/'.$moduleCFG->name.'.php');
						if (!\class_exists($moduleCFG->name)) throw new Exception($moduleCFG->name.' undefined class.');
						if ($moduleCFG->name instanceof Module) throw new Exception($moduleCFG->name.' class is not extends Module.');
						$moduleObject = new $moduleCFG->name();
						self::$modules[$moduleCFG->name] = $moduleObject;
						Log::info('Модуль '.$moduleCFG->name.' загружен.');	
					}else{
						self::$modules[$moduleCFG->name] = false;
					}
				}catch (\Exception $ex) {
					Log::error($ex->getFile().':'.$ex->getLine().' MODULE_LOAD_FAILED '.$ex->getMessage());
				}
			}
		}
		static public function unload() {
			foreach(self::$modules as $key=>&$module) {
				if ($module !== false) {
					Log::info('Модуль '.\get_class($module).' выгружен.');
					unset($module);
				}
			}
		}

		static public function getModule($moduleName) {
			if (isset(self::$modules[$moduleName]) && self::$modules[$moduleName] !== false && \get_class(self::$modules[$moduleName]) === $moduleName) {
				if (self::$modules[$moduleName]->visible) return self::$modules[$moduleName];///O
			}else{
				try {
					include_once('modules/'.$moduleName.'/'.$moduleName.'.php');
					if (!\class_exists($moduleName)) throw new \Exception($moduleName.' undefined class.');
					if ($moduleName instanceof Module) throw new \Exception($moduleName.' class is not extends Module.');
					$moduleObject = new $moduleName();
					self::$modules[$moduleName] = $moduleObject;
					Log::info('Модуль '.$moduleName.' загружен.');
					return $moduleObject;
				}catch (\Exception $ex) {
					Log::error($ex->getFile().':'.$ex->getLine().' MODULE_LOAD_FAILED '.$ex->getMessage());
				}
			}
			return null;
		}
		static public function install($moduleName) {
			try {
				$res = Database::query('SELECT count(id) as count FROM modules WHERE name = %s;', $moduleName);
				if ($res[0]->count > 0) throw new \Exception($moduleName.' already installed.'.$res[0]->count);
				include_once('modules/'.$moduleName.'/'.$moduleName.'.php');
				if (!\class_exists($moduleName)) throw new \Exception($moduleName.' undefined class.');
				if ($moduleName instanceof Module) throw new \Exception($moduleName.' class is not extends Module.');
				$moduleObject = new $moduleName();
				self::$modules[] = $moduleObject;
				Log::info('Модуль '.$moduleName.' загружен.');
				Database::query('INSERT INTO modules(name) VALUES(%s);', $moduleName);
				$moduleObject->install();
				Log::info('Модуль '.$moduleName.' установлен.');	
			}catch (\Exception $ex) {
				Log::error($ex->getFile().':'.$ex->getLine().' MODULE_INSTALL_FAILED '.$ex->getMessage());
			}
		}
		static public function uninstall($moduleName) {
			try {
				$res = Database::query('SELECT count(id) as count FROM modules WHERE name=%s;', $moduleName);
				if ($res[0]->count < 1) throw new \Exception($moduleName.' not installed.');
				$moduleObject = self::getModule($moduleName);
				if ($moduleObject===null) return;
				$moduleObject->uninstall();
				for($key=0, $length=\count(self::$modules); $key<$length; ++$key) {
					if (\get_class(self::$modules[$key]) === $moduleName) {
						unset(self::$modules[$key]);
						Log::info('Модуль '.$moduleName.' выгружен.');
						break;
					}
				}
				Database::query('DELETE FROM modules WHERE name=%s;', $moduleName);
				Log::info('Модуль '.$moduleName.' удален.');	
			}catch (\Exception $ex) {
				Log::error($ex->getFile().':'.$ex->getLine().' MODULE_UNINSTALL_FAILED '.$ex->getMessage());
			}
		}
		
		static public function addEventListener($path, $moduleName, $listener, $equal=true, $options=NULL) {
			
		}
		static public function hasEventListener($type) {
			
		}
		static public function removeEventListener($path, $moduleName, $listener, $equal=true) {
			
		}
		static public function willTrigger($type) {
			
		}
	}
	
}