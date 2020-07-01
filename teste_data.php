<?php
	date_default_timezone_set('America/Sao_Paulo');
	$date = date_create(date('Y-m-d'));
	date_add($date, date_interval_create_from_date_string('-30 days'));
	$timeWithoutDeal = date_format($date, 'd-m-Y');

	echo $timeWithoutDeal;
?>	