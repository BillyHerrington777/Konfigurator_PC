<?php
require_once "config/constants.php";
include "db.php";


$xajaxargs = @$_POST['xajaxargs'] ;
$xajax = @$_POST['xajax'] ;

global $out__;

error_reporting(E_ALL & ~E_WARNING);


$marka_mb = $_POST['auto'] ?? '';
$models_bd = $_POST['models'] ?? '';
$videocards_vk = $_POST['videocards'] ?? '';
$cpucoolings_cu = $_POST['cpucoolings'] ?? '';
$rammemoryes_rm = $_POST['rammemoryes'] ?? '';
$storages_st = $_POST['storages'] ?? '';
$powerunits_pu = $_POST['powerunits'] ?? '';
$proc = $_POST['proc'] ?? '';





function getCompany($number) {
    // Предопределённые значения для каждого числа
    $values = [
        0 => "HyperPC",
        1 => "KNS",
        2 => "Xcom",
        3 => "Регард",
        4 => "Ситилинк",
    ];

    // Проверяем, существует ли ключ в массиве
    if (array_key_exists($number, $values)) {
        return $values[$number];
    } else {
        return ""; // Возвращаем ошибку, если число вне диапазона
    }
}





function getPriceFromURLHyper($url) {
    // Проверяем, является ли URL пустым
    if (empty($url)) {
        return 0; // Возвращаем цену 0, если URL пустой
    }

    // Инициализация cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключить проверку SSL, если необходимо
    $html = curl_exec($ch);
    curl_close($ch);

    // Поиск JSON-кода внутри тега <script>
    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

    // Проверяем, найден ли JSON
    if (isset($matches[1])) {
        $jsonData = $matches[1];
        // Декодируем JSON в PHP-массив
        $data = json_decode($jsonData, true);

        // Извлекаем цену
        if (isset($data['offers'][0]['price'])) {
            return $data['offers'][0]['price']; // Возвращаем цену
        } else {
            return "Цена не найдена.";
        }
    } else {
        return "JSON-код не найден на странице.";
    }
}






function getPriceKNS($url) {
    // Проверяем, является ли URL пустым
    if (empty($url)) {
        return 0; // Возвращаем цену 0, если URL пустой
    }

    // Получаем HTML-код страницы
    $html = file_get_contents($url);

    if ($html === false) {
        return 0; // Возвращаем 0, если страницу не удалось загрузить
    }

    // Создаем DOMDocument и загружаем HTML
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Отключаем ошибки парсинга
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Создаем XPath для поиска
    $xpath = new DOMXPath($dom);

    // Ищем элемент с meta-тегом, содержащим цену
    $priceMeta = $xpath->query("//div[@itemprop='offers']//meta[@itemprop='price']");

    if ($priceMeta->length > 0) {
        // Извлекаем цену из атрибута 'content'
        $price = $priceMeta->item(0)->attributes->getNamedItem('content')->nodeValue;
        return $price; // Возвращаем только значение цены
    } else {
        return 0; // Возвращаем 0, если цена не найдена
    }
}



function getPriceXcom($url) {

    if (empty($url)) {
        return 0;
    }
    // Инициализация cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    // Проверяем успешность загрузки
    if (!$html) {
        return "Не удалось загрузить страницу!";
    }

    // Убираем лишние пробелы и ненужные символы для ускорения обработки
    $html = preg_replace('/\s+/', ' ', $html);

    // Загружаем HTML в DOMDocument
    libxml_use_internal_errors(true); // Отключаем ошибки для чистой обработки
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    libxml_clear_errors();

    // XPath для быстрого поиска нужного элемента
    $xpath = new DOMXPath($dom);

    // Поиск тега <meta itemprop="price">
    $priceElement = $xpath->query('//meta[@itemprop="price"]')->item(0);

    return $priceElement instanceof DOMElement ? $priceElement->getAttribute('content') : "Цена не найдена!";
}





function parsePriceRegardCit($url) {
    // Проверяем, если URL пустой, возвращаем 0
    if (empty($url)) {
        return 0;
    }

    // Инициализация cURL
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
    ]);

    // Выполнение запроса и получение HTML-кода страницы
    $html = curl_exec($ch);
    curl_close($ch);

    // Проверяем успешность загрузки
    if (!$html) {
        return "Не удалось загрузить страницу!";
    }

    // Загрузка HTML-кода в DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Поиск JSON-данных в HTML-коде
    $scripts = $dom->getElementsByTagName('script');

    foreach ($scripts as $script) {
        if ($script->getAttribute('type') === 'application/ld+json') {
            $jsonData = json_decode($script->nodeValue, true);
            if (isset($jsonData['offers']) && isset($jsonData['offers']['price'])) {
                return htmlspecialchars($jsonData['offers']['price']);
            }
        }
    }

    return 0;
}












if($marka_mb != "" && $models_bd != "" && $videocards_vk != "" && $cpucoolings_cu != "" && $rammemoryes_rm != "" && $storages_st != "" && $powerunits_pu != ""){
    

if ($proc == 0) {echo "<b><center> Ошибка, не полностью выбраны данные для подбора,<br> вернитесь назад и укажите все данные полностью </b>";exit();}

$product_query = "
SELECT 
    pr.product_title as prpt, pr.product_price as prpr, pr.product_image as prpi, pr.product_desc as prpd, pr.hyper_url as prhy, pr.kns_url as prkns, pr.xcom_url as prxcom, pr.regard_url as prreg, pr.citi_url as prciti,
    mb.product_title as mbpt, mb.product_price as mbpr, mb.product_image as mbpi, mb.product_desc as mbpd, mb.hyper_url as mbhy, mb.kns_url as mbkns, mb.xcom_url as mbxcom, mb.regard_url as mbreg, mb.citi_url as mbciti,
    bd.product_title as bdpt, bd.product_price as bdpr, bd.product_image as bdpi, bd.product_desc as bdpd, bd.hyper_url as bdhy, bd.kns_url as bdkns, bd.xcom_url as bdxcom, bd.regard_url as bdreg, bd.citi_url as bdciti,
    vk.product_title as vkpt, vk.product_price as vkpr, vk.product_image as vkpi, vk.product_desc as vkpd, vk.hyper_url as vkhy, vk.kns_url as vkkns, vk.xcom_url as vkxcom, vk.regard_url as vkreg, vk.citi_url as vkciti,
    cu.product_title as cupt, cu.product_price as cupr, cu.product_image as cupi, cu.product_desc as cupd, cu.hyper_url as cuhy, cu.kns_url as cukns, cu.xcom_url as cuxcom, cu.regard_url as cureg, cu.citi_url as cuciti,
    rm.product_title as rmpt, rm.product_price as rmpr, rm.product_image as rmpi, rm.product_desc as rmpd, rm.hyper_url as rmhy, rm.kns_url as rmkns, rm.xcom_url as rmxcom, rm.regard_url as rmreg, rm.citi_url as rmciti,
    st.product_title as stpt, st.product_price as stpr, st.product_image as stpi, st.product_desc as stpd, st.hyper_url as sthy, st.kns_url as stkns, st.xcom_url as stxcom, st.regard_url as streg, st.citi_url as stciti,
    pu.product_title as pupt, pu.product_price as pupr, pu.product_image as pupi, pu.product_desc as pupd, pu.hyper_url as puhy, pu.kns_url as pukns, pu.xcom_url as puxcom, pu.regard_url as pureg, pu.citi_url as puciti
FROM 
    processors as pr
    LEFT JOIN motherboards as mb 
        ON mb.product_title = '".$marka_mb."'
    LEFT JOIN bodyes as bd 
        ON  bd.product_title = '".$models_bd."'
    LEFT JOIN videocards AS vk 
        ON vk.product_title = '".$videocards_vk."'
    LEFT JOIN cpucoolings AS cu 
        ON cu.product_title = '".$cpucoolings_cu."'
    LEFT JOIN rammemoryes AS rm 
        ON rm.product_title = '".$rammemoryes_rm."'
    LEFT JOIN storages AS st 
        ON st.product_title = '".$storages_st."'
    LEFT JOIN powerunits AS pu 
        ON  pu.product_title = '".$powerunits_pu."'
WHERE 
    pr.processor_id = '".$proc."'";   
echo '<div class="panel-body">';
		$run_query = mysqli_query($con,$product_query);
		if(mysqli_num_rows($run_query) > 0){
		while($row = mysqli_fetch_array($run_query))
        {
		//-----------------------------1-------------------------
        $urlhyper_pr=$row['prhy'];
        $urlkns_pr=$row['prkns'];
        $urlxcom_pr=$row['prxcom'];
        $urlregard_pr=$row['prreg'];
        $urlciti_pr=$row['prciti'];
        $pricepr_hyper=getPriceFromURLHyper($urlhyper_pr);
        $pricepr_kns=getPriceKNS($urlkns_pr);
        $pricepr_xcom=getPriceXcom($urlxcom_pr);
        $pricepr_regard=parsePriceRegardCit($urlregard_pr);
        $pricepr_citi=parsePriceRegardCit($urlciti_pr);

        $prices_pr = array($pricepr_hyper, $pricepr_kns, $pricepr_xcom, $pricepr_regard, $pricepr_citi); // Используем array()

        $filteredprices=array_filter($prices_pr,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_pr=min($filteredprices);
            $index_min_pr = array_search($minprice_pr, $prices_pr);
        }
        else{
            $minprice_pr="Товара нету в магазинах";
        }
        $company_pr=getCompany($index_min_pr);


        if($index_min_pr==0){
            $minurl_pr=$urlhyper_pr;
        }
        if($index_min_pr==1){
            $minurl_pr=$urlkns_pr;
        }
        if($index_min_pr==2){
            $minurl_pr=$urlxcom_pr;
        }
        if($index_min_pr==3){
            $minurl_pr=$urlregard_pr;
        }
        if($index_min_pr==4){
            $minurl_pr=$urlciti_pr;
        }

        

		$pro_title_pr = $row['prpt'];
		$pro_price_pr = $row['prpr'];
		$pro_image_pr = $row['prpi'];
		$product_desc_pr = $row['prpd'];
		//----------------------------2--------------------------

        $urlhyper_mb=$row['mbhy'];
        $urlkns_mb=$row['mbkns'];
        $urlxcom_mb=$row['mbxcom'];
        $urlregard_mb=$row['mbreg'];
        $urlciti_mb=$row['mbciti'];
        $pricemb_hyper=getPriceFromURLHyper($urlhyper_mb);
        $pricemb_kns=getPriceKNS($urlkns_mb);
        $pricemb_xcom=getPriceXcom($urlxcom_mb);
        $pricemb_regard=parsePriceRegardCit($urlregard_mb);
        $pricemb_citi=parsePriceRegardCit($urlciti_mb);

        $prices_mb = array($pricemb_hyper, $pricemb_kns, $pricemb_xcom, $pricemb_regard, $pricemb_citi); // Используем array()

        $filteredprices=array_filter($prices_mb,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_mb=min($filteredprices);
            $index_min_mb = array_search($minprice_mb, $prices_mb);
        }
        else{
            $minprice_mb="Товара нету в магазинах";
        }
        $company_mb=getCompany($index_min_mb);



        if($index_min_mb==0){
            $minurl_mb=$urlhyper_mb;
        }
        if($index_min_mb==1){
            $minurl_mb=$urlkns_mb;
        }
        if($index_min_mb==2){
            $minurl_mb=$urlxcom_mb;
        }
        if($index_min_mb==3){
            $minurl_mb=$urlregard_mb;
        }
        if($index_min_mb==4){
            $minurl_mb=$urlciti_mb;
        }
        



		$pro_title_mb = $row['mbpt'];
		$pro_price_mb = $row['mbpr'];
		$pro_image_mb = $row['mbpi'];
		$product_desc_mb = $row['mbpd'];
		//----------------------------3--------------------------


        $urlhyper_bd=$row['bdhy'];
        $urlkns_bd=$row['bdkns'];
        $urlxcom_bd=$row['bdxcom'];
        $urlregard_bd=$row['bdreg'];
        $urlciti_bd=$row['bdciti'];
        $pricebd_hyper=getPriceFromURLHyper($urlhyper_bd);
        $pricebd_kns=getPriceKNS($urlkns_bd);
        $pricebd_xcom=getPriceXcom($urlxcom_bd);
        $pricebd_regard=parsePriceRegardCit($urlregard_bd);
        $pricebd_citi=parsePriceRegardCit($urlciti_bd);

        $prices_bd = array($pricebd_hyper, $pricebd_kns, $pricebd_xcom, $pricebd_regard, $pricebd_citi); // Используем array()

        $filteredprices=array_filter($prices_bd,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_bd=min($filteredprices);
            $index_min_bd = array_search($minprice_bd, $prices_bd);
        }
        else{
            $minprice_bd="Товара нету в магазинах";
        }
        $company_bd=getCompany($index_min_bd);

        if($index_min_bd==0){
            $minurl_bd=$urlhyper_bd;
        }
        if($index_min_bd==1){
            $minurl_bd=$urlkns_bd;
        }
        if($index_min_bd==2){
            $minurl_bd=$urlxcom_bd;
        }
        if($index_min_bd==3){
            $minurl_bd=$urlregard_bd;
        }
        if($index_min_bd==4){
            $minurl_bd=$urlciti_bd;
        }
        







		$pro_title_bd = $row['bdpt'];
		$pro_price_bd = $row['bdpr'];
		$pro_image_bd = $row['bdpi'];
		$product_desc_bd = $row['bdpd'];
		//----------------------------4--------------------------

        $urlhyper_vk=$row['vkhy'];
        $urlkns_vk=$row['vkkns'];
        $urlxcom_vk=$row['vkxcom'];
        $urlregard_vk=$row['vkreg'];
        $urlciti_vk=$row['vkciti'];
        $pricevk_hyper=getPriceFromURLHyper($urlhyper_vk);
        $pricevk_kns=getPriceKNS($urlkns_vk);
        $pricevk_xcom=getPriceXcom($urlxcom_vk);
        $pricevk_regard=parsePriceRegardCit($urlregard_vk);
        $pricevk_citi=parsePriceRegardCit($urlciti_vk);

        $prices_vk = array($pricevk_hyper, $pricevk_kns, $pricevk_xcom, $pricevk_regard, $pricevk_citi); // Используем array()

        $filteredprices=array_filter($prices_vk,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_vk=min($filteredprices);
            $index_min_vk = array_search($minprice_vk, $prices_vk);
        }
        else{
            $minprice_vk="Товара нету в магазинах";
        }
        $company_vk=getCompany($index_min_vk);

        if($index_min_vk==0){
            $minurl_vk=$urlhyper_vk;
        }
        if($index_min_vk==1){
            $minurl_vk=$urlkns_vk;
        }
        if($index_min_vk==2){
            $minurl_vk=$urlxcom_vk;
        }
        if($index_min_vk==3){
            $minurl_vk=$urlregard_vk;
        }
        if($index_min_vk==4){
            $minurl_vk=$urlciti_vk;
        }



		$pro_title_vk = $row['vkpt'];
		$pro_price_vk = $row['vkpr'];
		$pro_image_vk = $row['vkpi'];
		$product_desc_vk = $row['vkpd'];
		//----------------------------5--------------------------

        $urlhyper_cu=$row['cuhy'];
        $urlkns_cu=$row['cukns'];
        $urlxcom_cu=$row['cuxcom'];
        $urlregard_cu=$row['cureg'];
        $urlciti_cu=$row['cuciti'];
        $pricecu_hyper=getPriceFromURLHyper($urlhyper_cu);
        $pricecu_kns=getPriceKNS($urlkns_cu);
        $pricecu_xcom=getPriceXcom($urlxcom_cu);
        $pricecu_regard=parsePriceRegardCit($urlregard_cu);
        $pricecu_citi=parsePriceRegardCit($urlciti_cu);

        $prices_cu = array($pricecu_hyper, $pricecu_kns, $pricecu_xcom, $pricecu_regard, $pricecu_citi); // Используем array()

        $filteredprices=array_filter($prices_cu,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_cu=min($filteredprices);
            $index_min_cu = array_search($minprice_cu, $prices_cu);
        }
        else{
            $minprice_cu="Товара нету в магазинах";
        }
        $company_cu=getCompany($index_min_cu);

        if($index_min_cu==0){
            $minurl_cu=$urlhyper_cu;
        }
        if($index_min_cu==1){
            $minurl_cu=$urlkns_cu;
        }
        if($index_min_cu==2){
            $minurl_cu=$urlxcom_cu;
        }
        if($index_min_cu==3){
            $minurl_cu=$urlregard_cu;
        }
        if($index_min_cu==4){
            $minurl_cu=$urlciti_cu;
        }





		$pro_title_cu = $row['cupt'];
		$pro_price_cu = $row['cupr'];
		$pro_image_cu = $row['cupi'];
		$product_desc_cu = $row['cupd'];
		//----------------------------6--------------------------

        $urlhyper_rm=$row['rmhy'];
        $urlkns_rm=$row['rmkns'];
        $urlxcom_rm=$row['rmxcom'];
        $urlregard_rm=$row['rmreg'];
        $urlciti_rm=$row['rmciti'];
        $pricerm_hyper=getPriceFromURLHyper($urlhyper_rm);
        $pricerm_kns=getPriceKNS($urlkns_rm);
        $pricerm_xcom=getPriceXcom($urlxcom_rm);
        $pricerm_regard=parsePriceRegardCit($urlregard_rm);
        $pricerm_citi=parsePriceRegardCit($urlciti_rm);

        $prices_rm = array($pricerm_hyper, $pricerm_kns, $pricerm_xcom, $pricerm_regard, $pricerm_citi); // Используем array()

        $filteredprices=array_filter($prices_rm,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_rm=min($filteredprices);
            $index_min_rm = array_search($minprice_rm, $prices_rm);
        }
        else{
            $minprice_rm="Товара нету в магазинах";
        }
        $company_rm=getCompany($index_min_rm);

        if($index_min_rm==0){
            $minurl_rm=$urlhyper_rm;
        }
        if($index_min_rm==1){
            $minurl_rm=$urlkns_rm;
        }
        if($index_min_rm==2){
            $minurl_rm=$urlxcom_rm;
        }
        if($index_min_rm==3){
            $minurl_rm=$urlregard_rm;
        }
        if($index_min_rm==4){
            $minurl_rm=$urlciti_rm;
        }





		$pro_title_rm = $row['rmpt'];
		$pro_price_rm = $row['rmpr'];
		$pro_image_rm = $row['rmpi'];
		$product_desc_rm = $row['rmpd'];
		//----------------------------7--------------------------

        $urlhyper_st=$row['sthy'];
        $urlkns_st=$row['stkns'];
        $urlxcom_st=$row['stxcom'];
        $urlregard_st=$row['streg'];
        $urlciti_st=$row['stciti'];
        $pricest_hyper=getPriceFromURLHyper($urlhyper_st);
        $pricest_kns=getPriceKNS($urlkns_st);
        $pricest_xcom=getPriceXcom($urlxcom_st);
        $pricest_regard=parsePriceRegardCit($urlregard_st);
        $pricest_citi=parsePriceRegardCit($urlciti_st);

        $prices_st = array($pricest_hyper, $pricest_kns, $pricest_xcom, $pricest_regard, $pricest_citi); // Используем array()

        $filteredprices=array_filter($prices_st,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_st=min($filteredprices);
            $index_min_st = array_search($minprice_st, $prices_st);
        }
        else{
            $minprice_st="Товара нету в магазинах";
        }
        $company_st=getCompany($index_min_st);

        if($index_min_st==0){
            $minurl_st=$urlhyper_st;
        }
        if($index_min_st==1){
            $minurl_st=$urlkns_st;
        }
        if($index_min_st==2){
            $minurl_st=$urlxcom_st;
        }
        if($index_min_st==3){
            $minurl_st=$urlregard_st;
        }
        if($index_min_st==4){
            $minurl_st=$urlciti_st;
        }





		$pro_title_st = $row['stpt'];
		$pro_price_st = $row['stpr'];
		$pro_image_st = $row['stpi'];
		$product_desc_st = $row['stpd'];
		//----------------------------8--------------------------

        $urlhyper_pu=$row['puhy'];
        $urlkns_pu=$row['pukns'];
        $urlxcom_pu=$row['puxcom'];
        $urlregard_pu=$row['pureg'];
        $urlciti_pu=$row['puciti'];
        $pricepu_hyper=getPriceFromURLHyper($urlhyper_pu);
        $pricepu_kns=getPriceKNS($urlkns_pu);
        $pricepu_xcom=getPriceXcom($urlxcom_pu);
        $pricepu_regard=parsePriceRegardCit($urlregard_pu);
        $pricepu_citi=parsePriceRegardCit($urlciti_pu);

        $prices_pu = array($pricepu_hyper, $pricepu_kns, $pricepu_xcom, $pricepu_regard, $pricepu_citi); // Используем array()

        $filteredprices=array_filter($prices_pu,function($price){
            return $price!=0;
        });

        if(!empty($filteredprices)){
            $minprice_pu=min($filteredprices);
            $index_min_pu = array_search($minprice_pu, $prices_pu);
        }
        else{
            $minprice_pu="Товара нету в магазинах";
        }
        $company_pu=getCompany($index_min_pu);

        if($index_min_pu==0){
            $minurl_pu=$urlhyper_pu;
        }
        if($index_min_pu==1){
            $minurl_pu=$urlkns_pu;
        }
        if($index_min_pu==2){
            $minurl_pu=$urlxcom_pu;
        }
        if($index_min_pu==3){
            $minurl_pu=$urlregard_pu;
        }
        if($index_min_pu==4){
            $minurl_pu=$urlciti_pu;
        }


		$pro_title_pu = $row['pupt'];
		$pro_price_pu = $row['pupr'];
		$pro_image_pu = $row['pupi'];
		$product_desc_pu = $row['pupd'];
		//--------------------------------------------------------

if(intval($minprice_pr)==0){
    $message_pr='без учёта процессора';
}
else{
    $message_pr='';
}

if(intval($minprice_mb)==0){
    $message_mb='без учёта мат. платы';
}
else{
    $message_mb='';
}

if(intval($minprice_bd)==0){
    $message_bd='без учёта корпуса';
}
else{
    $message_bd='';
}

if(intval($minprice_vk)==0){
    $message_vk='без учёта видеокарты';
}
else{
    $message_vk='';
}

if(intval($minprice_cu)==0){
    $message_cu='без учёта куллера';
}
else{
    $message_cu='';
}

if(intval($minprice_rm)==0){
    $message_rm='без учёта оперативной памяти';
}
else{
    $message_rm='';
}
if(intval($minprice_pu)==0){
    $message_pu='без учёта блока питания';
}
else{
    $message_pu='';
}


		$pro_price_total = intval($minprice_pr) 
                 + intval($minprice_mb) 
                 + intval($minprice_bd) 
                 + intval($minprice_vk) 
                 + intval($minprice_cu) 
                 + intval($minprice_rm) 
                 + intval($minprice_st) 
                 + intval($minprice_pu);

		 echo "
										<!-------------------------------------1----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_pr</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_pr' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_pr." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_pr) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_pr . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'>   <b>Описание:</b><br/> $product_desc_pr</div>
										</div>
										 <!-------------------------------------2----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_mb</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_mb' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_mb." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_mb) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_mb . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;' ><b>Описание:</b><br/> $product_desc_mb</div>
										</div>
										<!-------------------------------------3----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_bd</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_bd' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_bd." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_bd) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_bd . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_bd</div>
										</div>
										<!-------------------------------------4----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_vk</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_vk' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_vk." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_vk) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_vk . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_vk</div>
										</div>
										<!-------------------------------------5----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_cu</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_cu' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_cu." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_cu) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_cu . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_cu</div>
										</div>
										<!-------------------------------------6----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_rm</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_rm' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_rm." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_rm) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_rm . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_rm</div>
										</div>
										<!-------------------------------------7----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_st</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_st' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_st." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_st) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_st . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_st</div>
										</div>
										<!-------------------------------------8----------------------------------->
										<div class='row'>
											<div class='col-md-4'>
														<div class='panel panel-info'>
															<div class='panel-heading'>$pro_title_pu</div>
															<div class='panel-body text-center' style='overflow: hidden;'>
																<img src='component_images/$pro_image_pu' class='img-responsive center-block' style='width:220px; height:250px;'/>
															</div>
															<div class='panel-heading'>". $minprice_pu." ".CURRENCY." <b><a href='" . htmlspecialchars($minurl_pu) . "' target='_blank' style='color: #FFFFFF;'>Магазин</a> :</b> " . $company_pu . "</div>
														</div>
											</div>	
											<div class='panel-right' style='border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;'><b>Описание:</b><br/> $product_desc_pu</div>
										</div>
										<!-------------------------------------------------------------------------->
										<div class='row'>
											<div class='col-md-4'>
												<b><font color='red'>Общая цена сборки: ".$pro_price_total." ".CURRENCY." ".$message_pr." ".$message_mb."".$message_bd."".$message_vk."".$message_cu."".$message_rm."".$message_st."".$message_pu." </font></b>
											</div>
										</div>
										";
		 
 		}
	}
	else {echo "<b><center>Данные не найдены, повторите выбор...</b>";} 
	echo '</div>';
	exit();
    
}
	
//------------------------xajax 1--------------------------------------------------------------------------------

if ($xajax == "getmark") {
    header("Content-type: text/xml; charset=utf-8"); 
    $processor_id = $xajaxargs[0];
    
    if ($processor_id == 0) {
        die("<cmd n=\"as\" t=\"auto\" p=\"options[0].text\"><![CDATA[Ошибка! Выберите процессор...]]></cmd>");
    }

    // Получаем socket_compatibility и chipset_type выбранного процессора
    $stmt = mysqli_prepare($con, "SELECT socket_compatibility, chipset_type FROM processors WHERE processor_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $processor_id);
    mysqli_stmt_execute($stmt);
    $socket_result = mysqli_stmt_get_result($stmt);
    $socket_row = mysqli_fetch_assoc($socket_result);
    $socket_compatibility = $socket_row['socket_compatibility'];
    $chipset_types = explode(", ", $socket_row['chipset_type']); // Разбиваем строку чипсетов

    // Формируем условия для SQL запроса с учетом чипсета
    $chipset_conditions = array_map(fn($chipset) => "chipset_type LIKE '%{$chipset}%'", $chipset_types);
    $chipset_query_part = implode(" OR ", $chipset_conditions);

    $query = "SELECT * FROM motherboards WHERE socket_compatibility = ? AND ($chipset_query_part) ORDER BY product_title ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $socket_compatibility);
    mysqli_stmt_execute($stmt);
    $run_query = mysqli_stmt_get_result($stmt);
    
    $i = 0;
    $out_ = "";
    $last_year = "";
    
    if (mysqli_num_rows($run_query) > 0) {
        while ($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ .= "<cmd n=\"as\" t=\"auto\" p=\"options[$i].text\"><![CDATA[" . htmlspecialchars($row['product_title']) . "]]></cmd>";
                $out_ .= "<cmd n=\"as\" t=\"auto\" p=\"options[$i].value\"><![CDATA[" . htmlspecialchars($row['motherboard_id']) . "]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }
    
    $i++;
    $out_null = "<cmd n=\"as\" t=\"auto\" p=\"options[0].text\"><![CDATA[выберите материнскую плату]]></cmd>";
    $out_null .= "<cmd n=\"as\" t=\"auto\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"auto\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"auto\" p=\"options.length\"><![CDATA[" . $i . "]]></cmd>";
    echo iconv("utf-8", "utf-8", $out . $out_null . $out_ . "</xjx>");
}

//------------------------------------------------------2--------------------------------------------------------
if ($xajax == "getmodels") {
    header("Content-type: text/xml; charset=utf-8"); 
    $motherboard_id = $xajaxargs[0]; // ID выбранной материнской платы 
    if ($motherboard_id == 0) die("Ошибка! Выберите материнскую плату...");

    // Получаем форм-фактор выбранной материнской платы
    $motherboard_query = "SELECT form_factor FROM motherboards WHERE motherboard_id = ".$motherboard_id;
    $motherboard_result = mysqli_query($con, $motherboard_query);
    if (!$motherboard_result) {
        die("Ошибка при получении данных материнской платы: " . mysqli_error($con));
    }
    $motherboard_row = mysqli_fetch_assoc($motherboard_result);
    $form_factor = $motherboard_row['form_factor'];

    // Проверяем, есть ли корпуса с таким же форм-фактором, как у материнской платы
    $check_form_factor_query = "SELECT COUNT(*) as count FROM bodyes WHERE form_factor = '".$form_factor."'";
    $check_form_factor_result = mysqli_query($con, $check_form_factor_query);
    if (!$check_form_factor_result) {
        die("Ошибка при проверке форм-фактора корпусов: " . mysqli_error($con));
    }
    $check_form_factor_row = mysqli_fetch_assoc($check_form_factor_result);
    $has_matching_form_factor = ($check_form_factor_row['count'] > 0);

    // Формируем SQL-запрос в зависимости от наличия корпусов с таким же форм-фактором
    if ($has_matching_form_factor) {
        // Если есть корпуса с таким же форм-фактором, выбираем их и корпуса с форм-фактором "All"
        $product_query = "SELECT * FROM bodyes WHERE form_factor = '".$form_factor."' OR form_factor = 'All' ORDER BY product_title ASC";
    } else {
        // Если нет корпусов с таким же форм-фактором, выбираем только корпуса с форм-фактором "All"
        $product_query = "SELECT * FROM bodyes WHERE form_factor = 'All' ORDER BY product_title ASC";
    }

    // Выполняем запрос и формируем XML-ответ
    $i = 0;
    $out_ = "";
    $last_year = "";
    $run_query = mysqli_query($con, $product_query);
    if (!$run_query) {
        die("Ошибка при выполнении запроса: " . mysqli_error($con));
    }
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"models\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"models\" p=\"options[$i].value\"><![CDATA[".$row['body_id']."]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }

    $i++;
    $out_null =  "<cmd n=\"as\" t=\"models\" p=\"options[0].text\"><![CDATA[выберите корпус]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"models\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"models\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"models\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}
//--------------------------------------------------------3-----------------------------------------------------------
if ($xajax == "getvideocards") {
    header("Content-type: text/xml; charset=utf-8"); 
    $body_id = $xajaxargs[0];
    if ($body_id == 0) die("Ошибка! Выберите корпус...");

    // Получаем максимальную длину видеокарты для выбранного корпуса
    $body_query = "SELECT max_gpu_length FROM bodyes WHERE body_id = ".$body_id;
    $body_result = mysqli_query($con, $body_query);
    $body_row = mysqli_fetch_assoc($body_result);
    $max_gpu_length = $body_row['max_gpu_length'];

    // Выбираем видеокарты, которые подходят по длине
    $product_query = "SELECT * FROM videocards WHERE max_gpu_length <= ".$max_gpu_length." ORDER BY product_title ASC";
    $i = 0;
    $out_ = "";
    $last_year = "";
    $run_query = mysqli_query($con, $product_query);
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"videocards\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"videocards\" p=\"options[$i].value\"><![CDATA[".$row['videocard_id']."]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }

    $i++;
    $out_null =  "<cmd n=\"as\" t=\"videocards\" p=\"options[0].text\"><![CDATA[выберите видеокарту]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"videocards\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"videocards\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"videocards\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}
//---------------------------------------------------------------4---------------------------------------------
if ($xajax == "getcpucoolings") {
    header("Content-type: text/xml; charset=utf-8"); 
    $processor_id = $xajaxargs[0];
    $body_id = $xajaxargs[1];


    if ($processor_id == 0 || $body_id == 0) die("Ошибка! Выберите процессор и корпус...");

    // Получаем данные о процессоре
    $processor_query = "SELECT socket_compatibility FROM processors WHERE processor_id = " . $processor_id;
    $processor_result = mysqli_query($con, $processor_query);
    $processor_row = mysqli_fetch_assoc($processor_result);
    $processor_socket = $processor_row['socket_compatibility'];

    // Получаем данные о корпусе
    $body_query = "SELECT max_cooler_height FROM bodyes WHERE body_id = " . $body_id;
    $body_result = mysqli_query($con, $body_query);
    $body_row = mysqli_fetch_assoc($body_result);
    $body_max_cooler_height = $body_row['max_cooler_height'];

    // Фильтрация кулеров
    $cooler_query = "SELECT * FROM cpucoolings WHERE max_cooler_height <= " . $body_max_cooler_height . " AND socket_compatibility LIKE '%" . $processor_socket . "%' ORDER BY product_title ASC";
    $run_query = mysqli_query($con, $cooler_query);

    $i = 0;
    $out_ = "";
    $last_year = "";

    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"cpucoolings\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"cpucoolings\" p=\"options[$i].value\"><![CDATA[".$row['cpucooling_id']."]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }

    $i++;
    $out_null =  "<cmd n=\"as\" t=\"cpucoolings\" p=\"options[0].text\"><![CDATA[выберите кулер]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"cpucoolings\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"cpucoolings\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"cpucoolings\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}
//---------------------------------------------------------------5---------------------------------------------
if ($xajax == "getrammemoryes") {
    header("Content-type: text/xml; charset=utf-8"); 
    $motherboard_id = $xajaxargs[0];
    
    // Проверка на корректный motherboard_id
    if ($motherboard_id == 0) {
        die("Ошибка! Выберите материнскую плату...");
    }

    // Получаем ram_type выбранной материнской платы
    $motherboard_query = "SELECT ram_type FROM motherboards WHERE motherboard_id = ".$motherboard_id;
    $run_motherboard_query = mysqli_query($con, $motherboard_query);

    // Проверка на ошибку выполнения запроса
    if (!$run_motherboard_query) {
        die("Ошибка при выполнении запроса: " . mysqli_error($con));
    }

    $motherboard_row = mysqli_fetch_assoc($run_motherboard_query);

    // Проверка на наличие данных
    if (!$motherboard_row) {
        die("Ошибка: Материнская плата с ID $motherboard_id не найдена.");
    }

    $ram_type = $motherboard_row['ram_type'];

    // Получаем оперативную память с таким же ram_type
    $product_query = "SELECT * FROM rammemoryes WHERE ram_type = '".$ram_type."' ORDER BY product_title ASC";
    $run_query = mysqli_query($con, $product_query);

    // Проверка на ошибку выполнения запроса
    if (!$run_query) {
        die("Ошибка при выполнении запроса: " . mysqli_error($con));
    }

    $i = 0;
    $out_ = "";
    $last_year = "";

    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"rammemoryes\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"rammemoryes\" p=\"options[$i].value\"><![CDATA[".$row['rammemory_id']."]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }

    $i++;
    $out_null =  "<cmd n=\"as\" t=\"rammemoryes\" p=\"options[0].text\"><![CDATA[выберите опер.память]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"rammemoryes\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"rammemoryes\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"rammemoryes\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}
//---------------------------------------------------------------6---------------------------------------------
if ($xajax == "getstorages") {

    header("Content-type: text/xml; charset=utf-8"); 
    $vendor_id = $xajaxargs[0];
    if ($vendor_id == 0) die("Ошибка! Выберите опер.память...");
	
            
    // Измененный запрос: выбираем все накопители без фильтрации по processor_id
    $product_query = "SELECT * FROM storages ORDER BY product_title ASC";
    $i = 0;
    $out_ = "";
    $last_year = "";
    $run_query = mysqli_query($con, $product_query);
    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_year != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"storages\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"storages\" p=\"options[$i].value\"><![CDATA[".$row['storage_id']."]]></cmd>";
                $last_year = $row['product_title'];
            }
        }
    }
    
    $i++;
    $out_null =  "<cmd n=\"as\" t=\"storages\" p=\"options[0].text\"><![CDATA[выберите накопитель]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"storages\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"storages\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"storages\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}
//---------------------------------------------------------------7---------------------------------------------
if ($xajax == "getpowerunits") {
    header("Content-type: text/xml; charset=utf-8"); 

    $processor_id = $xajaxargs[0];
    $videocard_id = $xajaxargs[1];

    if ($processor_id == 0 || $videocard_id == 0) die("Ошибка! Выберите процессор и видеокарту...");

    // Получаем мощность процессора
    $processor_query = "SELECT power_consumption FROM processors WHERE processor_id = " . $processor_id;
    $processor_result = mysqli_query($con, $processor_query);
    $processor_row = mysqli_fetch_assoc($processor_result);
    $processor_power = $processor_row['power_consumption'];

    // Получаем мощность видеокарты
    $videocard_query = "SELECT power_consumption FROM videocards WHERE videocard_id = " . $videocard_id;
    $videocard_result = mysqli_query($con, $videocard_query);
    $videocard_row = mysqli_fetch_assoc($videocard_result);
    $videocard_power = $videocard_row['power_consumption'];

    // Суммарная мощность
    $total_power = $processor_power + $videocard_power;

    // Выбираем блоки питания с мощностью больше суммарной
    $powerunit_query = "SELECT * FROM powerunits WHERE power_consumption > " . $total_power . " ORDER BY product_title ASC";
    $run_query = mysqli_query($con, $powerunit_query);

    $i = 0;
    $out_ = "";
    $last_car = "";

    if(mysqli_num_rows($run_query) > 0){
        while($row = mysqli_fetch_array($run_query)) {
            if ($last_car != $row['product_title']) {
                $i++;
                $out_ = $out_ . "<cmd n=\"as\" t=\"powerunits\" p=\"options[$i].text\"><![CDATA[".$row['product_title']."]]></cmd>";
                $out_ = $out_ . "<cmd n=\"as\" t=\"powerunits\" p=\"options[$i].value\"><![CDATA[".$row['powerunit_id']."]]></cmd>";
                $last_car = $row['product_title'];
            }
        }
    }

    $i++;
    $out_null = "<cmd n=\"as\" t=\"powerunits\" p=\"options[0].text\"><![CDATA[выберите блок питания]]></cmd>";
    $out_null = $out_null . "<cmd n=\"as\" t=\"powerunits\" p=\"options[0].value\"><![CDATA[0]]></cmd>";

    $out = "<?xml version=\"1.0\" encoding=\"utf-8\" ?><xjx><cmd n=\"as\" t=\"powerunits\" p=\"options.length\"><![CDATA[0]]></cmd><cmd n=\"as\" t=\"powerunits\" p=\"options.length\"><![CDATA[".$i."]]></cmd>";
    echo iconv("utf-8","utf-8",$out . $out_null . $out_ . "</xjx>");
}











?>





