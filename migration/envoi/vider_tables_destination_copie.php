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
function migration_envoi_vider_tables_destination_copie($tables, $exlure_tables = array(), $serveur=''){
	$data = array('tables'=>$tables,'exlure_tables'=>$exlure_tables,'serveur'=>$serveur);

	$migration_envoi = charger_fonction('migration_envoi','action');
	return $migration_envoi('vider_tables_destination_copie',$data);
}
