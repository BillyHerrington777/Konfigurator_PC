// Запускаем код после полной загрузки DOM-дерева
$(document).ready(function(){
    // Вызываем функции при загрузке страницы
    cat();     // Загружаем категории
    brand();   // Загружаем бренды
    product(); // Загружаем продукты
    page();    // Загружаем пагинацию
    
    //----------------------------------------------------------
    // Функция для загрузки пагинации
    function page(){
        $.ajax({
            url     : "action.php",    // URL для запроса
            method  : "POST",          // Метод отправки
            data    : {page:1},        // Передаем параметр page=1
            success : function(data){   // При успешном ответе
                $("#pageno").html(data); // Вставляем HTML в элемент с id="pageno"
            }
        })
    }
    
    // Обработчик клика по элементу пагинации
    $("body").delegate("#page","click",function(){
        var pn = $(this).attr("page"); // Получаем номер страницы из атрибута
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем параметры для получения продуктов с определенной страницы
            data    : {getProduct:1, setPage:1, pageNumber:pn},
            success : function(data){
                $("#get_product").html(data); // Обновляем список продуктов
            }
        })
    })
    //---------------------------------------------------------
    
    // Функция загрузки категорий из базы данных
    function cat(){
        $.ajax({
            url     : "action.php",
            method  : "POST",
            data    : {category:1}, // Запрашиваем категории
            success : function(data){
                $("#get_category").html(data); // Вставляем категории в соответствующий элемент
            }
        })
    }
    
    // Функция загрузки брендов из базы данных
    function brand(){
        $.ajax({
            url     : "action.php",
            method  : "POST",
            data    : {brand:1}, // Запрашиваем бренды
            success : function(data){
                $("#get_brand").html(data); // Вставляем бренды в соответствующий элемент
            }
        })
    }
    //-----------------------------------------------------------
    
    // Функция загрузки продуктов из базы данных
    function product(){
        $.ajax({
            url     : "action.php",
            method  : "POST",
            data    : {getProduct:1}, // Запрашиваем продукты
            success : function(data){
                $("#get_product").html(data); // Вставляем продукты в соответствующий элемент
            }
        })
    }
    
    /*
        Обработчик клика по категории.
        При клике загружаем продукты выбранной категории.
    */
    $("body").delegate(".category","click",function(event){
        $("#get_product").html("<h3>Загрузка...</h3>"); // Показываем сообщение о загрузке
        event.preventDefault(); // Отменяем стандартное поведение
        var cid = $(this).attr('cid'); // Получаем ID категории
        
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем ID выбранной категории
            data    : {get_seleted_Category:1, cat_id:cid},
            success : function(data){
                $("#get_product").html(data); // Вставляем продукты категории
                // Если экран меньше 480px, прокручиваем к продуктам
                if($("body").width() < 480){
                    $("body").scrollTop(683);
                }
            }
        })
    })

    /*
        Обработчик клика по бренду.
        При клике загружаем продукты выбранного бренда.
    */
    $("body").delegate(".selectBrand","click",function(event){
        event.preventDefault(); // Отменяем стандартное поведение
        $("#get_product").html("<h3>Загрузка...</h3>"); // Показываем сообщение о загрузке
        var bid = $(this).attr('bid'); // Получаем ID бренда
        
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем ID выбранного бренда
            data    : {selectBrand:1, brand_id:bid},
            success : function(data){
                $("#get_product").html(data); // Вставляем продукты бренда
                // Если экран меньше 480px, прокручиваем к продуктам
                if($("body").width() < 480){
                    $("body").scrollTop(683);
                }
            }
        })
    })
    //--------------------------------------
    
    // Обработчик изменения селектора цены
    $("body").delegate("#price_select","change",function(event){
        var val_select = $(this).val(); // Получаем выбранное значение
        var val_id = $('#barnds_id').val(); // Получаем ID бренда
        
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем параметры для фильтрации по цене и бренду
            data    : {selectBrand:1, brand_id:val_id, select_id:val_select},
            success : function(data){
                $("#get_product").html(data); // Обновляем список продуктов
                // Если экран меньше 480px, прокручиваем к продуктам
                if($("body").width() < 480){
                    $("body").scrollTop(683);
                }
            }
        })        
    });
    //--------------------------------------
    
    /*
        Обработчик поиска продуктов.
        При клике на кнопку поиска отправляем запрос с ключевым словом.
    */
    $("#search_btn").click(function(){
        $("#get_product").html("<h3>Загрузка...</h3>"); // Показываем сообщение о загрузке
        var keyword = $("#search").val(); // Получаем поисковый запрос
        
        if(keyword != ""){ // Если запрос не пустой
            $.ajax({
                url     : "action.php",
                method  : "POST",
                // Передаем параметры поиска
                data    : {search:1, keyword:keyword},
                success : function(data){ 
                    $("#get_product").html(data); // Вставляем результаты поиска
                    // Если экран меньше 480px, прокручиваем к продуктам
                    if($("body").width() < 480){
                        $("body").scrollTop(683);
                    }
                }
            })
        }
    })
    //end

    // Добавление продукта в корзину
    $("body").delegate("#product","click",function(event){
        var pid = $(this).attr("pid"); // Получаем ID продукта
        event.preventDefault(); // Отменяем стандартное поведение
        $(".overlay").show(); // Показываем overlay (затемнение)
        
        $.ajax({
            url : "action.php",
            method : "POST",
            // Передаем параметры для добавления в корзину
            data : {addToCart:1, proId:pid},
            success : function(data){
                count_item(); // Обновляем счетчик товаров
                getCartItem(); // Обновляем содержимое корзины
                $('#product_msg').html(data); // Показываем сообщение
                $('.overlay').hide(); // Скрываем overlay
            }
        })
    })
    // Конец добавления в корзину
    
    // Функция подсчета товаров в корзине
    count_item();
    function count_item(){
        $.ajax({
            url : "action.php",
            method : "POST",
            data : {count_item:1}, // Запрос на подсчет товаров
            success : function(data){
                $(".badge").html(data); // Обновляем счетчик
            }
        })
    }
    // Конец функции подсчета

    // Загрузка содержимого корзины в выпадающее меню
    getCartItem();
    function getCartItem(){
        $.ajax({
            url : "action.php",
            method : "POST",
            // Запрос на получение содержимого корзины
            data : {Common:1, getCartItem:1},
            success : function(data){
                $("#cart_product").html(data); // Вставляем содержимое корзины
            }
        })
    }

    /*
        Обработчик изменения количества товара.
        При изменении количества пересчитываем сумму.
    */
    $("body").delegate(".qty","keyup",function(event){
        event.preventDefault(); // Отменяем стандартное поведение
        var row = $(this).parent().parent(); // Получаем строку товара
        var price = row.find('.price').val(); // Получаем цену
        var qty = row.find('.qty').val(); // Получаем количество
        
        // Проверяем, что введено число
        if (isNaN(qty)) {
            qty = 1;
        };
        // Проверяем, что количество больше 0
        if (qty < 1) {
            qty = 1;
        };
        
        var total = price * qty; // Считаем сумму
        row.find('.total').val(total); // Устанавливаем сумму
        
        // Пересчитываем общую сумму
        var net_total=0;
        $('.total').each(function(){
            net_total += ($(this).val()-0);
        })
        $('.net_total').html("Итого : "+ net_total + " " + CURRENCY);
    })
    // Конец обработчика изменения количества

    /*
        Обработчик удаления товара из корзины.
        При клике на кнопку удаления отправляем запрос на удаление.
    */
    $("body").delegate(".remove","click",function(event){
        var remove = $(this).parent().parent().parent(); // Получаем строку товара
        var remove_id = remove.find(".remove").attr("remove_id"); // Получаем ID товара
        
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем параметры для удаления
            data    : {removeItemFromCart:1, rid:remove_id},
            success : function(data){
                $("#cart_msg").html(data); // Показываем сообщение
                checkOutDetails(); // Обновляем детали заказа
            }
        })
    })
    
    /*
        Обработчик обновления количества товара в корзине.
        При клике на кнопку обновления отправляем запрос с новым количеством.
    */
    $("body").delegate(".update","click",function(event){
        var update = $(this).parent().parent().parent(); // Получаем строку товара
        var update_id = update.find(".update").attr("update_id"); // Получаем ID товара
        var qty = update.find(".qty").val(); // Получаем новое количество
        
        $.ajax({
            url     : "action.php",
            method  : "POST",
            // Передаем параметры для обновления
            data    : {updateCartItem:1, update_id:update_id, qty:qty},
            success : function(data){
                $("#cart_msg").html(data); // Показываем сообщение
                checkOutDetails(); // Обновляем детали заказа
            }
        })
    })
    
    // Инициализация функций при загрузке
    checkOutDetails();
    net_total();
    
    /*
        Функция для получения деталей заказа.
        Используется для отображения товаров в корзине на странице оформления заказа.
    */
    function checkOutDetails(){
        $('.overlay').show(); // Показываем overlay
        $.ajax({
            url : "action.php",
            method : "POST",
            // Запрос на получение деталей заказа
            data : {Common:1, checkOutDetails:1},
            success : function(data){
                $('.overlay').hide(); // Скрываем overlay
                $("#cart_checkout").html(data); // Вставляем детали заказа
                net_total(); // Пересчитываем сумму
            }
        })
    }
    
    /*
        Функция пересчета общей суммы заказа.
        Пересчитывает сумму на основе количества и цены каждого товара.
    */
    function net_total(){
        var net_total = 0;
        // Пересчитываем сумму для каждого товара
        $('.qty').each(function(){
            var row = $(this).parent().parent(); // Получаем строку товара
            var price  = row.find('.price').val(); // Получаем цену
            var total = price * $(this).val()-0; // Считаем сумму
            row.find('.total').val(total); // Устанавливаем сумму
        })
        // Считаем общую сумму
        $('.total').each(function(){
            net_total += ($(this).val()-0);
        })
        // Отображаем общую сумму
        $('.net_total').html("Итого : "+ net_total + " " + CURRENCY);
    }
})