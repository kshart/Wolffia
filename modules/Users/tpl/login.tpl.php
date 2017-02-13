<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo $Title;?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link href="/css/bootstrap.min.css" rel="stylesheet"/>
	<style>
		body {
			display: flex;
			justify-content: center;
			align-items: flex-start;
			background-color: #DDD;
		}
		.auth-content {
			background-color: #EEE;
			padding: 15px;
			width:100%;
			margin-top:0;
		}
		.auth-title {
			margin-top:0;
			display:block;
		}
		.auth-input {
			margin-bottom:15px;
			width:100%;
			display:block;
		}
		@media (min-width: 768px) {
			.auth-content {
				width: 300px;
			}
		}
		@media (min-width: 992px) {
			.auth-content {
				width: 400px;
			}
		}
		@media (max-width: 992px) {
			.auth-content {
				width: 400px;
			}
		}
		@media (max-width: 550px) {
			.auth-body {
				padding: 0 30px;
				width:100%;
			}
		}
		@media (min-height: 400px) {
			.auth-content {
				margin-top: 100px;
			}
		}
		@media (max-height: 400px) {
			body {
				align-items: stretch;
			}
			.auth-content {
				width:100%;
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
			}
		}
		
	</style>
</head>
<body>
	<div class="auth-content">
		<div id="auth" class="auth-body">
			<h3 class="auth-title">Авторизация</h3>
			<input class="auth-input" type="text" data-user-input="username"/>
			<input class="auth-input" type="password" data-user-input="password"/>
			<a class="btn btn-primary btn-block" href="#" onclick="$.user.authFromForm('auth');">Go</a>
		</div>
	</div>
	<div hidden>
		<h3>Создание аккаунта</h3>
		<input type="text" id="CAusername"/>
		<input type="password" id="CApassword"/>
		<button onclick="createAccount();">Отправить</button>
	</div>
<!--?php 
if (core\ModuleManager::getModule('Users')->userCheckPermission(['PM_ALL'])) {
	$user = [];
	$user['users'] = core\Database::query('SELECT * FROM user;');
	$user['session'] = core\Database::query('SELECT * FROM session;');
	foreach ($user['users'] as $key=>$value) {
		echo '<div>'.$value->I.':'.$value->s.':'.$value->v.' '.'<a href="/user/delete?username='.$value->I.'" class="btn btn-danger">Удалить</a></div>';
	}
	echo '<hr/>';
	if ($user['session']!==false) foreach ($user['session'] as $key=>$value) {
		echo '<div>'.$value->userID.':'.$value->agenthash.':'.$value->timeCreated.':'.$value->M.':'.$value->N.' '.'<a href="/?url='.$value->I.' class="btn btn-danger">Удалить</a></div>';
	}
}
?-->
	<script src="/js/BigInteger.js"></script>
	<script src="/js/sha256.js"></script>
	<script src="/js/jquery-3.1.0.js"></script>
	<script src="/js/jquery.user.js"></script>
	<script src="/js/bootstrap.min.js"></script>
</body>
</html>