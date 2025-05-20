<?php
require "config/constants.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Конфигуратор ПК</title>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<script src="js/jquery2.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="main.js"></script>
		<link rel="stylesheet" type="text/css" href="style.css"/>
	</head>
<body>
<div class="wait overlay">
	<div class="loader"></div>
</div>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">	
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapse" aria-expanded="false">
					<span class="sr-only">navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a href="#" class="navbar-brand">Конфигуратор ПК</a>
			</div>
		<div class="collapse navbar-collapse" id="collapse">
			<ul class="nav navbar-nav">
				<li><a href="index.php"><span class="glyphicon glyphicon-home"></span> На главную</a></li>
			</ul>
		</div>
	</div>
	</div>
	<p><br/></p>
	<p><br/></p>
	<p><br/></p>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8" id="cart_msg">
				<!--Cart Message--> 
			</div>
			<div class="col-md-2"></div>
		</div>
		<div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<div class="panel panel-primary">
					<div class="panel-heading">Управление товаром в корзине</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-2 col-xs-2"><b>Действия</b></div>
							<div class="col-md-2 col-xs-2"><b>Изображение</b></div>
							<div class="col-md-2 col-xs-2"><b>Наименование</b></div>
							<div class="col-md-2 col-xs-2"><b>Количество</b></div>
							<div class="col-md-2 col-xs-2"><b>Цена</b></div>
							<div class="col-md-2 col-xs-2"><b>Сумма <?php echo CURRENCY; ?></b></div>
						</div>
						<div id="cart_checkout"></div>
						
						</div> 
					</div>
					<div class="panel-footer"></div>
				</div>
			</div>
			<div class="col-md-2"></div>
			
		</div>

<script>var CURRENCY = '<?php echo CURRENCY; ?>';</script>
</body>	
</html>
















		