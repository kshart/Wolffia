;(function($, window, document, undefined) {
	"use strict";
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
	var N = bigInt("115B8B692E0E045692CF280B436735C77A5A9E8A9E7ED56C965F87DB5B2A2E9B", 16),
		g = bigInt(2),
		k = bigInt("42f303a20fb5696dcf6585de0ae05aca8db1bf26e18757ef215f9d543fd18126", 16);
	$.user = {
		create:function (username, password) {
			console.log(username, password);
			
		},
		auth:function (username, password) {
			console.log(username, password);
			var U = username,
				p = password,
				a = bigInt.randBetween(N, "1e200"),
				A, aStr;
			A = g.modPow(a, N);
			aStr = fitTo64left(A.toString(16));
			$.post("/user/auth", U+" "+aStr, function (data, textStatus, jqXHR) {
				if (textStatus==="success") {
					var s, B, spacePos, u, x, clientS, clientK, M;
					spacePos = data.indexOf(' ');
					if (spacePos < 0) return;
					s = bigInt(data.substr(0, spacePos), 16);
					B = bigInt(data.substr(spacePos+1), 16);
					u = bigInt( H(bintToHex(A)+bintToHex(B)), 16);
					x = bigInt( H(bintToHex(s)+p), 16);
					clientS = (B.subtract(k.multiply(g.modPow(x, N)))).modPow(a.plus(u.multiply(x)), N);
					clientK = bigInt( H(bintToHex(clientS)), 16);
					M = H("f88bd56f4a0b34ffe63bc124ecd5a1943de0a2f2145f392e2a24e7ac114289b8" + H(U) + bintToHex(s) + bintToHex(A) + bintToHex(B) + bintToHex(clientK));
					$.post("/user/auth", H(U+M)+M, function (data, textStatus, jqXHR) {
						if (textStatus==="success") {
							console.log("serverS="+data, U, M);
							document.cookie = "M="+M+";path=/;EXPIRES=Sun Jan 01 2090 05:00:00 GMT+0500";
							document.cookie = "U="+H(U+M)+";path=/;EXPIRES=Sun Jan 01 2090 05:00:00 GMT+0500";
						}
					});
				}
			}).fail(function() {
				alert( "error" );
			});
		},
		authFromForm:function (id) {
			var username = $("#"+id+" input[data-user-input='username']")[0].value, password = $("#"+id+" input[data-user-input='password']")[0].value;
			console.log(username, password);
			var U = username,
				p = password,
				a = bigInt.randBetween(N, "1e200"),
				A, aStr;
			A = g.modPow(a, N);
			aStr = fitTo64left(A.toString(16));
			$.post("/user/auth", U+" "+aStr, function (data, textStatus, jqXHR) {
				if (textStatus==="success") {
					var s, B, spacePos, u, x, clientS, clientK, M;
					spacePos = data.indexOf(' ');
					if (spacePos < 0) return;
					s = bigInt(data.substr(0, spacePos), 16);
					B = bigInt(data.substr(spacePos+1), 16);
					u = bigInt( H(bintToHex(A)+bintToHex(B)), 16);
					x = bigInt( H(bintToHex(s)+p), 16);
					clientS = (B.subtract(k.multiply(g.modPow(x, N)))).modPow(a.plus(u.multiply(x)), N);
					clientK = bigInt( H(bintToHex(clientS)), 16);
					M = H("f88bd56f4a0b34ffe63bc124ecd5a1943de0a2f2145f392e2a24e7ac114289b8" + H(U) + bintToHex(s) + bintToHex(A) + bintToHex(B) + bintToHex(clientK));
					$.post("/user/auth", H(U+M)+M, function (data, textStatus, jqXHR) {
						if (textStatus==="success") {
							console.log("serverS="+data, U, M);
							document.cookie = "M="+M+";path=/;";
							document.cookie = "U="+H(U+M)+";path=/;";
						}
					});
				}
			}).fail(function() {
				alert( "error" );
			});
		},
	};
	$.fn.user = function (option) {
		if (typeof option !== "object" || option==undefined) return;
		var element = getElement(option);
		this[0].appendChild(element);
		return element;
	};
})(jQuery, window, document);
