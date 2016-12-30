<?php

class FirstModule extends core\Module {
	public $a = 0;
	public $disp;
	public function __construct() {
	}
	public function install() {
		$this->disp = new core\EventDispatcher(['on', 'off']);
		$this->disp->addEventListener('on', 'on', $this);
		$event = new core\Event('on');
		$event2 = clone $event;
		$this->disp->dispatchEvent($event2);
	}
	public function uninstall() {
		
	}
	public function on($event) {
		core\Log::info('FirstModule:on');
	}
}