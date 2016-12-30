<?php

namespace core {
	class ServerConfig {
		const dbConnect = 'mysql';
		const dbConnectHost = 'localhost';
		const dbConnectUser = 'root';
		const dbConnectPassword = '';
		const dbConnectDatabase = 'newtest';
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
			func_num_args();
			$valuei = 1;
			$value = null;
			$pos = 0;
			$length = strlen($sql);
			$posTo = strpos($sql, '%', $pos);
			$resultSQL = '';
			while ($posTo !== false) {
				$resultSQL .= substr($sql, $pos, $posTo-$pos);
				$valueType = substr($sql, $posTo+1, 1);
				switch($valueType) {
					case 'i':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlInt($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						break;
					case 's':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlString($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						break;
					case 'b':
						$valueRaw = func_get_arg($valuei);
						$value = self::sqlBool($valueRaw);
						$valuei++;
						$pos = $posTo+2;
						break;
					default:
						$value = '%';
						$pos = $posTo+1;
				}
				$resultSQL .= $value;
				$posTo = strpos($sql, '%', $pos);
			}
			$resultSQL .= substr($sql, $pos);
			
			$res = self::$link->query($resultSQL);
			Log::info($resultSQL);
			if (self::$link->errno) {
				Log::error('DB_ERROR '.self::$link->error.' '.$sql);
			}
			if ( \is_bool($res) ) return $res;
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
			return '"'.$string.'"';
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
	class StreamManager {
		
		static public function open() {
			
		}
		static public function close() {
			
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
	class Server {
		static public function requestGet($name, $type) {
			
		}
		static public function requestPost($name, $type) {
			
		}
		//static public function 
	}
	class Connect {
		const POST = 'post';
		const GET = 'get';
		const A09_STR = 1;
		public static function init() {
			
		}
		public static function getResource($name, $type=self::A09_STR, $connectType=self::POST) {
			if ($connectType===self::GET) {
				switch($type) {
					case self::A09_STR:
						return filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
				}
			}
			//core\Connect::getResource("core.Module.UserData", Connect::STANDART_STR, Connect::POST);
		}
	}


	
}