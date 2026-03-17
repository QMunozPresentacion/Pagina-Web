<?php

function descargarXML($url){
    $options = [
        "http" => [
            "method"  => "GET",
            "header"  => "Accept: application/xml\r\n"
        ]
    ];
    $contexto = stream_context_create($options);
    return file_get_contents($url, false, $contexto);
}

function prepararXML($xmlString){

    $xml = simplexml_load_string($xmlString);

    $ns = $xml->getNamespaces(true);
    
    if (isset($ns['d2']))  
        $xml->registerXPathNamespace('d2', $ns['d2']);

    if (isset($ns['com'])) 
        $xml->registerXPathNamespace('com', $ns['com']);

    if (isset($ns['sit'])) 
        $xml->registerXPathNamespace('sit', $ns['sit']);

    if (isset($ns['loc'])) 
        $xml->registerXPathNamespace('loc', $ns['loc']);
    
    if (isset($ns['lse']))
        $xml->registerXPathNamespace('lse', $ns['lse']);

    return $xml;
}
?>