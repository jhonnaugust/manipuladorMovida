<script type="text/javascript"></script>
<?php

class BitrixAPI
{
	//fields
	private $webhook;

	public function __construct($webhook) 
	{      
       $this->webhook = $webhook;
	}
    public function callMethodPage($method, $params, $start, $page_size)
    {
        $comands = [];
        for($i = $start; $i < $start + $page_size; $i+=50){
            $comands[$i] = $method."?start=".($i) . "&" . http_build_query($params);
        }
        $qtt_requests = count($comands);
        $final_result = []; 
        $total = null;
        $control = $start;
        for($i = 0 ; $i < $qtt_requests ; $i+=50){    
            $batch_payload = [
                'cmd' => array_slice($comands, $i, $i+50)
            ];
            $response_batch = $this->callMethod("batch", $batch_payload);

            $result_batch = $response_batch->result->result;   
            if(!is_array($result_batch)){
                throw new \Exception("BX24 API: batch result not set, error: ". print_r($response_batch->result->result_error[0],true));
            }
            $total = $response_batch->result->result_total[0]; 
            foreach($result_batch as $result_key => $result){    
                if($control > $total){
                    break 2;
                }   
                foreach($result as $elem){
                    $final_result[] = $elem;
                }
                $control+=50;
            }     
        }
        $return = [
            'result'=> $final_result,
            'page' => count($final_result),
            'total' => $total
        ];
        return $return;
    }
    public function callMethodAll($method, $params = [])
    {
        $response_0 = $this->callMethod($method, $params);
        $total =  $response_0->total;
        $result_0 = $response_0->result;

        $total_requests_necessarios = intval($total / 50 );
        $final_result = [];
        $batchs_exec = 0;
        $start = 1;
        $stop = 50;
        while($total_requests_necessarios >= $start && $batchs_exec < 20){
            $batch_payload = [
                'cmd'=>[]
            ];
            for($i = $start;$i<=$total_requests_necessarios && $i <= $stop; $i++){
                $batch_payload['cmd']["result_$i"] = $method."?start=".($i*50) . "&" . http_build_query($params);
            } 
            $response_batch_0 = $this->callMethod("batch", $batch_payload);
            $result_batch = $response_batch_0->result->result;
            $response_batch_0 = null;
            $result_batch_array = [];
            
            for($i = $start;$i<=$total_requests_necessarios & $i <= $stop; $i++){
                $r = "result_".$i;
                $result_batch_array[] = $result_batch[$r];
                $result_batch[$r] = null;
                $start = $i;
            }
            $start++;
            $final_result = array_merge($final_result, ...$result_batch_array);
            $result_batch_array = null;
            $batchs_exec++;
            $stop+=50;
        }
        $final_result = array_merge($result_0, $final_result);
        $return = [
            'result'=> $final_result,
            'page' => count($final_result),
            'total'=> $total
        ];
        return $return;
    }
	public function callMethod($method, $params = [], $refresh=true) 
	{   
        $queryUrl  = $this->webhook . $method;
        $queryData = http_build_query($params);
		//var_dump($queryData);
        $curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_POST           => 1,
		  CURLOPT_HEADER         => 0,
		  CURLOPT_RETURNTRANSFER => 1,
		  CURLOPT_URL            => $queryUrl,
		  CURLOPT_POSTFIELDS     => $queryData,
		  CURLOPT_CONNECTTIMEOUT => 0,
          CURLOPT_TIMEOUT=> 1000, //1000seconds,
          CURLOPT_SSL_VERIFYPEER => false
		));
        $result = curl_exec($curl);
        if(!$result){
            echo curl_error($curl);
        }
		curl_close($curl);
		$result = json_decode($result);
	   	 
		if(isset($result->error) && $result->error == "QUERY_LIMIT_EXCEEDED"){
			usleep(200);
			$result = $this->callMethod($method, $params, false);
        }
        if(isset($result->error)){
			throw new Exception("Bitrix24 Error: " . $result->error_description);
		}
		return $result;
    }
	
}
