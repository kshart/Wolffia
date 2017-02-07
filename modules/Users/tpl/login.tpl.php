<html>
<body>
	<div>
		<h3>Авторизация 1</h3>
		<input type="text" id="username"/>
		<input type="password" id="password"/>
		<button onclick="send();">Отправить</button>
	</div>
	<div id="auth">
		<h3>Авторизация 2</h3>
		<input type="text" data-user-input="username"/>
		<input type="password" data-user-input="password"/>
	</div>
	<div>
		<h3>Создание аккаунта</h3>
		<input type="text" id="CAusername"/>
		<input type="password" id="CApassword"/>
		<button onclick="createAccount();">Отправить</button>
	</div>
<?php 
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
?>
	<script src="/js/BigInteger.js"></script>
	<script src="/js/sha256.js"></script>
	<script src="/js/jquery-3.1.0.js"></script>
	<script src="/js/jquery.user.js"></script>
	<script>
	function send() {
		$.user.auth(document.getElementById("username").value, document.getElementById("password").value);
	}
	function sendA() {
		function H(str) {
			return CryptoJS.SHA256(str).toString();
		}
		function fitTo64(str) {
			if (str.length<64) str += '0'.repeat(64-str.length);
			return str;
		}
		function fitTo64left(str) {
			if (str.length<64) str = '0'.repeat(64-str.length)+str;
			return str;
		}
		function bintToHex(b) {
			var str = b.toString(16), i;
			for(i=0; i<str.length && str[i]==='0'; i++) {};
			return str.substr(i);
		}
		var U = document.getElementById("username").value,
			p = document.getElementById("password").value,
			N = bigInt("115B8B692E0E045692CF280B436735C77A5A9E8A9E7ED56C965F87DB5B2A2E9B", 16),
			g = bigInt(2),
			a = bigInt.randBetween(N, "1e200"), A, clientK, req = new XMLHttpRequest(), aStr;
		
		A = g.modPow(a, N);
		function firstStep () {
			if ( req.status===200 && req.readyState===4 ) {
				var s, B, spacePos=0;
				spacePos = req.responseText.indexOf(' ');
				if (spacePos < 0) return;
				s = bigInt(req.responseText.substr(0, spacePos), 16);
				B = bigInt(req.responseText.substr(spacePos+1), 16);
				
				var u = bigInt( H(bintToHex(A)+bintToHex(B)), 16),
					k = bigInt("42f303a20fb5696dcf6585de0ae05aca8db1bf26e18757ef215f9d543fd18126", 16),
					x = bigInt( H(bintToHex(s)+p), 16), clientS, clientK, M;
				console.log("a="+bintToHex(a));
				console.log("A="+bintToHex(A));
				console.log("B="+bintToHex(B));
				
				clientS = (B.subtract(k.multiply(g.modPow(x, N)))).modPow(a.plus(u.multiply(x)), N);
				clientK = bigInt( H(bintToHex(clientS)), 16);
				M = H("f88bd56f4a0b34ffe63bc124ecd5a1943de0a2f2145f392e2a24e7ac114289b8" + H(U) + s.toString(16) + bintToHex(A) + bintToHex(B) + bintToHex(clientK));
				
				console.log("M="+M);
				console.log("u="+u.toString(16));
				console.log("clientS="+bintToHex(clientS)+"\nclientK="+bintToHex(clientK)+"\nB:"+B.toString(10));
				document.cookie = "M="+M+";path=/;";
				document.cookie = "U="+H(U+M)+";path=/;";
				req.open("POST", "/user/auth");
				req.onreadystatechange = secondStep;
				req.send(M.toString());
				console.log(U+M);
			}
		}
		function secondStep () {
			if ( req.status===200 && req.readyState===4 ) {
				console.log("serverS="+req.responseText);
			}
		}
		aStr = fitTo64left(A.toString(16));
		req.open("POST", "/user/auth");
		req.onreadystatechange = firstStep;
		req.send(U+" "+aStr);
	}
	function createAccount() {
		function H(str) {
			return CryptoJS.SHA256(str).toString();
		}
		function fitTo64(str) {
			if (str.length<64) str += '0'.repeat(64-str.length);
			return str;
		}
		function fitTo64left(str) {
			if (str.length<64) str = '0'.repeat(64-str.length)+str;
			return str;
		}
		function bintToHex(b) {
			var str = b.toString(16), i;
			for(i=0; i<str.length && str[i]==='0'; i++) {};
			return str.substr(i);
		}
		var U = document.getElementById("CAusername").value,
			p = document.getElementById("CApassword").value,
			N = bigInt("115B8B692E0E045692CF280B436735C77A5A9E8A9E7ED56C965F87DB5B2A2E9B", 16),
			g = bigInt(2), s, x, v, sStr = 0, vStr, req = new XMLHttpRequest();
		do {
			s = fitTo64( bintToHex(bigInt( H(bigInt.randBetween(N, "1e100").toString(16)) , 16).modPow(1, N)) );
			x = bigInt( H(s+p), 16);
			v = g.modPow(x, N);
			vStr = v.toString(16);
		} while(vStr.length !== 64);
		req.open("POST", "/user/create");
		req.send(U+" "+s+vStr);
	}
	</script>
</body>
</html>