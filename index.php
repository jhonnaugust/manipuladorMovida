<?php
include_once "./Bitrix24Webhook.php";

$webhook = "https://b24-ol3oxl.bitrix24.com.br/rest/1/6lrqky19v4v9x47r/";
$bx24 = new BitrixAPI($webhook);                                        


//$titleDeal = 'Teste manipulador Movida 4';
$titleDeal = $_GET['TITLE'];

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

$response = $bx24->callMethod("crm.deal.list", $params);
$deals = $response->result;

//var_dump($deals);

//Na fase Perdido, verifica se existe Deal criado no fúnil Grandes Grupos
if(count($deals)<1){

	date_default_timezone_set('America/Sao_Paulo');
	$date = date_create(date('Y-m-d'));
	date_add($date, date_interval_create_from_date_string('-30 days'));
	$timeWithoutDeal = date_format($date, 'd-m-Y');

	$stageIdLose = ['LOSE'];

	$params = [
	    "filter" => [
	        "TITLE" =>$titleDeal,
	        "CATEGORY_ID" =>$categoryID,
	        "STAGE_ID" =>$stageIdLose
	    ]
	];
	
	$response2 = $bx24->callMethod("crm.deal.list", $params);
    $dealsLose = $response2->result;

    foreach($dealsLose as $deal){
    	$id = $deal->ID;
    	$name = $deal->TITLE;
    	$assigned_by_id[] = $deal->ASSIGNED_BY_ID; 
	}

	
	$assigned_by_id;
	$message = "ATENÇÃO! O cliente <b>{$titleDeal}</b> está sem nenhum card criado desde <b>{$timeWithoutDeal}</b>, data onde seu último negócio foi fechado.";


	$params_message = [
	    	"USER_ID" =>$assigned_by_id,
	    	"MESSAGE" =>$message
	    
	];

	$bx24->callMethod('im.notify', $params_message);

	
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

