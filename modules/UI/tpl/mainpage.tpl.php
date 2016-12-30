<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width">
<title>UI</title>
<link href="/css/bootstrap.css" rel="stylesheet">
<style>
	.r-panel {
		position: absolute;
		background-color: #F00;
		top: 0;
		bottom: 0;
		right: 0;
		width: 300px;
	}
</style>
</head>
<body>

<div class="container-fluid">
	<div id="canvas" class="item">
		<div draggable="true"><div>asfasg</div></div>
	</div>
	<div class="r-panel">
		<div class="">
			<div class="row">
				<div class="col-xs-4">
					<a onpress="addControl('btn');" class="btn btn-block btn-info">Кнопка</a>
				</div>
				<div class="col-xs-4">
					<a href="" class="btn btn-block btn-info">2</a>
				</div>
				<div class="col-xs-4">
					<a href="" class="btn btn-block btn-info">3</a>
				</div>
			</div>
		</div>
		<div class="">
			<h3>Выбранный елемент: Кнопка</h3>
			<div>x: <input type="number"></input></div>
			<div>y: <input type="number"></input></div>
		</div>
	</div>
</div>
	
<script src="/js/jquery-3.1.0.js"></script>
<script src="/js/jquery.dom.js"></script>
<script src="/js/jquery.ui.js"></script>
<script src="/js/jquery.autocomplete.js"></script>
<script src="/js/bootstrap.js"></script>
<script src="/js/jquery.client.js"></script>

<script>


$("#canvas").UI();
function addControl (type) {
	switch(type) {
		case "btn":
			var btn = Elements.Button();
			break;
	}
}
</script>
</body>