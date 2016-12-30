<?php
class Market extends core\Module {
	public function install() {
		
		core\PathManager::addEventListener('market', 'Market', 'mainDispatcher', false);
	}
	public function uninstall() {
		core\PathManager::removeEventListener('market', 'Market', 'mainDispatcher', false);
		
	}
	
	public function mainDispatcher($event) {
		$event->end = true;
		switch ($event->overURL) {
			case '':
				include 'tpl/main.tpl.php';
				break;
			case '/categories':
				$categories = core\Database::query('SELECT * FROM market_category;');
				if ($categories === false) {
					
					break;
				}
				echo json_encode($categories);
				break;
			case '/items':
				$searchData = file_get_contents('php://input', '', NULL, 0, 1024);
				if ($searchData === '') {
					$items = core\Database::query('SELECT * FROM market_item LIMIT 10;');
					echo json_encode($items);
				}
				break;
			case '/cart':
				break;
			default:
				header('Location: /market');
				
		}
	}
	public function getItems() {
		
	}
}

