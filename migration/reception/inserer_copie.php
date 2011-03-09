<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */
include_spip('inc/migration');

/**
 * fonction d'insertion en base lors de la copie de base a base
 *
 * @param string $table
 * @param array $row
 * @param array $desc_dest
 * @param string $serveur_dest
 * @return int/bool
 */
function migration_reception_inserer_copie($status, $data){
	include_spip('base/dump');

	$status['status'] = 'copier';
	$status['compteurs']['table'][$data['table']] += count($data['rows']);
	$status['progress'][$data['table']] .= "Table ".$data['table']." : ".$status['compteurs']['table'][$data['table']];

	$res = base_inserer_copie($data['table'],$data['rows'],$data['desc_dest'],'');
	update_migration_depuis($status);
	return $res;
}