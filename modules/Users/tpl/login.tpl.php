<html>
<body>
	<div>
		<h3>Авторизация</h3>
		<input type="text" id="username"/>
		<input type="password" id="password"/>
		<button onclick="sendA();">Отправить</button>
	</div>
	<div>
		<h3>Создание аккаунта</h3>
		<input type="text" id="CAusername"/>
		<input type="password" id="CApassword"/>
		<button onclick="createAccount();">Отправить</button>
	</div>
<?php 
	$user = [];
	$user['users'] = core\Database::query('SELECT * FROM user;');
	$user['session'] = core\Database::query('SELECT * FROM session;');
	foreach ($user['users'] as $key=>$value) {
		echo '<div>'.$value->I.':'.$value->s.':'.$value->v.' '.'<a href="/?url='.$value->I.' class="btn btn-danger">Удалить</a></div>';
	}
	if ($user['session']!==false) foreach ($user['session'] as $key=>$value) {
		echo '<div>'.$value->userID.':'.$value->agenthash.':'.$value->timeCreated.':'.$value->M.':'.$value->N.' '.'<a href="/?url='.$value->I.' class="btn btn-danger">Удалить</a></div>';
	}
?>
	<script src="/js/BigInteger.js"></script>
	<script src="/js/sha256.js"></script>
	<script>
	/*var U = "username", p = "username", s = "0",
		N = bigInt("115b8b692e0e045692cf280b436735c77a5a9e8a9e7ed56c965f87db5b2a2ece3", 16), g = bigInt(2),
		k = bigInt(CryptoJS.SHA256(N.toString(16)+g.toString(16)).toString(), 16),
		x = bigInt(CryptoJS.SHA256(s+CryptoJS.SHA256(U+":"+p)).toString(), 16),
		v = g.modPow(x, N);
	//console.log("x="+x.toString(16));
	//console.log("v="+v.toString(16));

	var a = bigInt.randBetween("0", "1e100"),
		A = g.modPow(a, N);

	//console.log("a="+a.toString(16));
	//console.log("A="+A.toString(16));
	var b = bigInt.randBetween("0", "1e100"),
		B = ((k.multiply(v)).add(g.modPow(b, N))).mod(N);
	//console.log("b="+b.toString(16));
	//console.log("B="+B.toString(16));
	//console.log((k.multiply(v)).toString(16));
	//B=k*v + g^b % N
	var u = bigInt(CryptoJS.SHA256(A.toString(16)+B.toString(16)).toString(), 16);
	//console.log("u="+u.toString(16));
	//x = H(s, p)
	//S = (B - (k * g^x)) ^ (a + (u * x)) % N
	//K = H(S)
	var clientS = ((B.subtract(k.multiply(g.modPow(x, N))))).modPow(a.plus(u.multiply(x)), N),
		clientK = bigInt(CryptoJS.SHA256(clientS.toString(16)).toString(), 16);
	//console.log(clientK.toString(16));
	var serverS = (A.multiply(v.modPow(u, N))).modPow(b, N),
		serverK = bigInt(CryptoJS.SHA256(serverS.toString(16)).toString(), 16);
	//console.log(serverK.toString(16));

	var M = CryptoJS.SHA256(   bigInt(CryptoJS.SHA256(N.toString(16)), 16).xor(bigInt(CryptoJS.SHA256(g.toString(16)), 16)).toString(16)  + CryptoJS.SHA256(U) + s + A + B + clientK).toString();*/
	//console.log(M);
	//console.log(CryptoJS.SHA256(A.toString(16) + M + serverK.toString(16)).toString());
	//M = H( H(N) XOR H(g), H(I), s, A, B, K)
	// Calculate bx = g^x % N
	// Calculate (B - k * bx + N * k ) % N
	//var bx = this.g.modPow(x, this.N);
	//var btmp = B.add(this.N.multiply(this.k)).subtract(bx.multiply(this.k)).mod(this.N);
	
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
				req.open("POST", "/user/auth");
				req.onreadystatechange = secondStep;
				req.send(M.toString());
				document.cookie = "M="+M+";path=/;";
				document.cookie = "U="+CryptoJS.SHA256(U+M).toString()+";path=/;";
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
		console.log("x_="+(s+p));
		console.log(U, p, N.toString(16), x.toString(16), s, vStr);
		//mmmm
		//U=hhhh
		//p=hhhh
		//N=115b8b692e0e045692cf280b436735c77a5a9e8a9e7ed56c965f87db5b2a2e9b
		//x=76a717fe4a36428d84ed9d023fb9774806d55bd92335e090a283474546096901
		//s=16f13df24616fc84453acb4a35306d25f031c0a20ed5a11b15fcc1f98b3380e0
		//v=104b0820ac7d397e069baa9ae3abde3af67a2f77860ce4eb3bc7934b9c8988db
		//p=hhhh
		//v=104b0820ac7d397e069baa9ae3abde3af67a2f77860ce4eb3bc7934b9c8988db
		//x=76a717fe4a36428d84ed9d023fb9774806d55bd92335e090a283474546096901
		//s=16f13df24616fc84453acb4a35306d25f031c0a20ed5a11b15fcc1f98b3380e0
		req.open("POST", "/user/create");
		req.send(U+" "+s+vStr);
	}
	</script>
</body>
</html>