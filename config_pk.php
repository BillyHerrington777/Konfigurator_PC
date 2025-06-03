<?php
require "config/constants.php";
include "db.php";
$vendors  = array ();

$product_query = "SELECT processor_id, product_title FROM processors ORDER BY product_title ASC";
$run_query = mysqli_query($con, $product_query);
if (mysqli_num_rows($run_query) > 0) {
    while ($row = mysqli_fetch_array($run_query)) {
        $vendors[] = $row; // Сохраняем массив с processor_id и product_title
    }
}


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
		<script type="text/javascript" src="js/xajax.js"></script>
		<script type="text/javascript">

			var xajaxRequestUri="out.php";
			var xajaxDebug=false;
			var xajaxStatusMessages=false;
			var xajaxWaitCursor=true;
			var xajaxDefinedGet=0;
			var xajaxDefinedPost=1;
			var xajaxLoaded=false;
			function xajax_getmark(){return xajax.call("getmark", arguments, 1);}
			function xajax_getmodels(){return xajax.call("getmodels", arguments, 1);}
			function xajax_getvideocards(){return xajax.call("getvideocards", arguments, 1);}
			function xajax_getcpucoolings(processor_id, body_id) {return xajax.call("getcpucoolings", [processor_id, body_id], 1);}
			function xajax_getrammemoryes(motherboard_id){return xajax.call("getrammemoryes",[parseInt(motherboard_id)], 1);}
			function xajax_getstorages(){return xajax.call("getstorages", arguments, 1);}
			function xajax_getpowerunits(processor_id, videocard_id){return xajax.call("getpowerunits",[processor_id, videocard_id] , 1);}
			//function xajax_list(){return xajax.call("list", arguments, 1);}
		</script>
		<script type="text/javascript">
			 function FormClick() {
			   $("#myDiv").html("Загрузка…");
				var n = document.getElementById("auto").options.selectedIndex;
				var auto = document.getElementById("auto").options[n].text;
				var n1 = document.getElementById("models").options.selectedIndex;
				var models = document.getElementById("models").options[n1].text;
				var n2 = document.getElementById("videocards").options.selectedIndex;
				var videocards = document.getElementById("videocards").options[n2].text;
				var n3 = document.getElementById("cpucoolings").options.selectedIndex;
				var cpucoolings = document.getElementById("cpucoolings").options[n3].text;
				var n4 = document.getElementById("rammemoryes").options.selectedIndex;
				var rammemoryes = document.getElementById("rammemoryes").options[n4].text;
				var n5 = document.getElementById("storages").options.selectedIndex;
				var storages = document.getElementById("storages").options[n5].text;
				var n6 = document.getElementById("powerunits").options.selectedIndex;
				var powerunits = document.getElementById("powerunits").options[n6].text;
				var proc = document.myForm.proc.value;

				 //alert (auto + ' ' + models + ' ' + proc);
				 $.post("out.php", {auto:auto,models:models,videocards:videocards,cpucoolings:cpucoolings,rammemoryes:rammemoryes,storages:storages,powerunits:powerunits,proc:proc}, function(data){$("#myDiv").html(data);document.getElementById('event').innerHTML = String.fromCharCode(8659); }) 
			}
		</script>
		<link rel="stylesheet" type="text/css" href="style.css">
		
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
			
		</div>
	</div>
</div>	
	<p><br/></p>
	<p><br/></p>
	<p><br/></p>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-1"></div>
			<!-------------left ajax select-------------->
			
			<div class="col-md-2 col-xs-12" >
			  
				<!--Here we get select components jquery Ajax Request-->
				<form method="POST" id="myForm" name="myForm" enctype="multipart/form-data" >
	
					<div class="form-group row">
						<label for="proc"class="col-sm-2 col-form-label" > Процессор: </label>
						
						<select size="1" name="proc" id="proc" onchange="xajax_getmark(this.value)" class="form-control" ><option value="0">Выберите процессор</option>
							<?php
								foreach($vendors as $vendor) { ?>
									<option value="<?= $vendor['processor_id'] ?>"><?php echo $vendor['product_title']; ?></option>
							<?php
								} 
							?>						
						</select>
					</div>
					
					<div class="form-group row">
						<label for="auto"class="col-sm-2 col-form-label"> Мат.плата: </label>
						<select name=auto id=auto onchange="xajax_getmodels(this.value)" class="form-control">
						<option value="0">Выберите материнскую плату</option>
						</select>
					</div>
					
					<div class="form-group row">
						<label for="models" class="col-sm-2 col-form-label" > Корпус: </label>
						<select name=models id=models onchange="xajax_getvideocards(this.value)" class="form-control">
						<option value="0">Выберите корпус</option>
						</select>
					  
					</div>
					<!------------------------dop------------------------------>
					<div class="form-group row">
    					<label for="videocards" class="col-sm-2 col-form-label">Видеокарта:</label>
    					<select name="videocards" id="videocards" onchange="xajax_getcpucoolings(document.getElementById('proc').value, document.getElementById('models').value)" class="form-control">
        				<option value="0">Выберите видеокарту</option>
    					</select>
						</div>
					<div class="form-group row">
						<label for="cpucoolings"class="col-sm-2 col-form-label"> Кулер: </label>
						<select name=cpucoolings id=cpucoolings onchange="xajax_getrammemoryes(document.getElementById('auto').value)" class="form-control">
						<option value="0">Выберите кулер</option>
						</select>
					</div>
					<div class="form-group row">
						<label for="rammemoryes"class="col-sm-2 col-form-label"> Опер.память: </label>
						<select name=rammemoryes id=rammemoryes onchange="xajax_getstorages(this.value)" class="form-control">
						<option value="0">Выберите операт.память</option>
						</select>
					</div>
					<div class="form-group row">
						<label for="storages"class="col-sm-2 col-form-label"> Накопитель: </label>
						<select name=storages id=storages onchange="xajax_getpowerunits(document.getElementById('proc').value, document.getElementById('videocards').value)" class="form-control">
						<option value="0">Выберите накопитель</option>
						</select>
					</div>
					<div class="form-group row">
						<label for="powerunits"class="col-sm-2 col-form-label"> Блок питания: </label>
						<select name=powerunits id=powerunits class="form-control">
						<option value="0">Выберите блок питания</option>
						</select>
					</div>
					<!------------------------------end dop--------------------------------->
					<div class="form-col">
					<input type="button" class="btn btn-primary mb-2" value="Показать" onClick="FormClick();"> <input type="reset" value="Очистить" class="btn btn-primary mb-2">
					</div>
				</form>
				
			</div>
			<!---------------right resultat-------------->
			<div class="col-md-8 col-xs-12">
				<div class="row">
					<div class="col-md-12 col-xs-12" id="product_msg">
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Комплектующие</div>
						<div id='myDiv'>
							<!--Here we get product jquery Ajax Request-->		
						</div>
					<div class="panel-footer">&copy; <?php echo date("Y"); ?></div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
</body>
</html>