<?php

namespace core {
	class ServerConfig {
		const dbConnect = 'mysql';
		const dbConnectHost = 'localhost';
		const dbConnectUser = 'root';
		const dbConnectPassword = '';
		const dbConnectDatabase = 'main';
		const dbConnectCharset = 'utf8';

	}
	
	class Database {
		private static $link = null;

		public static function init() {
			switch(ServerConfig::dbConnect){
				case 'mysql':
					self::$link = new \mysqli(ServerConfig::dbConnectHost, ServerConfig::dbConnectUser, ServerConfig::dbConnectPassword, ServerConfig::dbConnectDatabase);//mysql_connect();
					if (self::$link === false || self::$link->connect_errno) {
						Log::error('Ошибка соединения');
						break;
					}
					self::$link->set_charset(ServerConfig::dbConnectCharset);

			}
		}
		public static function query($sql) {
			if (!is_string($sql)) return false;
			
			$valuei = 1;
			$value = null;
			$pos = 0;
			$length = strlen($sql);
			$posTo = strpos($sql, '%', $pos);
			$resultSQL = '';
			while ($posTo !== false) {
				$resultSQL .= substr($sql, $pos, $posTo-$pos);
				switch($sql[$posTo+1]) {
					case 'i':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlInt($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						$resultSQL .= $value;
						break;
					case 's':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlString($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						$resultSQL .= $value;
						break;
					case 'v':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlValueName($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						$resultSQL .= $value;
						break;
					case 'b':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlBool($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						$resultSQL .= $value;
						break;
					case 'a':
						if (($posTo+3) > $length) return false;
						$value = '';
						$arrayType = $sql[$posTo+2];
						$arrayRaw = func_get_arg($valuei);
						if (!is_array($arrayRaw)) $arrayRaw = [];
						if ($arrayType==='i') {
							foreach($arrayRaw as $arrayKey=>$arrayValue) {
								$resultSQL .= self::sqlInt($arrayValue).',';
							}
						}else if ($arrayType==='b') {
							foreach($arrayRaw as $arrayKey=>$arrayValue) {
								$resultSQL .= self::sqlBool($arrayValue).',';
							}
						}else if ($arrayType==='s') {
							foreach($arrayRaw as $arrayKey=>$arrayValue) {
								$resultSQL .= self::sqlString($arrayValue).',';
							}
						}else if ($arrayType==='v') {
							foreach($arrayRaw as $arrayKey=>$arrayValue) {
								$resultSQL .= self::sqlValueName($arrayValue).',';
							}
						}
						$resultSQL = substr($resultSQL, 0, \strlen($resultSQL)-1);
						$valuei++;
						$pos = $posTo+3;
						break;
					default:
						$value = '%';
						$pos = $posTo+1;
				}
				$posTo = strpos($sql, '%', $pos);
			}
			$resultSQL .= substr($sql, $pos);
			
			$res = self::$link->query($resultSQL);
			Log::info($resultSQL);
			if (self::$link->errno) Log::error('DB_ERROR '.self::$link->error.' '.$sql);
			if ( \is_bool($res) ) {
				if ($res===false) return false;
				return self::$link->insert_id;
			}
			$arr = [];
			while ($row = $res->fetch_object()) {
				$arr[] = $row;
			}
			//$res->free_result();
			return $arr;
		}
		public static function sqlBool($bool) {
			if ($bool) return 'true';
			return 'false';
		}
		public static function sqlInt($int) {
			return (integer)$int;
		}
		public static function sqlString($string) {
			switch(ServerConfig::dbConnect){
				case 'mysql':
					return '"'.self::$link->real_escape_string($string).'"';
					
			}
		}
		public static function sqlValueName($string) {
			switch(ServerConfig::dbConnect){
				case 'mysql':
					return self::$link->real_escape_string($string);
					
			}
		}
		/*public static function sqlUint($uint) {
			return (integer)$int;
		}*/
	}
	class Log {
		static function info($str) {
			global $loginfo;
			fwrite($loginfo, '<p>'.$str."</p>\n");
			//echo '<p>'.$str.'</p>';
		}
		static function warning($str) {
			global $loginfo;
			fwrite($loginfo, '<p style="color:#FF0">'.$str."</p>\n");
			//echo '<p style="color:#FF0">'.$str.'</p>';
		}
		static function error($str) {
			global $loginfo;
			fwrite($loginfo, '<p style="color:#F00">'.$str."</p>\n");
			//echo '<p style="color:#F00">'.$str.'</p>';
		}
	}
	class PathManager {
	/*
	SELECT url, min(length(url)) FROM `url` WHERE (url like '/book%');
	SELECT url 
		FROM `url` 
		WHERE (equal=false AND '/book/546' like CONCAT(url,'%')) 
			OR (equal=true AND url='/book/546')
		ORDER BY length(url) DESC
	*/
		static function dispatch() {
			//$path = '';
			if (isset($_GET['path'])) {
				$path = filter_input(INPUT_GET, 'path', FILTER_SANITIZE_SPECIAL_CHARS);
			}else{
				$path = '';
			}
			switch($path) {
				//case '404':
					//include_once '/core/template/404.tpl.php';
					//return;
			}
			$array = Database::query('SELECT pages.url, modules.name as moduleName, pages.listener, pages.options FROM pages INNER JOIN modules ON pages.moduleID=modules.id WHERE (equal=false AND %s like CONCAT(url,"%")) OR (equal=true AND url=%s)ORDER BY length(url) DESC;', $path, $path);
			foreach($array as $page) {
				$event = new Event($page->url);
				$event->end = false;
				$event->options = $page->options;
				$event->overURL = substr($path, strlen($page->url));
				$moduleObject = ModuleManager::getModule($page->moduleName);
				if ($moduleObject === null)	continue;
				$listener = $page->listener;
				$moduleObject->$listener($event);
				if ($event->end) return; 
			}
			//var_dump($array);
			header('HTTP/1.1 404 Not Found');
			include_once 'core\template\404.tpl.php';
			//Log::info($_SERVER['QUERY_STRING'].' - '.$_SERVER['REQUEST_URI']);
		}

		static public function addEventListener($path, $moduleName, $listener, $equal=true, $options=NULL) {
			//logic sql function
			$checkURI = Database::query('SELECT count(url) as count FROM pages WHERE url=%s;', $path);
			if ($checkURI[0]->count > 0) return false;
			$checkModule = Database::query('SELECT id FROM modules WHERE name=%s;', $moduleName);
			if (\count($checkModule) < 1) return false;
			Database::query('INSERT INTO pages(moduleID, listener, url, equal, options) VALUE(%i, %s, %s, %b, %s);', $checkModule[0]->id, $listener, $path, $equal, $options);
			return true;
		}
		static public function hasEventListener($type) {
			foreach($this->events as $eventType => &$eventsArray) {
				if ($eventType === $type) return true;
			}
			return false;
		}
		static public function removeEventListener($path, $moduleName, $listener, $equal=true) {
			$checkURI = Database::query('SELECT modules.id FROM pages INNER JOIN modules ON pages.moduleID=modules.id WHERE pages.url=%s AND modules.name=%s AND pages.listener=%s AND pages.equal=%b;', $path, $moduleName, $listener, $equal);
			if (count($checkURI) < 1) return false;
			Database::query('DELETE FROM pages WHERE url=%s AND moduleID=%i AND listener=%s AND equal=%b;', $path, $checkURI[0]->id, $listener, $equal);
			return true;
		}
		static public function willTrigger($type) {}
	}
	
	class Connect {
		const POST = 'post';
		const GET = 'get';
		const A09_STR = 1;
		const URL = 2;
		const INT = 3;
		const FLOAT = 4;
		const JSON_OBJ = 5;
		public static function init() {
			
		}
		public static function getResource($name, $type=self::A09_STR, $connectType=self::POST) {
			if ($connectType===self::GET) {
				switch($type) {
					case self::A09_STR:
						return filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
					case self::INT:
						return filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT);
					case self::FLOAT:
						return filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_FLOAT);
					case self::URL:
						return filter_input(INPUT_GET, $name, FILTER_SANITIZE_URL);
				}
			}else if ($connectType===self::POST) {
				switch($type) {
					case self::A09_STR:
						return filter_input(file_get_contents('php://input', '', NULL, 0, 30000), $name, FILTER_SANITIZE_STRING);
					case self::INT:
						return filter_input(file_get_contents('php://input', '', NULL, 0, 11), $name, FILTER_SANITIZE_NUMBER_INT);
					case self::FLOAT:
						return filter_input(file_get_contents('php://input', '', NULL, 0, 40), $name, FILTER_SANITIZE_NUMBER_FLOAT);
					case self::URL:
						return filter_input(file_get_contents('php://input', '', NULL, 0, 10000), $name, FILTER_SANITIZE_URL);
					case self::JSON_OBJ:
						return json_decode(file_get_contents('php://input', '', NULL, 0, 30000));
				}
			}
			//core\Connect::getResource("core.Module.UserData", Connect::STANDART_STR, Connect::POST);
		}
	}


	
}
