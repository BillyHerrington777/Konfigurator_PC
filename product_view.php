<?php
require "config/constants.php";
include "db.php";
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
		<link rel="stylesheet" type="text/css" href="style.css">
		<style></style>
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
				<a href="index.php" class="navbar-brand">Конфигуратор ПК</a>
			</div>
		<div class="collapse navbar-collapse" id="collapse">
			<ul class="nav navbar-nav">
				<li><a href="index.php"><span class="glyphicon glyphicon-home"></span> На главную</a></li>
			</ul>
			<!--------------------not search------------------>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-shopping-cart"></span> Корзина <span class="badge" >0</span></a>
					<div class="dropdown-menu" style="width:400px;">
						<div class="panel panel-success">
							<div class="panel-heading">
								<div class="row">
									<div class="col-md-3">№</div>
									<div class="col-md-3">Изображение</div>
									<div class="col-md-3">Наименование</div>
									<div class="col-md-3">Цена <?php echo " ".CURRENCY; ?></div>
								</div>
							</div>
							<div class="panel-body">
								<div id="cart_product">
								</div>
							</div>
							<div class="panel-footer"></div>
						</div>
					</div>
				</li>
				
			</ul>
		</div>
	</div>
</div>	
	<p><br/></p>
	<p><br/></p>
	<p><br/></p>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1"></div>
			
			<div class="col-md-8 col-xs-12">
				<div class="row">
					<div class="col-md-12 col-xs-12" id="product_msg">
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Информация о товаре</div>
					<div class="panel-body">
						<?php 
							$pid = $_GET["pid"]; 
							$product_query = "SELECT br.*, pr.* FROM products  as pr LEFT JOIN brands as br ON pr.product_brand = br.brand_id WHERE product_id=".$pid;
							$run_query = mysqli_query($con,$product_query);
							if(mysqli_num_rows($run_query) > 0){
								while($row = mysqli_fetch_array($run_query)){
									$pro_id    = $row['product_id'];
									$pro_cat   = $row['product_cat'];
									$pro_brand = $row['product_brand'];
									$pro_title = $row['product_title'];
									$product_desc = $row['product_desc'];
									$pro_price = $row['product_price'];
									$pro_image = $row['product_image'];
									$brands_title = $row['brand_title'];
									$product_qty = $row['product_qty'];
									echo "
										<div class='col-md-4'>
													<div class='panel panel-info'>
														<div class='panel-heading'>$pro_title</div>
														<div class='panel-body'>
															<img src='product_images/$pro_image' style='width:220px; height:250px;'/>
														</div>
														
														<div class='panel-heading'>". $pro_price." ".CURRENCY."
															<button pid='$pro_id' style='float:right;' id='product' class='btn btn-danger btn-xs'>В корзину</button>
															
														</div>
													</div>
												</div>	
												<div class='panel-right'><b>Описание:</b><br/> $product_desc</div>
												<div class='panel-right'><b>Тип сборки:</b> $brands_title</div>
												<div class='panel-right'><b>Количество в магазине:</b> $product_qty</div>
									";
							} } ?>
						
					</div>
					<div class="panel-footer">&copy; <?php echo date("Y"); ?></div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
</body>
</html>