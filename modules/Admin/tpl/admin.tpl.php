<?php 
	$admin = [];
	$admin['modules'] = core\Database::query('SELECT * FROM modules;');
	$admin['pages'] = core\Database::query('SELECT * FROM pages join modules on pages.moduleID = modules.id;');
?><!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width">
<title>Панель Администрирования</title>
<link href="/css/bootstrap.min.css" rel="stylesheet">
<style>
.sidebar {
	padding:0;
	padding-top:15px;
	position:fixed;
	top:0;
	background-color:#DDD;
	bottom:0;
	overflow-y:auto;
}
.sidebar ul li {
	width:100%;
	margin:0;
}
.sidebar ul li a {
	margin:0;
}


.sidebar-two {
	padding:0;
	padding-top:15px;
	position:fixed;
	top:0;
	background-color:#EEE;
	bottom:0;
	overflow-y:auto;
}
.btn.btn-sidebar {
	text-align:left;
	border-radius:0;
	
}
.btn.btn-sidebar:hover{
	background-color: #FFF;
	box-shadow:none;
}

.autocomplete-suggestions {
	overflow-y: auto;
	padding: 5px 0;
	margin: 2px 0 0;
	font-size: 14px;
	text-align: left;
	list-style: none;
	background-color: #fff;
	-webkit-background-clip: padding-box;
		background-clip: padding-box;
	border: 1px solid #ccc;
	border: 1px solid rgba(0, 0, 0, .15);
	border-radius: 4px;
	-webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
		box-shadow: 0 6px 12px rgba(0, 0, 0, .175);
	top: auto;
	height: auto;
	margin-bottom: 2px;
}

.autocomplete-suggestion {
	display: block;
	padding: 3px 10px;
	clear: both;
	font-weight: normal;
	line-height: 1.42857143;
	color: #333;
	white-space: nowrap;
	cursor: pointer;
}
.autocomplete-suggestion:hover,
.autocomplete-suggestion:focus {
	color: #262626;
	text-decoration: none;
	background-color: #f5f5f5;
}


.input-hiden {
	display: inline-block;
	width:initial;
	border-color:#FFF;
	border-color:rgba(0,0,0,0);
	box-shadow:none;
}
.input-hiden:invalid {
	border-color:#555;
	border-color:rgba(0,0,0,255);
	background-color:#FFFFCC;
	box-shadow:none;
}


.btn-spoiler {
    border: 1px solid #ccc;
	border-radius:0;
	padding:0 7px;
}
.btn-spoiler::after {
	display:inline-block;
    content: ">";
	transform: rotate(0);
}
.btn-spoiler[aria-expanded="true"]::after {
	display:inline-block;
    content: ">";
	transform: rotate(90deg);
}

.tab-modules .item {
	padding: 10px 15px;
	cursor: pointer;
}
.tab-modules .item:hover{
	background-color: #FFF;
	box-shadow:none;
}
.tab-modules .item .item-first,
.tab-modules .item .item-second {
	margin: 0;
}
.tab-modules .item .item-first{
	font-size: 16pt;
}
.tab-modules .item .item-second {
	font-size: 10pt;
}

</style>
</head>
<body>

	<!--<div>
		<form action="/admin/installModule">
			<input name="name"></input>
			<button type="submin">Установить</button>
		</form>
	</div>
	<div>
		<form action="/admin/registratePage">
			<input name="file" placeholder="file"></input>
			<input name="url" placeholder="url"></input>
			<input name="equal" type="checkbox">equal</input>
			<button type="submin">Установить</button>
		</form>
	</div>-->
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-1 sidebar">
				<ul class="nav nav-tabs nav-sidebar" role="tablist">
					<li class="active"><a class="btn btn-sidebar" href="#tab_modules" role="tab" data-toggle="tab">Модули</a></li>
					<li><a class="btn btn-sidebar" href="#tab_pages" role="tab" data-toggle="tab">Страницы</a></li>
				</ul>
			</div>
			<div class="col-xs-push-1 col-xs-2 sidebar-two tab-content">
				<div id="tab_modules" class="tab-pane fade in active tab-modules">
					<ul id="sidebar_module_list" class="nav nav-sidebar"></ul>
				</div>
				<div id="tab_pages" class="tab-pane fade">
					<ul class="nav nav-sidebar">
						<li><a class="btn btn-sidebar" onclick="panel.show('gr_Dsmen');">Дежурства</a></li>
						<li><a class="btn btn-sidebar" onclick="panel.show('gr_Lsostav');">Колличество личного состава</a></li>
						<li><a class="btn btn-sidebar" onclick="panel.show('gr_Karauls');">Караулы</a></li>
						<li><a class="btn btn-sidebar" onclick="panel.show('gr_DPK');">ДПК</a></li>
					</ul>
				</div>
			</div>
			<div class="col-xs-push-3 col-xs-9" id="content">
				<h2>Страницы</h2>
				<div></div>
			</div>
		</div>
	</div>
<script src="/js/jquery-3.1.0.js"></script>
<script src="/js/jquery.dom.js"></script>
<script src="/js/bootstrap.js"></script>
<script src="/js/jquery.client.js"></script>
<script>
<?php 
	//var_dump($admin['pages']);
	foreach ($admin['pages'] as $key=>$value) {
		//echo '<div>'.$value->url.':'.$value->equal.':'.$value->listener.' '.'<a href="/admin/unregistratePage?url='.$value->url.'&equal='.$value->equal.'" class="btn btn-danger">Удалить</a></div>';
	}
	echo 'var moduleList = [';
	foreach ($admin['modules'] as $key=>$value) {
		$module = core\ModuleManager::getModule($value->name);
		echo '{"class":"'.$value->name.'", "name":"'.$module->name.'", version:"'.$module->version.'", description:"'.$module->description.'"},';
	}
	echo '];';
?>
	console.log(moduleList);
	$("#sidebar_module_list").html("");
	for(var i=0; i<moduleList.length; i++) {
		
		var obj = {t:"li", "class":"item", onClick:function (e) {
				var that = this;
				$.post("/admin/module", moduleList[$(this).attr("data-value")].class, function (data, textStatus, jqXHR) {
					$("#content").html(data);
				});
				console.log( moduleList[$(this).attr("data-value")].name );
			}, attr:{"data-value":i}, child:[
			{t:"p", "class":"item-first", text:moduleList[i].name},
			{t:"p", "class":"item-second", text:"Версия: "+moduleList[i].version},
			{t:"p", "class":"item-second", text:"Описание: "+moduleList[i].description}
		]};
		$("#sidebar_module_list").dom(obj);
		
	}
</script>
</body>