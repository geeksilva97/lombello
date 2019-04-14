<?php 

    // Caminho para o arquivo
    $filename = 'ativosdatacsv196132.txt';

    $handle = fopen($filename, 'r');

    $ativos = [];
    $siglas = [];
    $header = [];
    $filtros = [];
    $response = [
        'result' => []
    ];

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

    $filtros = array_intersect($header, array_keys($_GET));

    // aplicando filtros nos ativos
    foreach ($filtros as $filtro) {
        $valor_filtro = (!empty($_GET[$filtro])) ? explode(',', $_GET[$filtro]) : null;

        if(!is_null($valor_filtro)) {
            $res = $ativos;
            $filtrado = [];

            foreach ($res as $_ativo) {
                if(in_array($_ativo[$filtro], $valor_filtro)) {
                    $filtrado[] = $_ativo;
                }
            }

            $ativos = $filtrado;
        }
    }

    // atributos
    $atributo = (isset($_GET['atributo']) && !empty($_GET['atributo'])) ? explode(',', $_GET['atributo']) : null;



    if(!is_null($atributo)) {
        $filtrado = [];
        foreach($ativos as $ativo_filtrado) {
            $ativo = [];
            foreach($atributo as $atr) {
                $ativo[$atr] = $ativo_filtrado[$atr];
            }
            $filtrado[] = $ativo;
        }
        $ativos = $filtrado;
    }

    echo json_encode(
        [
            'result' => $ativos
        ]
    );
?>