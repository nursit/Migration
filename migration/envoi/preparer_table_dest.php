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
 * Preparer la table dans la base de destination :
 * la droper si elle existe (sauf si auteurs ou meta sur le serveur principal)
 * la creer si necessaire, ou ajouter simplement les champs manquants
 *
 * @param string $table
 * @param array $desc
 * @param string $serveur_dest
 * @param bool $init
 * @return array
 */
function migration_envoi_preparer_table_dest($table, $desc, $serveur_dest, $init=false) {
	$data = array('table'=>$table,'desc'=>$desc,'serveur'=>$serveur_dest, 'init'=>$init);

	$migration_envoi = charger_fonction('migration_envoi','action');
	return $migration_envoi('preparer_table_dest',$data);
}
