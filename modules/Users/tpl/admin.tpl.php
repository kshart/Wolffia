<ul class="nav nav-tabs nav-sidebar" role="tablist">
	<li class="active"><a class="btn btn-sidebar" href="#tab_user_1" role="tab" data-toggle="tab">Пользователи</a></li>
	<li><a class="btn btn-sidebar" href="#tab_user_2" role="tab" data-toggle="tab">Добавить пользователя</a></li>
</ul>
<div class="tab-content">
	<div id="tab_user_1" class="tab-pane fade in active tab-modules">
		
	</div>
	<div id="tab_user_2" class="tab-pane fade">
		<h3>Новый пользователь</h3>
		Логин:<input type="text" id="CAusername"/>
		Пароль:<input type="password" id="CApassword"/>
		<a class="btn btn-primary" onclick="createAccount();">Создать</a>
	</div>
</div>
<script src="/js/BigInteger.js"></script>
<script src="/js/sha256.js"></script>
<script>
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