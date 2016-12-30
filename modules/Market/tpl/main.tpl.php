<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width">
<title>Market</title>
<link href="/css/bootstrap.css" rel="stylesheet">
<style>
	body {
		background-image: url(img/bg.png);
		//background-origin: content-box;
		background-size:400px 400px;
	}
	.beforhead-navigation {
		width:100%;
	}
	.beforhead-navigation ul li {
		display: inline-block;
	}
	.beforhead-navigation ul {
		margin:4px 0;
	}
	.beforhead-navigation ul li a {
		color: #000;
		padding:0 8px;
		border-color: #444;
		border-left-style:solid;
		border-left-width:1px;
		border-right-style:solid;
		border-right-width:1px;
	}
	.beforhead-navigation ul li:last-child a {
		border-right-style:none;
	}
	.beforhead-navigation ul li:first-child a {
		border-left-style:none;
	}
	.head-navigation {
		background-color: #F00;
		background: linear-gradient(to top, #1F5028, #16541F);
		width: 100%;
		display: flex;
		justify-content: space-between;
		align-items: center;
		color: #B1F5D8;
	}
	.head-navigation-btn {
		padding: 10px;
		transition: background-color .2s ease;
		cursor: pointer;
	}
	.head-navigation-btn:hover {
		background-color: #FF0;
		transition: background-color .2s ease;
	}
	.search-box {
		display: inline-flex;
		padding: 0;
		border: solid 1px;
	}
	.search-box input {
		border: none;
		padding: 0;
		padding-left: 6px;
		margin: 0;
	}
	.search-box a {
		background-color: #CCC;
		color: #000;
		border: none;
		padding: 5px 15px;
		margin: 0;
		transition: background-color .2s ease;
	}
	.search-box a:hover {
		background-color: #BBB;
		color: #000;
		transition: background-color .2s ease;
		text-decoration: none;
	}
	.search-box a:focus {
		text-decoration: none;
	}
	.underhead {
		background-color: #FFF;
		background: linear-gradient(to top, #3CD155, #6DE882);
		padding: 15px;
		
	}
	.category {
		display: inline-block;
		width: 100%;
	}
	.category .category-head {
		cursor: pointer;
		color: #000;
		font-size: 16pt;
		display: block;
		margin: 0;
		padding: 6px;
		width: 100%;
		transition: background-color .2s ease;
	}
	.category .category-head:hover {
		background-color: rgba(255, 255, 255, .3);
		color: #000;
		transition: background-color .2s ease;
		text-decoration: none;
	}
	.category .category-head:focus {
		text-decoration: none;
	}
	.category .category-head .glyphicon {
		font-size: 12pt;
	}
	.page-items {
		background-color: #FFF;
		display: flex;
		flex-wrap: wrap;
		justify-content: space-around;
	}
	.page-items .item {
		border: solid 1px #EEE;
		overflow: hidden;
		width: 200px;
		margin: 15px 0;
	}
	.page-items .item .content {
		padding: 15px;
	}
	.page-items .item .content img {
		margin: 4px auto;
		vertical-align: middle;
		display: block;
		max-width: 100%;
	}
	
	
	.page-items .item .caption {
		position: relative;
		background-color: #EEE;
		padding: 5px 10px;
		transition: transform .2s ease;
	}
	.page-items .item .caption .caption-hidden {
		background-color: #EEE;
		position: absolute;
		left: 0;
		right: 0;
		padding: 0 10px;
		padding-bottom: 10px;
		transition: transform .2s ease;
	}
	.page-items .item:hover .caption {
		transition: transform .2s ease;
	}
	.page-items .item:hover .caption.line1 {
		transform: translateY(-15pt);
	}
	.page-items .item:hover .caption.line2 {
		transform: translateY(-30pt);
	}
	.page-items .item:hover .caption.line3 {
		transform: translateY(-45pt);
	}
</style>
<script>
	<?php
		$page = [];
		$page['items'] = core\Database::query('SELECT * FROM market_item;');
		echo 'var data = '.json_encode($page).';';
	?>
</script>
</head>
<body>
	<div class="container">
		<div class="beforhead-navigation">
			<ul>
				<li><a href="#">Домой</a></li>
				<li><a href="#">Поиск</a></li>
				<li><a href="#">Каталог</a></li>
			</ul>
		</div>
		<div class="head-navigation">
			<div class="head-navigation-main">
				<img src="img/PCM1794A-Q1.jpg"/><a href="#">Микруха</a>
				
			</div>
			<div class="head-navigation-btn">
				Корзина<br>
				10 товаров на 12999,99 руб
				<a href="">Открыть</a>
			</div>
		</div>
		<div class="underhead">
			<div class="search-box">
				<input type="text"/>
				<a href="#">Поиск</a>
			</div>
			<p>Поиск: категория резисторы, ключевые слова «»</p>
			<div class="category">
				<a class="category-head">Категории <span class="glyphicon glyphicon-chevron-down"></span></a>
				<ul class="category-body">
					<li><a href="#">Домой</a></li>
					<li><a href="#">Поиск</a></li>
					<li><a href="#">Каталог</a></li>
				</ul>
			</div>
		</div>
		<div class="page-items" id="items-container">
			<!--<div class="item">
				<div class="content">
					<img src="img/PCM1794A-Q1.jpg"/>
				</div>
				<div class="caption line3">
					dv-f44d<br/>
					Fsd: 123tp<br/>
					Trrrs: 1pt
					<div class="caption-hidden">
						Fsd: 123tp<br/>
						Fsd: 123tp<br/>
						Fsd: 123tp
					</div>
				</div>
			</div>-->
		</div>
		
	</div>
	
<script src="/js/jquery-3.1.0.js"></script>
<script src="/js/jquery.dom.js"></script>
<script src="/js/jquery.ui.js"></script>
<script src="/js/bootstrap.js"></script>

<script>
	var ctr = $("#items-container");
	$.post("http://localhost/market/items", "", function(data, status, jqXNR) {
		if (status=="success") {
			var items = JSON.parse(data);
			for(var i=0; i<items.length; i++) {
				var img = items[i].mainImg==null?"img/item-default.png":items[i].mainImg;
				var obj = {"class":"item",child:[
					{"class":"content", child:[
						{t:"img", attr:{src:img}}
					]},
					{"class":"caption line1", text:items[i].name, child:[
						{"class":"caption-hidden", text:"Fsd: 123tp"}
					]}
				]};
				$("#items-container").dom(obj);
			}
		}
		
	});
	/*for(var i=0; i<data.items.length; i++) {
		var img = data.items[i].mainImg==null?"img/item-default.png":data.items[i].mainImg;
		var obj = {"class":"item",child:[
			{"class":"content", child:[
				{t:"img", attr:{src:img}}
			]},
			{"class":"caption line1", text:data.items[i].name, child:[
				{"class":"caption-hidden", text:"Fsd: 123tp"}
			]}
		]};
		$("#items-container").dom(obj);
	}*/
	
	
</script>
</body>