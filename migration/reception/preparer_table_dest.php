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
function migration_reception_preparer_table_dest_dist($status, $data) {
	include_spip('base/dump');

	$status['status'] = 'preparer';
	// initialiser le compteur de progression
	if (!isset($status['progress']['tables'][$data['table']])) {
		$status['progress']['tables'][$data['table']] = '0';
	}

	$res = base_preparer_table_dest($data['table'], $data['desc'], '', $data['init']);
	update_migration_depuis($status);
	return $res;
}
