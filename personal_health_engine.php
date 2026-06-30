<?php

function analyzeForUser($product,$user){

$nut = $product['nutriments'] ?? [];

$risk=0;
$warnings=[];

if($user['age']>50 && ($nut['sodium_100g'] ?? 0)>400){
 $risk+=2;
 $warnings[]="High sodium not ideal for seniors";
}

if(stripos($user['diseases'],"diabetes")!==false && ($nut['sugars_100g'] ?? 0)>10){
 $risk+=3;
 $warnings[]="High sugar risk for diabetes";
}

$bmi=$user['weight']/pow($user['height']/100,2);

if($bmi>30 && ($nut['fat_100g'] ?? 0)>20){
 $risk+=2;
 $warnings[]="High fat not advised for obesity";
}

if($risk<=2) $level="Low";
elseif($risk<=5) $level="Moderate";
else $level="High";

$nutrition=[
"calories"=>min(($nut['energy-kcal_100g'] ?? 0)/5,100),
"sugar"=>min(($nut['sugars_100g'] ?? 0)*5,100),
"sodium"=>min(($nut['sodium_100g'] ?? 0)/5,100),
"fat"=>min(($nut['fat_100g'] ?? 0)*4,100)
];

$list=[];

if(!empty($product['ingredients_text'])){
 $ings=explode(",",$product['ingredients_text']);

 foreach($ings as $i){

  $riskLevel="Safe";

  if(stripos($i,"sugar")!==false) $riskLevel="Moderate";
  if(stripos($i,"benzoate")!==false) $riskLevel="High";

  $list[]=[
   "name"=>trim($i),
   "category"=>"Ingredient",
   "risk"=>$riskLevel
  ];
 }
}

return [
"product_name"=>$product['product_name'] ?? "Unknown",
"brand"=>$product['brands'] ?? "",
"image"=>$product['image_front_url'] ?? "",
"risk_score"=>$risk,
"risk_level"=>$level,
"warnings"=>$warnings,
"nutrition"=>$nutrition,
"ingredients_list"=>$list
];

}
?>