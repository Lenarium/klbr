<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Loader; 
use Bitrix\Highloadblock as HL; 

Loader::includeModule("sale");
Loader::includeModule("highloadblock"); 

// получение списка заказов за вчерашний день и создание файла csv
// начал 21:11
$yesterday = \Bitrix\Main\Type\Date::createFromText("вчера")->format("d.m.Y");
$today = \Bitrix\Main\Type\Date::createFromText("сегодня")->format("d.m.Y");

$dbRes = \Bitrix\Sale\Order::getList([
    'select' => ['ID', 'DATE_PAYED', 'PRICE'],
    'filter' => [   
        [
            'LOGIC' => 'AND',
            "<DATE_INSERT" =>  $today, 
            ">=DATE_INSERT" => $yesterday, 
        ],
        "PAYED" => "Y", 
        "CANCELED" =>"N", 
    ],
    'order' => ['ID' => 'DESC']
]);

$fp = fopen('file'.$yesterday.'.csv', 'w');
fputcsv($fp, ['номер заказа', 'дата заказа', 'сумма заказа']);
$count = 0;
$orderListPrice = 0;
while ($order = $dbRes->fetch()){
    $orderListPrice = $orderListPrice + $order['PRICE'];
    $count = $count + 1;
    fputcsv($fp, $order);
}
fclose($fp);
//закончил 21:41


// занесение данных в hlbl
// начал выполнение 21:46
$hlbl = 4;
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 
$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data_class = $entity->getDataClass(); 

// Массив полей для добавления
$data = array(
    "UF_DATE"           => date("d.m.Y"),
    "UF_FILE_SRC"       => __DIR__.'file'.$yesterday.'.csv',
    "UF_ORDERS_COUNT"   => $count,
    "UF_ORDER_PRICE"    => $orderListPrice,
);
$result = $entity_data_class::add($data);
//закончил 21:56



// https://skr.sh/sDBDwg3dCvG
// https://skr.sh/sDBUKuxdvTZ
// https://skr.sh/sDBC3eHT1w8
