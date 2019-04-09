<?php 

    // Caminho para o arquivo
    $filename = 'ativosdatacsv196132.txt';

    $handle = fopen($filename, 'r');

    $ativos = [];
    $siglas = [];
    $header = [];

    $row = 1;
    while($data = fgetcsv($handle)){
        if($row == 1) {
            $header = $data;
            $row++;
            continue;    
        }
        $total = count($data);
        $d = [];
        for($i=0; $i<$total; $i++) {
            $d[$header[$i]] = $data[$i];
        }

        $ativos[] = $d;
        $siglas[] = $d['sigla'];
        $row++;
    }


    $sigla = (isset($_GET['sigla']) && !empty($_GET['sigla'])) ? explode(',', $_GET['sigla']) : null;
    $atributo = (isset($_GET['atributo']) && !empty($_GET['atributo'])) ? explode(',', $_GET['atributo']) : null;

    // filtros
    $classe_filtro = (isset($_GET['classe']) && !empty($_GET['classe'])) ? explode(',', $_GET['classe']) : null;
    $setor_filtro = (isset($_GET['setor']) && !empty($_GET['setor'])) ? explode(',', $_GET['setor']) : null;

    $filtered = [
        'ativos' => []
    ];


    // verificando sigla
    if(!is_null($sigla)) {
        $indices = [];
        foreach($sigla as $codigo_ativo){
            if($index = array_search(strtoupper($codigo_ativo), $siglas)) $indices[] = $index;
        }

        foreach($indices as $indice){
            $filtered['ativos'][] = $ativos[$indice];
        }
    }else {
        $filtered['ativos'] = $ativos;
    }


    if(!is_null($classe_filtro)) {
        $res = $filtered['ativos'];
        $filtered['ativos'] = [];

        foreach($res as $_ativo){
            if(in_array($_ativo['classe'], $classe_filtro)) {
                $filtered['ativos'][] = $_ativo;
            }
        }
    }

    if(!is_null($setor_filtro)) {
        $res = $filtered['ativos'];
        $filtered['ativos'] = [];

        foreach($res as $_ativo){
            if(in_array($_ativo['setor'], $setor_filtro)) {
                $filtered['ativos'][] = $_ativo;
            }
        }
    }


    $final = ['result' => []];

    // verificando atributo
    if(!is_null($atributo)) {
        foreach($filtered['ativos'] as $ativo_filtrado) {
            $ativo = [];
            foreach($atributo as $atr) {
                $ativo[$atr] = $ativo_filtrado[$atr];
            }
            $final['result'][] = $ativo;
        }
    }else {
        $final['result'] = $filtered['ativos'];
    }

    echo json_encode($final);

?>