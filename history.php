<?php

	$handle = opendir('historico');
	$stocks = [];
	$available_filters = ['sigla']; // filtros disponíveis
	$range_filters = [];
	$filters = [];
	$history = [];
	$unmatched = [];
	$matched = [];

	// Listando todos os ativos que possuem histórico na base de dados
	while ($filename = readdir($handle)) {
		if($filename == '.' || $filename == '..') continue;

		// recuperando somente o código do ativo
		$stock_code = explode('.', $filename)[0];
		$stocks[] = $stock_code;
	}

	closedir($handle);

	// verificando filtros aplicados
	$filters = array_intersect($available_filters, array_keys($_GET));

	// verificando filtros de intervalo aplicados
	foreach ($_GET as $key => $value) {
        if(preg_match('/(\w+)_[maior|menor]/', $key)) {
            $range_filters[] = $key;
        }
    }


    // Aplicando filtros
    foreach ($filters as $_filter) {
    	$filter_value = (!empty($_GET[$_filter])) ? explode(',', $_GET[$_filter]) : null;

    	if(!is_null($filter_value)) {
    		$matched = array_intersect($filter_value, $stocks);
    		$unmatched = array_diff($filter_value, $stocks);
    		
    		//
    		foreach ($matched as $_stock_code) {
    			$history[$_stock_code] = array_map(function($elem){
    				return explode(',', trim($elem));
    			}, file('historico/'.$_stock_code.'.txt'));
    			array_shift($history[$_stock_code]);
    		}
    	}
    }


    $filtered = [];
	$local = [];
	$_history_lines = array_values($history);

	foreach ($_history_lines as $key => $value) {
		// var_dump($value);
		$local = array_merge($local, $value);
	}


    // Aplicando filtros de intervalo
    foreach ($range_filters as $_filter) {
    	$filter_value = (!empty($_GET[$_filter])) ? $_GET[$_filter] : null;
    	if(!is_null($filter_value)) {
    		
    		$filtered = [];

    		if(preg_match('/date/', $_filter)) {
	    		$value = (int) str_replace('-', '', $filter_value);
	    		$aux = explode('_', $_filter);

	            foreach($local as $_stock) {
	            	$dt =$_stock[1];
	                if($aux[1] == 'menor') {
	                	if($dt < $value) {
	                		$filtered[] = $_stock;
	                	}
	                }else if($aux[1] == 'maior') {
	                    if($dt > $value) {
	                		$filtered[] = $_stock;
	                	}
	                }
	            } // fim do foreach aninhado

	            $local = $filtered;

	    	}else if(preg_match('/price/', $_filter)) {
	    		$value = str_replace(',', '.', str_replace('.', '', $filter_value));
	    		$aux = explode('_', $_filter);

	            foreach($local as $_stock) {
	            	$dt =$_stock[2];
	                if($aux[1] == 'menor') {
	                	if($dt < $value) {
	                		$filtered[] = $_stock;
	                	}
	                }else if($aux[1] == 'maior') {
	                    if($dt > $value) {
	                		$filtered[] = $_stock;
	                	}
	                }
	            } // fim do foreach aninhado

	            $local = $filtered;
	    	}
    	}
    }


    // CRIANDO OBJETO DE RESPOSTA
    $result = [
    	'not_found' => $unmatched,
    	'found' => []
    ];
    $found= [];

    // aplicando formato de data
    $formatted_local = array_map(function($f){
    	$date = $f[1];
    	$year = substr($date, 0, 4);
    	$month = substr($date, 4, 2);
    	$day = substr($date, 6, 2);
    	$f[1] = $day.'/'.$month.'/'.$year;
    	return $f;
    }, $local);

    foreach ($matched as $key => $sigla) {
    	$found[$sigla] = [];
    	foreach ($formatted_local as $_key => $history_line) {
    		if($history_line[0] == $sigla) {
    			$found[$sigla][] = $history_line;
    		}
    	}
    }


   

    $result['found'] = $found;


    echo '<pre>';
    echo json_encode($result);

    
