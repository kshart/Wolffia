<?php
use core\Connect;

class Users extends core\Module {
	const MIN_USERNAME_LENGTH = 6;
	const MAX_USERNAME_LENGTH = 32;
	const V_LENGTH = 64;
	const S_LENGTH = 64;
	const ATH_2ROUND_REQUEST_LENGTH = 128;
	const ATH_2ROUND_U_LENGTH = 64;
	const ATH_2ROUND_M_LENGTH = 64;
	private $username = null;
	private $userID = null;
	private $permission = '';
	private $permissionAssociative = [];
	public function __construct() {
		$this->name = 'standart/Users';
		$this->description = 'Модуль для работы с пользователями и их ограничениями.';
		$this->version = '0.1a';
		$this->adminPanel = 'tpl/admin.tpl.php';
		try {
			include 'permissions.php';
		}catch (Exception $exc) {
			unlink(__DIR__.'/permissions.php');
			echo $exc->getTraceAsString();
		}
		$this->permissionAssociative = array_merge($this->permissionAssociative, $permissions);
	}
	public function install() {
/*
CREATE TABLE user (
	id int UNSIGNED AUTO_INCREMENT,
	I varchar(32) NOT NULL,
	s char(64) NOT NULL,
	v char(65) NOT NULL,
	permission varchar(32),
	Primary key(id)

)
CREATE TABLE session (
	userID int UNSIGNED NOT NULL,
	Ihash char(64) NOT NULL,
	agenthash char(64) NOT NULL,
	timeCreated timestamp NOT NULL,
	status ENUM('valid','auth') NOT NULL,
	M char(65) NOT NULL,
	R char(65) NOT NULL,
	Foreign key(userID) references user(id) on update cascade on delete cascade
)

g = 2
N = 115b8b692e0e045692cf280b436735c77a5a9e8a9e7ed56c965f87db5b2a2ece3

)

*/
		core\Database::query('CREATE TABLE user (id int UNSIGNED AUTO_INCREMENT,I varchar(32) NOT NULL,s char(64) NOT NULL,v char(65) NOT NULL,permission varchar(32),Primary key(id));');
		core\Database::query('CREATE TABLE session (userID int UNSIGNED NOT NULL,Ihash varchar(64) NOT NULL,agenthash char(64) NOT NULL,timeCreated timestamp NOT NULL, status ENUM("valid","auth") NOT NULL,M char(65) NOT NULL,R char(65) NOT NULL,Foreign key(userID) references user(id) on update cascade on delete cascade);');
		core\PathManager::addEventListener('user', 'Users', 'mainDispatcher', false);
		$this->permissionsRegistrate(['PM_USERS_CREATE_USER', 'PM_USERS_DELETE_USER', 'PM_USERS_GET_LIST', 'PM_USERS_CHANGE_USER', 'PM_USERS_CHANGE_PERMISSION_ASSOC']);
		//core\Database::query('INSERT INTO user (I, s char(64) NOT NULL,v char(65) NOT NULL,permission);');
	}
	public function uninstall() {
		unlink(__DIR__.'/permissions.php');
		core\Database::query('DROP TABLE session;');
		core\Database::query('DROP TABLE user;');
		core\PathManager::removeEventListener('user', 'Users', 'mainDispatcher', false);
	}
	private function detectUser() {
		$userEmpty = core\Database::query('SELECT id FROM user LIMIT 1');
		if ($userEmpty!==false&&count($userEmpty)===1) {
			$U = filter_input(INPUT_COOKIE, 'U', FILTER_SANITIZE_SPECIAL_CHARS);
			$M = filter_input(INPUT_COOKIE, 'M', FILTER_SANITIZE_SPECIAL_CHARS);
			if (strlen($U)<64&&strlen($M)<64) return;
			$userAgentHash = hash('sha256', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS));
			$userID = core\Database::query('SELECT userID, I, permission FROM session JOIN user ON session.userID=user.id WHERE status="valid" AND Ihash=%s AND M=%s AND agenthash=%s LIMIT 1', $U, $M, $userAgentHash);
			if ($userID === false) return;
			$this->userID = $userID[0]->userID;
			$this->username = $userID[0]->I;
			$this->permission = $userID[0]->permission;
		}else{
			$this->userID = -1;
			$this->username = 'root';
			$this->permission = $this->permissionAssociative['PM_ALL'];
		}
		
	}
	private function bintToHex($b) {
		$str = $b->toHex();
		for($i=0; $i<strlen(str) && $str[$i]=='0'; $i++) {};
		return substr($str, $i);
	}
	
	
	public function mainDispatcher($event) {
		$event->end = true;
		switch($event->overURL) {
			case '/':
			case '':
			case '/login':
				include 'tpl/login.tpl.php';
				return;
			case '/create':
				if (!$this->userCheckPermission(['PM_USERS_CREATE_USER'])) {
					core\Log::error('Users::userCreate У тебя нет прав, Добби;');
					header('HTTP/1.1 400 Bad Request');
					return false;
				}
				$accountData = file_get_contents('php://input', '', NULL, 0, 161);//32+1+64+64 length username + space + salt + v
				$spacePos = strpos($accountData, ' ');
				$username = substr($accountData, 0, $spacePos);
				$str = substr($accountData, $spacePos+1, 128);
				if (strlen($username)>self::MAX_USERNAME_LENGTH ||
					strlen($username)<self::MIN_USERNAME_LENGTH ||
					strlen($str)!=128 ||
					filter_var($username, FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^\D[a-zA-Z\d]*$/')))===false ) {
					header('HTTP/1.1 400 Bad Request');
					return;
				}
				$salt = substr($str, 0, 64);
				$vstr = substr($str, 64, 64);
				$result = $this->userCreate($username, $salt, $vstr);
				return $result;
			case '/delete':
				if (!$this->userCheckPermission(['PM_USERS_DELETE_USER'])) {
					core\Log::error('Users::userDelete У тебя нет прав, Добби;');
					header('HTTP/1.1 400 Bad Request');
					return false;
				}
				$username = file_get_contents('php://input', '', NULL, 0, self::MAX_USERNAME_LENGTH);
				if (strlen($username)<self::MIN_USERNAME_LENGTH ||
					filter_var($username, FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^\D[a-zA-Z\d]*$/')))===false ) {
					header('HTTP/1.1 400 Bad Request');
					return;
				}
				$result = $this->userDelete($username);
				if ($result===false) header('HTTP/1.1 400 Bad Request');
				return;
			case '/list':
				if (!$this->userCheckPermission(['PM_USERS_GET_LIST'])) {
					core\Log::error('Users::userDelete У тебя нет прав, Добби;');
					header('HTTP/1.1 400 Bad Request');
					return false;
				}
				$limit = file_get_contents('php://input', '', NULL, 0, 64);
				$spacePos = strpos($limit, ' ');
				if ($limit==='') {
					$users = core\Database::query('SELECT I, permission FROM user;');
					echo json_encode($users);
					return;
				}else if ($spacePos===false) {
					$users = core\Database::query('SELECT I, permission FROM user LIMIT %i;', $limit);
					echo json_encode($users);
					return;
				}else{
					$startPos = substr($limit, 0, $spacePos);
					$endPos = substr($limit, $spacePos+1);
					$users = core\Database::query('SELECT I, permission FROM user LIMIT %i..%i;', $startPos, $endPos);
					echo json_encode($users);
					return;
				}
				return;
			case '/auth':
				$loginData = file_get_contents('php://input', '', NULL, 0, 97);//32+1+64 length username + space + A
				$spacePos = strpos($loginData, ' ');
				if ($spacePos !== false) {
					//open session
					//req U+" "+A.toString(16)
					$login = substr($loginData, 0, $spacePos);
					$Astr = substr($loginData, $spacePos+1, 64);
					if (strlen($login)>32 ||
						strlen($Astr)!=64 ||
						filter_var($login, FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/^\D[a-zA-Z\d]*$/')))===false ) {
						echo 'unconditional input';
						return;
					}
					$user = core\Database::query('SELECT id, s, v, I FROM user WHERE I=%s;', $login);
					if ($user === false || count($user) < 1) return;
					
					include_once '/core/BigInteger.php';
					
					$userAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);
					$s = $user[0]->s;
					$userID = $user[0]->id;
					$v = new Math_BigInteger($user[0]->v, 16);
					$A = new Math_BigInteger($Astr, 16);

					$bstr = '';
					for($i=0; $i<65; ++$i) $bstr .= dechex(rand(0, 15));
					
					$b = new Math_BigInteger($bstr, 16);
					$g = new Math_BigInteger(2);
					$k = new Math_BigInteger('42f303a20fb5696dcf6585de0ae05aca8db1bf26e18757ef215f9d543fd18126', 16);//SHA256(N+g)
					$N = new Math_BigInteger('115B8B692E0E045692CF280B436735C77A5A9E8A9E7ED56C965F87DB5B2A2E9B', 16);
					
					//((k.multiply(v)).add(g.modPow(b, N)));
					$kmv = $k->multiply($v);
					$B = $kmv->add($g->modPow($b, $N));
					$uStr = hash('sha256', self::bintToHex($A).self::bintToHex($B));
					$u = new Math_BigInteger($uStr, 16);
					$vpowuN = $v->modPow($u, $N);
					$amvpowuN = $A->multiply($vpowuN);
					$serverS = $amvpowuN->modPow($b, $N);
					$serverK = hash('sha256', self::bintToHex($serverS));
					//H(N) xor H(g) = "f88bd56f4a0b34ffe63bc124ecd5a1943de0a2f2145f392e2a24e7ac114289b8"
					//H(H(N) xor H(g), H(I), s, A, B, K);
					$serverM = hash('sha256', 'f88bd56f4a0b34ffe63bc124ecd5a1943de0a2f2145f392e2a24e7ac114289b8'.hash('sha256', $login).$s.self::bintToHex($A).self::bintToHex($B).$serverK );
					$serverN = hash('sha256', $A->toString().$serverM.$serverK);
					$Ihash = hash('sha256', $user[0]->I.$serverM);
					$reqEmptySession = core\Database::query('SELECT userID FROM session WHERE userID=%s AND agenthash=%s LIMIT 1', $userID, $userAgentHash);
					if (count($reqEmptySession)>0) {
						core\Database::query('UPDATE session SET Ihash=%s, M=%s, R=%s, timeCreated=now(), status="valid" WHERE userID=%i AND agenthash=%s', $Ihash, $serverM, $serverN, $userID, $userAgentHash);
					}else{
						core\Database::query('INSERT INTO session(userID, M, R, agenthash, Ihash, status) VALUES(%i, %s, %s, %s, %s, "auth")', $userID, $serverM, $serverN, $userAgentHash, $Ihash);
					}
					echo $s.' '.self::bintToHex($B);
					//H(A, M, K);
					//var b = bigInt.randBetween("0", "1e100"),
					//B = ((k.multiply(v)).add(g.modPow(b, N))).mod(N);
				}else{
					//save session
					//req M
					$data = file_get_contents('php://input', '', NULL, 0, self::ATH_2ROUND_REQUEST_LENGTH);
					
					if (strlen($data)!==self::ATH_2ROUND_REQUEST_LENGTH) return;
					$U = substr($data, 0, self::ATH_2ROUND_U_LENGTH);
					$M = substr($data, self::ATH_2ROUND_U_LENGTH);
					$userAgentHash = hash('sha256', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS));
					$userID = core\Database::query('UPDATE session SET status="valid" WHERE status="auth" AND Ihash=%s AND M=%s AND agenthash=%s', $U, $M, $userAgentHash);
				}
				break;
			case '/logout':
				$U = core\Connect::getResource('U', core\Connect::A09_STR, core\Connect::COOKIES);
				$M = core\Connect::getResource('M', core\Connect::A09_STR, core\Connect::COOKIES);
				$userAgentHash = hash('sha256', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_SPECIAL_CHARS));
				$userID = core\Database::query('DELETE FROM session WHERE Ihash=%s AND M=%s AND agenthash=%s', $U, $M, $userAgentHash);
				break;
			default:
		}
	}
	
	
	
	public function userCheckPermission($permissions, $username=null) {
		$perm = '';
		foreach($permissions as $key=>$value) {
			if (isset($this->permissionAssociative[$value])) {
				$perm .= $this->permissionAssociative[$value];
			}else{
				core\Log::error('Users::userCheckPermission ХУЕВЫЕ Premissions "'.$value.'";');
				return false;
			}
		}
		if (strlen($perm)<=0) return false;
		if ($username === null) {
			if ($this->userID === null) $this->detectUser();
			if ($this->userID === null) return false;
			if (strpos($this->permission, $this->permissionAssociative['PM_ALL']) !== false) return true;
			for($i=0, $len=strlen($perm); $i<$len; $i++) {
				$p = substr($perm, $i, 1);
				if (strpos($this->permission, $p) === false) return false;
			}
		}else{
			$user = core\Database::query('SELECT id, permission as count FROM user WHERE I=%s LIMIT 1;', $username);
			if ($user===false || count($user)<1) {
				core\Log::error('Users::userCheckPermission "'.$username.'" такого пользователя нет ;');
				return false;
			} 
			if (strpos($user[0]->permission, $this->permissionAssociative['PM_ALL']) !== false) return true;
			for($i=0, $len=strlen($perm); $i<$len; $i++) {
				$p = substr($perm, $i, 1);
				if ($p===$this->permissionAssociative['PM_ALL']) return true;
				if (strpos($user[0]->permission, $p) === false) return false;
			}
		}
		return true;
	}
	
	public function userCreate($username, $s, $v, $permission=[]) {
		//check username, v, s
		if (!$this->userCheckPermission(['PM_USERS_CREATE_USER'])) {
			core\Log::error('Users::userCreate У тебя нет прав, Добби;');
			return false;
		}
		if (strlen($username)<self::MIN_USERNAME_LENGTH || strlen($username)>self::MAX_USERNAME_LENGTH) {
			core\Log::error('Users::userCreate длина логина;');
			return false;
		}
		if (strlen($s)!==self::S_LENGTH || strlen($v)!==self::V_LENGTH) {
			core\Log::error('Users::userCreate длина s, v;');
			return false;
		}
		$user = core\Database::query('SELECT count(id)>0 as count FROM user WHERE I=%s LIMIT 1;', $username);
		if ($user === false || $user[0]->count==true) {
			core\Log::error('Users::userCreate Ты уже есть "'.$username.'";');
			return false;
		}
		$newPerm = '';
		foreach($permission as $key=>$value) {
			if (isset($this->permissionAssociative[$value])) {
				$newPerm .= $this->permissionAssociative[$value];
			}else{
				core\Log::error('Users::addPermissions ХУЕВЫЕ Premissions "'.$value.'";');
			}
		}
		core\Database::query('INSERT INTO user(I, s, v, permission) values(%s, %s, %s, %s);', $username, $s, $v, $newPerm);
		return true;
	}
	public function userDelete($username) {
		if (!$this->userCheckPermission(['PM_USERS_DELETE_USER'])) {
			core\Log::error('Users::userDelete У тебя нет прав, Добби;');
			return false;
		}
		core\Database::query('DELETE FROM user WHERE I=%s;', $username);
		return true;
	}
	
	public function addPermissions($arrPerm, $username) {
		if (!$this->userCheckPermission(['PM_USERS_CHANGE_USER'])) {
			core\Log::error('Users::addPermissions У тебя нет прав, Добби;');
			return false;
		}
		$newPerm = '';
		foreach($arrPerm as $key=>$value) {
			if (isset($this->permissionAssociative[$value])) {
				$newPerm .= $this->permissionAssociative[$value];
			}else{
				core\Log::error('Users::addPermissions ХУЕВЫЕ Premissions "'.$value.'";');
				return false;
			}
		}
		if (strlen($newPerm)<=0) return false;
		$user = core\Database::query('SELECT id, permission FROM user WHERE I=%s LIMIT 1;', $username);
		if ($user === false || !isset($user[0])) {
			core\Log::error('Users::addPermissions ХУЕВЫЙ ЛОГИН "'.$username.'";');
			return false;
		}
		$newPerm .= $user[0]->permission;
		$perm = '';
		for($i=0, $len=strlen($newPerm); $i<$len; $i++) {
			$p = substr($newPerm, $i, 1);
			if (strpos($perm, $p) === false) $perm .= $p;
		}
		core\Database::query('UPDATE user SET permission=%s WHERE id=%i;', $perm, $user[0]->id);
		if ($this->userID === $user[0]->id) {
			$this->permission = $perm;
		}
		core\Log::info('Users::addPermissions success username="'.$username.'" ['.implode(', ', $arrPerm).'];');
		return true;
	}
	public function deletePermissions($arrPerm, $username) {
		if (!$this->userCheckPermission(['PM_USERS_CHANGE_USER'])) {
			core\Log::error('Users::deletePermissions У тебя нет прав, Добби;');
			return false;
		}
		$deletePerm = '';
		foreach($arrPerm as $key=>$value) {
			if (isset($this->permissionAssociative[$value])) {
				$deletePerm .= $this->permissionAssociative[$value];
			}else{
				core\Log::error('Users::deletePermissions ХУЕВЫЕ Premissions "'.$value.'";');
				return false;
			}
		}
		if (strlen($deletePerm)<=0) return false;
		$user = core\Database::query('SELECT id, permission FROM user WHERE I=%s LIMIT 1;', $username);
		if ($user === false || !isset($user[0])) {
			core\Log::error('Users::deletePermissions ХУЕВЫЙ ЛОГИН "'.$username.'";');
			return false;
		}
		$perm = '';
		for($i=0, $len=strlen($user[0]->permission); $i<$len; $i++) {
			$p = substr($user[0]->permission, $i, 1);
			if (strpos($deletePerm, $p) === false && strpos($perm, $p) === false) $perm .= $p;
		}
		core\Database::query('UPDATE user SET permission=%s WHERE id=%i;', $perm, $user[0]->id);
		if ($this->userID === $user[0]->id) {
			$this->permission = $perm;
		}
		core\Log::info('Users::deletePermissions success username="'.$username.'" ['.implode(', ', $arrPerm).'];');
		return true;
	}
	public function permissionsRegistrate($arrPerm) {
		if (!$this->userCheckPermission(['PM_USERS_CHANGE_PERMISSION_ASSOC'])) {
			core\Log::error('Users::permissionsRegistrate У тебя нет прав, Добби;');
			return false;
		}
		foreach($arrPerm as $key=>$value) {
			if (!isset($this->permissionAssociative[$value])) {
				$this->permissionAssociative[$value] = mb_convert_encoding('&#'.(count($this->permissionAssociative)+0x21).';', 'UTF-8', 'HTML-ENTITIES');
			}
		}
		$file = fopen(__DIR__.'/permissions.php', 'w', true);
		fprintf($file, "<?php\n\$permissions = [");
		foreach($this->permissionAssociative as $key=>$value) {
			if ($value==='%') $value = '%%';
			fprintf($file, '\''.$key.'\'=>\''.$value.'\',');
		}
		fprintf($file, '];');
		fclose($file);
		return true;
	}
	
}
