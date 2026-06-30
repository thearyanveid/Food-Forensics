<?php

function api($url){
 return json_decode(file_get_contents($url),true);
}

function identifyOfficialProduct($p){

 if(!empty($p['barcode'])){
  $data = api("https://world.openfoodfacts.org/api/v0/product/".$p['barcode'].".json");
  if($data['status']==1) return $data['product'];
 }

 if(!empty($p['ingredients'])){
  $q = urlencode($p['ingredients_text'] ?? $p['ingredients']);
  $data = api("https://world.openfoodfacts.org/cgi/search.pl?search_terms=$q&search_simple=1&action=process&json=1");
  if(!empty($data['products'])) return $data['products'][0];
 }

 return null;
}
?>