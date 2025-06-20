<?php
// Начинаем сессию для работы с сессионными переменными
session_start();

// Получаем IP-адрес пользователя
$ip_add = getenv("REMOTE_ADDR");

// Подключаемся к базе данных
include "db.php";

// Обработка запроса для получения категорий
if(isset($_POST["category"])){
    // SQL-запрос для получения всех категорий из базы данных
    $category_query = "SELECT * FROM categories";
    $run_query = mysqli_query($con,$category_query) or die(mysqli_error($con));
    
    // Начинаем вывод HTML-разметки для категорий
    echo "
        <div class='nav nav-pills nav-stacked'>
            <li class='active'><a href='#'><h4>Категории</h4></a></li>
    ";
    
    // Если категории найдены, выводим их
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)){
            $cid = $row["cat_id"];          // ID категории
            $cat_name = $row["cat_title"];  // Название категории
            
            // Выводим каждую категорию как ссылку
            echo "
                    <li><a href='#' class='category' cid='$cid'>$cat_name</a></li>
            ";
        }
        echo "</div>"; // Закрываем блок категорий
    }
}

// Обработка запроса для получения типов
if(isset($_POST["brand"])){
    $brand_query = "SELECT * FROM brands";
    $run_query = mysqli_query($con,$brand_query);
    
    // Начинаем вывод HTML-разметки для типов
    echo "
        <div class='nav nav-pills nav-stacked'>
            <li class='active'><a href='#'><h4>Вид сборки</h4></a></li>
    ";
    
    // Если типы найдены, выводим их
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)){
            $bid = $row["brand_id"];        // ID типа
            $brand_name = $row["brand_title"]; // Название типа
            
            // Выводим каждый тип как ссылку
            echo "
                    <li><a href='#' class='selectBrand' bid='$bid'>$brand_name</a></li>
            ";
        }
        echo "</div>"; // Закрываем блок типов
    }
}

// Обработка запроса для пагинации (разбивки на страницы)
if(isset($_POST["page"])){
    $sql = "SELECT * FROM products";
    $run_query = mysqli_query($con,$sql);
    $count = mysqli_num_rows($run_query); // Общее количество товаров
    $pageno = ceil($count/20); // Вычисляем количество страниц (по 20 товаров на страницу)
    
    // Выводим номера страниц
    echo "<b>Страницы: </b>";
    for($i=1;$i<=$pageno;$i++){
        echo "
            <b><a href='#' page='$i' id='page'>$i</a></b>
        ";
    }
}

// Обработка запроса для получения товаров
if(isset($_POST["getProduct"])){
    $limit = 20; // Количество товаров на странице
    
    // Определяем, с какой позиции начинать выборку (для пагинации)
    if(isset($_POST["setPage"])){
        $pageno = $_POST["pageNumber"];
        $start = ($pageno * $limit) - $limit;
    }else{
        $start = 0;
    }
    
    // SQL-запрос для получения товаров с ограничением
    $product_query = "SELECT * FROM products LIMIT $start,$limit";
    $run_query = mysqli_query($con,$product_query);
    
    // Если товары найдены, выводим их
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)){
            $pro_id    = $row['product_id'];     // ID товара
            $pro_cat   = $row['product_cat'];    // Категория товара
            $pro_brand = $row['product_brand'];  // тип товара
            $pro_title = $row['product_title'];  // Название товара
            $pro_price = $row['product_price'];  // Цена товара
            $pro_image = $row['product_image'];   // Изображение товара
            
            // Выводим карточку товара
            echo "
                <div class='col-md-4'>
                    <div class='panel panel-info'>
                        <div class='panel-heading'>$pro_title</div>
                        <div class='panel-body'>
                            <img src='product_images/$pro_image' style='width:220px; height:250px;'/>
                        </div>
                        <div class='panel-heading'>". $pro_price." ".CURRENCY."
                            <button pid='$pro_id' style='float:right;' id='product' class='btn btn-danger btn-xs'>В корзину</button>
                            <a style='float:right;' href='product_view.php?pid=$pro_id' class='btn btn-info btn-xs'>Просмотр&nbsp;&nbsp;</a>
                        </div>
                    </div>
                </div>    
            ";
        }
    }
}

// Обработка запросов для фильтрации товаров (по категории, типу или поиску)
if(isset($_POST["get_seleted_Category"]) || isset($_POST["selectBrand"]) || isset($_POST["search"])){
    // Определяем тип фильтрации и формируем соответствующий SQL-запрос
    if(isset($_POST["get_seleted_Category"])){
        // Фильтрация по категории
        $id = $_POST["cat_id"];
        $sql = "SELECT * FROM products WHERE product_cat = '$id'";
    }else if(isset($_POST["selectBrand"])){
        // Фильтрация по типу с возможной дополнительной фильтрацией по цене
        $id = $_POST["brand_id"];
        if (isset($_POST["select_id"])) {
            switch($_POST["select_id"]){
                case 1: 
                    $sql = "SELECT * FROM products WHERE product_brand = '$id' AND product_price <= 30000";
                    break;
                case 2: 
                    $sql = "SELECT * FROM products WHERE product_brand = '$id' AND product_price BETWEEN 30000 AND 60000";
                    break;
                case 3: 
                    $sql = "SELECT * FROM products WHERE product_brand = '$id' AND product_price >= 60000";
                    break;
            }
        } else {
            $sql = "SELECT * FROM products WHERE product_brand = '$id'";
        }
        
        // Выводим выпадающий список для выбора ценового диапазона
        echo "<div class='col-md-12'>
                <label for='price_select'>Ценовой сегмент:</label>
                <select name='price' id='price_select'>
                  <option value=''>Выберите ценовой сегмент</option>
                  <option value='1'>бюджетный(до 30 000 руб.)</option>
                  <option value='2'>средний(от 30 0000 до 60 000 руб.)</option>
                  <option value='3'>высокий(от 60 000 руб.)</option>
                </select>
                <input type ='hidden' id='barnds_id' value='$id'/>
              </div>";
    }else {
        // Поиск товаров по ключевым словам
        $keyword = $_POST["keyword"];
        $sql = "SELECT * FROM products WHERE product_keywords LIKE '%$keyword%'";
    }
    
    // Выполняем SQL-запрос и выводим результаты
    $run_query = mysqli_query($con,$sql);
    if(mysqli_num_rows($run_query) > 0){
        while($row=mysqli_fetch_array($run_query)){
            $pro_id    = $row['product_id'];
            $pro_cat   = $row['product_cat'];
            $pro_brand = $row['product_brand'];
            $pro_title = $row['product_title'];
            $pro_price = $row['product_price'];
            $pro_image = $row['product_image'];
            
            // Выводим карточку товара
            echo "            
                <div class='col-md-4'>
                    <div class='panel panel-info'>
                        <div class='panel-heading'>$pro_title</div>
                        <div class='panel-body'>
                            <img src='product_images/$pro_image' style='width:220px; height:250px;'/>
                        </div>
                        <div class='panel-heading'>$pro_price Руб.
                            <button pid='$pro_id' style='float:right;' id='product' class='btn btn-danger btn-xs'>В корзину</button>
                            <a style='float:right;' href='product_view.php?pid=$pro_id' class='btn btn-info btn-xs'>Просмотр&nbsp;&nbsp;</a>
                        </div>
                    </div>
                </div>    
            ";
        }
    } else {
        // Если товары не найдены, выводим сообщение
        echo "<div class='col-md-12'><b><font color='red'>К сожалению товар не найден.</font></b></div>";
    }
}

// Обработка добавления товара в корзину
if(isset($_POST["addToCart"])){
    $p_id = $_POST["proId"]; // ID товара
    
    // Проверяем, не добавлен ли уже этот товар в корзину
    $sql = "SELECT id FROM cart WHERE ip_add = '$ip_add' AND p_id = '$p_id' AND user_id = -1";
    $query = mysqli_query($con,$sql);
    if (mysqli_num_rows($query) > 0) {
        echo "
            <div class='alert alert-warning'>
                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                <b>Товар уже добавлен в корзину. Продолжайте покупки!</b>
            </div>";
        exit();
    }
    
    // Добавляем товар в корзину
    $sql = "INSERT INTO `cart`
            (`p_id`, `ip_add`, `user_id`, `qty`) 
            VALUES ('$p_id','$ip_add','-1','1')";
    if (mysqli_query($con,$sql)) {
        echo "
            <div class='alert alert-success'>
                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                <b>Товар был добавлен в корзину!</b>
            </div>
        ";
        exit();
    }    
}

// Подсчет количества товаров в корзине
if (isset($_POST["count_item"])) {
    // SQL-запрос для подсчета товаров в корзине по IP-адресу
    $sql = "SELECT COUNT(*) AS count_item FROM cart WHERE ip_add = '$ip_add' AND user_id < 0";
    
    $query = mysqli_query($con,$sql);
    $row = mysqli_fetch_array($query);
    echo $row["count_item"]; // Выводим количество товаров
    exit();
}

// Получение товаров из корзины для отображения в выпадающем меню
if (isset($_POST["Common"])) {
    // SQL-запрос для получения товаров в корзине
    $sql = "SELECT a.product_id,a.product_title,a.product_price,a.product_image,b.id,b.qty FROM products a,cart b WHERE a.product_id=b.p_id AND b.ip_add='$ip_add' AND b.user_id < 0";
    
    $query = mysqli_query($con,$sql);
    
    // Обработка запроса для отображения товаров в выпадающем меню корзины
    if (isset($_POST["getCartItem"])) {
        if (mysqli_num_rows($query) > 0) {
            $n=0;
            while ($row=mysqli_fetch_array($query)) {
                $n++;
                $product_id = $row["product_id"];
                $product_title = $row["product_title"];
                $product_price = $row["product_price"];
                $product_image = $row["product_image"];
                $cart_item_id = $row["id"];
                $qty = $row["qty"];
                
                // Выводим информацию о товаре
                echo '
                    <div class="row">
                        <div class="col-md-3">'.$n.'</div>
                        <div class="col-md-3"><img class="img-responsive" src="product_images/'.$product_image.'" /></div>
                        <div class="col-md-3">'.$product_title.'</div>
                        <div class="col-md-3">'.$product_price.''.CURRENCY.'</div>
                    </div>';
            }
            // Кнопка для перехода к редактированию корзины
            ?>
                <a style="float:right;" href="cart.php" class="btn btn-warning">Редактировать&nbsp;&nbsp;<span class="glyphicon glyphicon-edit"></span></a>
            <?php
            exit();
        }
    }
    
    // Обработка запроса для отображения деталей корзины при оформлении заказа
    if (isset($_POST["checkOutDetails"])) {
        if (mysqli_num_rows($query) > 0) {
            $n=0;
            while ($row=mysqli_fetch_array($query)) {
                $n++;
                $product_id = $row["product_id"];
                $product_title = $row["product_title"];
                $product_price = $row["product_price"];
                $product_image = $row["product_image"];
                $cart_item_id = $row["id"];
                $qty = $row["qty"];

                // Выводим детали каждого товара с возможностью удаления и изменения количества
                echo 
                    '<div class="row">
                            <div class="col-md-2">
                                <div class="btn-group">
                                    <a href="#" remove_id="'.$product_id.'" class="btn btn-danger remove"><span class="glyphicon glyphicon-trash"></span></a>
                                    <a href="#" update_id="'.$product_id.'" class="btn btn-primary update"><span class="glyphicon glyphicon-ok-sign"></span></a>
                                </div>
                            </div>
                            <input type="hidden" name="product_id[]" value="'.$product_id.'"/>
                            <input type="hidden" name="" value="'.$cart_item_id.'"/>
                            <div class="col-md-2"><img class="img-responsive" src="product_images/'.$product_image.'"></div>
                            <div class="col-md-2">'.$product_title.'</div>
                            <div class="col-md-2"><input type="text" class="form-control qty" value="'.$qty.'" ></div>
                            <div class="col-md-2"><input type="text" class="form-control price" value="'.$product_price.'" readonly="readonly"></div>
                            <div class="col-md-2"><input type="text" class="form-control total" value="'.$product_price.'" readonly="readonly"></div>
                        </div>';
            }
            
            // Выводим итоговую сумму
            echo '<div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <b class="net_total" style="font-size:20px;"> </b>
                </div>';
        }
    }
}

// Удаление товара из корзины
if (isset($_POST["removeItemFromCart"])) {
    $remove_id = $_POST["rid"]; // ID товара для удаления
    $sql = "DELETE FROM cart WHERE p_id = '$remove_id' AND ip_add = '$ip_add'";
    if(mysqli_query($con,$sql)){
        echo "<div class='alert alert-danger'>
                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                <b>Товар удален из корзины.</b>
            </div>";
        exit();
    }
}

// Обновление количества товара в корзине
if (isset($_POST["updateCartItem"])) {
    $update_id = $_POST["update_id"]; // ID товара
    $qty = $_POST["qty"];             // Новое количество
    $sql = "UPDATE cart SET qty='$qty' WHERE p_id = '$update_id' AND ip_add = '$ip_add'";
    
    if(mysqli_query($con,$sql)){
        echo "<div class='alert alert-info'>
                <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
                <b>Количество товара обновлено.</b>
            </div>";
        exit();
    }
}
?>