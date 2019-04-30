<?php 
    include 'keys.php';
    global $keys;

    // Caminho para o arquivo
    $filename = 'ativosdatacsv196132.txt';
    $handle = fopen($filename, 'r');

    $ativos = [];
    $siglas = [];
    $header = [];
    $filtros = [];
    $filtros_range = [];
    $response = [
        'result' => []
    ];

    $key = $_GET['key'] ?? '';
    if(empty($key) || !in_array($key, $keys)) {
        die();
    }

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
    
    foreach ($_GET as $key => $value) {
        if(preg_match('/(\w+)_[maior|menor]/', $key)) {
            $filtros_range[] = $key;
        }
    }

    foreach($filtros_range as $filtro) {
        $valor_filtro = (!empty($_GET[$filtro])) ? $_GET[$filtro] : null;
        if(!is_null($valor_filtro) && is_numeric($valor_filtro)) {
            $res = $ativos;
            $filtrado = [];

            // quebrando string
            $aux = explode('_', $filtro);
            foreach($res as $_ativo) {
                if($aux[1] == 'menor') {
                    if($_ativo[$aux[0]] < $valor_filtro) {
                        $filtrado[] = $_ativo;
                    }
                }else if($aux[1] == 'maior') {
                    if($_ativo[$aux[0]] > $valor_filtro) {
                        $filtrado[] = $_ativo;
                    }
                }
            }

            $ativos = $filtrado;
        }
    }
    
    // aplicando filtros nos ativos
    foreach ($filtros as $filtro) {
        $valor_filtro = (!empty($_GET[$filtro])) ? explode(',', $_GET[$filtro]) : null;

        if(!is_null($valor_filtro)) {
            $res = $ativos;
            $filtrado = [];

            // Aplicando case insensitive
            $aux = array_map(function($elemento) {
                return strtoupper($elemento);
            }, $valor_filtro);

            $valor_filtro = $aux;

            // ajustando formatação do CNPJ
            if($filtro == 'cnpj') {
                $aux = array_map(function($elem) {
                    $cnpj = preg_replace('/\D/i', '', $elem);
                    $cnpj = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})/', '$1.$2.$3/$4-', $cnpj);
                    return $cnpj;
                }, $valor_filtro);
                $valor_filtro = $aux;
            }

            foreach ($res as $_ativo) {
                if(!isset($_ativo[$filtro])) continue;
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