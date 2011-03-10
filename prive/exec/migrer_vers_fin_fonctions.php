<?php
include_spip('inc/migration');

function affiche_champs_ignores($ignores){
	if (!is_array($ignores) OR !count($ignores))
		return '';
	$res = '';
	foreach($ignores as $table=>$champs){
		$res .= "Table $table&nbsp;: ".implode(', ',$champs);
	}
	return $res;
}
?>