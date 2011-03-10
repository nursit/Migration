<?php
include_spip('inc/migration');

function migration_affiche_champs_ignores($ignores){
	if (!is_array($ignores) OR !count($ignores))
		return '';
	$res = array();
	foreach($ignores as $table=>$champs){
		$res[] = "Table $table&nbsp;: ".implode(', ',$champs);
	}
	return implode('<br />',$res);
}

function migration_affiche_fichiers_ignores($ignores){
	if (!is_array($ignores) OR !count($ignores))
		return '';
	$res = array();
	foreach($ignores as $file=>$dummy){
		$res[] = "Fichier $file";
	}
	return implode('<br />',$res);
}

?>