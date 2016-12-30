<?php
namespace core {
	interface IEventDispatcher {
		//type:String, listener:Function, useCapture:Boolean = false, priority:int = 0, useWeakReference:Boolean = false  :void
		//event:Event  :Boolean
		//type:String  :Boolean
		//type:String, listener:Function, useCapture:Boolean = false  :void
		//type:String  :Boolean
		public function addEventListener($type, $listener, $object);
		public function dispatchEvent($event);
		public function hasEventListener($type);
		public function removeEventListener($type, $listener, $object);
		public function willTrigger($type);
	}
	interface IEventDispatcherStatic {
		static public function addEventListener($type, $listener, $object);
		static public function dispatchEvent($event);
		static public function hasEventListener($type);
		static public function removeEventListener($type, $listener, $object);
		static public function willTrigger($type);
	}
	class Event {
		//private $bubbles;// : Boolean
		public $cancelable;// : Boolean
		public $currentTarget = null;// : Object
		//private $eventPhase;// : uint
		public $target = null;// : Object
		public $type;// : String

		//type:String, bubbles:Boolean = false, cancelable:Boolean = false
		public function __construct($type, $cancelable = false) {
			$this->type = $type;
			$this->cancelable = $cancelable;
		}
		//:Event
		public function __clone() {
			return new Event($this->type, $this->cancelable);
		}
		//:Boolean
		//public function isDefaultPrevented() {}
		//:void
		//public function preventDefault() {}
		//:void
		///public function stopImmediatePropagation() {}
		//:void
		public function stopPropagation() {}
		//:String
		//public function __toString() {}

	}
	class EventDispatcher implements IEventDispatcher {
		protected $events = [];
		public function __construct($types) {
			foreach($types as $type) {
				$this->events[$type] = [];
			}
		}
		public function addEventListener($type, $listener, $object = null) {
			if ($object === null && !is_callable($listener)) return;
			if ($object !== null && !is_callable(array($object, $listener), true)) return;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $type) {
					$listenerArray[] = [$object, $listener];
					return;
				}
			}
		}
		public function dispatchEvent($event) {
			if ( !($event instanceof Event) ) return false;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $event->type) {
					foreach($listenerArray as &$listener) {
						if ($listener[0] === null) {
							$listener[1]($event);
						}else{
							$listener[0]->$listener[1]($event);
						}
					}
				}
			}
		}
		public function hasEventListener($type) {
			foreach($this->events as $eventType => &$eventsArray) {
				if ($eventType === $type) {
					return true;
				}
			}
			return true;
		}
		public function removeEventListener($type, $listener, $object = null) {
			if ($object === null && !is_callable($listener)) return;
			if ($object !== null && !is_callable(array($object, $listener), true)) return;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $type) {
					foreach($listenerArray as &$event) {
						if ($event[0] === $object && $event[1] === $listener) {
							unset($event);
							return;
						}
					}
					return;
				}
			}
		}
		public function willTrigger($type) {}
	}
	class ModuleEventDispatcher implements IEventDispatcher {
		protected $events = [];
		public function __construct($types) {
			foreach($types as $type) {
				$this->events[$type] = [];
			}
		}
		public function addEventListener($type, $moduleName, $listener) {
			if (!($moduleName instanceof String) && !\method_exists($moduleName, $listener)) return;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $type) {
					$listenerArray[] = [$moduleName, $listener];
					//add in base
					return;
				}
			}
		}
		public function dispatchEvent($event) {
			if ( !($event instanceof Event) ) return false;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $event->type) {
					foreach($listenerArray as &$listener) {
						$module = ModuleLoader::getModule($listener[0]);
						if ($module !== null && \method_exists($module, $listener[1])) {
							$module->$listener[1]($event);
						}else{
							Log::error(__FILE__.':'.__LINE__.' EVENT_DISPATCH_ERROR ');
						}
					}
				}
			}
		}
		public function hasEventListener($type) {
			foreach($this->events as $eventType => &$eventsArray) {
				if ($eventType === $type) {
					return true;
				}
			}
			return true;
		}
		public function removeEventListener($type, $moduleName, $listener) {
			if (!($moduleName instanceof String) && !\method_exists($moduleName, $listener)) return;
			foreach($this->events as $eventType => &$listenerArray) {
				if ($eventType === $type) {
					foreach($listenerArray as &$event) {
						if ($event[0] === $moduleName && $event[1] === $listener) {
							unset($event);
							//delete from bd;
							return;
						}
					}
					return;
				}
			}
		}
		public function willTrigger($type) {}
	}
}