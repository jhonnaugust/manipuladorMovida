<?php
include_once "./Bitrix24Webhook.php";

$webhook = "https://b24-ol3oxl.bitrix24.com.br/rest/1/6lrqky19v4v9x47r/";
$bx24 = new BitrixAPI($webhook);                                        


$titleDeal = $_GET['TITLE'];
$id = $_GET['ID'];


$categoryID = [0];
$stageID = ['NEW','PREPARATION','PREPAYMENT_INVOICE','EXECUTING','FINAL_INVOICE',];

if(!isset($titleDeal)){
    exit("Nome do negócio não informado");
}

$params = [
    "filter" => [
        "TITLE" =>$titleDeal,
        "CATEGORY_ID" =>$categoryID,
        "STAGE_ID" =>$stageID
    ]
];

var_dump($params);

$response = $bx24->callMethod("crm.deal.list", $params);
$deals = $response->result;

//var_dump($deals);

//Na fase Perdido, verifica se existe Deal criado no fúnil Grandes Grupos
if(count($deals)<1){

	//Atuliza campo para que o mesmo possa ser verificado (na hora de enviar e-mail para o Responsável/Gestor)dentro da automação.
	$semDeal = "UF_CRM_1593805882";
	
	$params_verifica_deal = [
	    "id" =>$id,
	    "fields"=> [
	       $semDeal => 'Sim'
	    ]
	];

	$bx24->callMethod("crm.deal.update", $params_verifica_deal);
	
}else{
	exit('Encerra o processo');
}

