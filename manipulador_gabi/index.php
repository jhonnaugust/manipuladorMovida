<?php
include_once "./Bitrix24Webhook.php";

$webhook = "https://b24-ol3oxl.bitrix24.com.br/rest/1/6lrqky19v4v9x47r/";
$bx24 = new BitrixAPI($webhook);                                        


$companyID = 1;//$_GET['company_id'];

$categoryID = [0,14];

if(!isset($companyID)){
    exit("Código da empresa não informado");
}

$params = [
    "filter" => [
        "COMPANY_ID" =>$companyID,
        "CATEGORY_ID" =>$categoryID
    ]
];

$response = $bx24->callMethod("crm.deal.list", $params);
$deals = $response->result;



//var_dump($deals);

if(count($deals)<1){
    exit("Empresa não tem négocios");
}

$sum_deals = 0;
foreach($deals as $deal){
    if(strpos($deal->STAGE_ID, 'PREPARATION') !== false){
    $sum_deals = $sum_deals + $deal->OPPORTUNITY;
    echo $deal->TITLE;
    }
}
var_dump($sum_deals);   

$total = "UF_CRM_1578952901";

$params_total = [
    "id" =>$companyID,
    "fields"=> [
       $total => $sum_deals
    ]
];


$bx24->callMethod("crm.company.update", $params_total);



