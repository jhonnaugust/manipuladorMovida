<?php
include_once "./Bitrix24Webhook.php";

$webhook = "https://movida.bitrix24.com.br/rest/5223/821uwjhgfixqe2b5/";
$bx24 = new BitrixAPI($webhook);                                        


$companyID = $_GET['COMPANY_ID'];
$id = $_GET['ID'];


$categoryID = [19];
$stageID = ['C19:NEW','C19:PREPARATION','C19:PREPAYMENT_INVOICE','C19:EXECUTING','C19:FINAL_INVOICE',];

if(!isset($companyID)){
    exit("Nome do negócio não informado");
}

$params = [
    "filter" => [
        "COMPANY_ID" =>$companyID,
        "CATEGORY_ID" =>$categoryID,
        "STAGE_ID" =>$stageID
    ]
];

//var_dump($params);

$response = $bx24->callMethod("crm.deal.list", $params);
$deals = $response->result;

//var_dump($deals);

//Na fase Perdido, verifica se existe Deal criado no fúnil Grandes Grupos
if(count($deals)<1){

	//Atuliza campo para que o mesmo possa ser verificado (na hora de enviar e-mail para o Responsável/Gestor)dentro da automação.
	$semDeal = "UF_CRM_1594151163";
	
	$params_verifica_deal = [
	    "id" =>$id,
	    "fields"=> [
	       $semDeal => 'Sim'
	    ]
	];

	$bx24->callMethod("crm.deal.update", $params_verifica_deal);

	//echo 'atualizou !!!';
	
}else{
	exit('Encerra o processo');
}

