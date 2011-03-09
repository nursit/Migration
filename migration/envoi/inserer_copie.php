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
 * @param array $rows
 * @param array $desc_dest
 * @param string $serveur_dest
 * @return int/bool
 */
function migration_envoi_inserer_copie($table,$rows,$desc_dest,$serveur_dest){
	$data = array('table'=>$table,'rows'=>$rows,'desc_dest'=>$desc_dest,'serveur'=>$serveur_dest);

	$migration_envoi = charger_fonction('migration_envoi','action');
	return $migration_envoi('inserer_copie',$data);
}