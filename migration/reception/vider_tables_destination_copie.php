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
 * Vider les tables de la base de destination
 * pour la copie dans une base
 *
 * @param array $tables
 * @param string $serveur
 */
function migration_reception_vider_tables_destination_copie($status, $data){
	include_spip('base/dump');

	$status['status'] = 'vider';

	foreach($data['tables'] as $table){
		if (!in_array($table,$data['exlure_tables']))
			$status['progress'][$table] .= "Vide";
	}
	base_vider_tables_destination_copie($data['tables'],$data['exlure_tables'],'');
	update_migration_depuis($status);
	return true;
}
