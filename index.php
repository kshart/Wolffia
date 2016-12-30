<?php
//using /database/
//ob_start();
//ob_get_contents() 
$time_start = microtime(true);
$loginfo = fopen('log.html', 'a');
error_reporting(E_ALL&~E_NOTICE);//build&~E_NOTICE
set_error_handler(create_function('$c, $m, $f, $l', 'throw new CodeException($m, $c, $f, $l);'), E_ALL&~E_NOTICE);

class CodeException extends Exception {
	public function __construct($message, $errorLevel = 0, $errorFile = '', $errorLine = 0) {
		parent::__construct($message, $errorLevel);
		$this->file = $errorFile;
		$this->line = $errorLine;
	}
}
include_once '/core/tool.php';
include_once '/core/modules.php';
include_once '/core/event.php';


class ResponseLine extends core\EventDispatcher {
	public function __construct() {
		//$stdout = null;
		parent::__construct(['onload']);
		core\Database::init();
	}
	public function __destruct() {
		core\ModuleManager::unload();
	}
	function start() {
		core\ModuleManager::load();
		//core\ModuleManager::install('Admin');
		//core\PathManager::addEventListener('admin', 'PageBuilder', 'adminPages', false);
		$event = new core\Event('onload');
		$this->dispatchEvent($event);
		core\PathManager::dispatch();
	}
}



//fclose(STDOUT);
//fclose(STDERR);
//$STDOUT = fopen('application.log', 'wb');
//$STDERR = fopen('error.log', 'wb');





$responseLine = new ResponseLine();
$responseLine->start();
$time_end = microtime(true);
core\Log::info('Выполнено за '.($time_end-$time_start).'сек');
//event
//eventDispatcher
//
//fclose($loginfo);