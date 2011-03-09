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
 * ouvrir la connection depuis le site distant
 * pour verifier que ca fonctionne
 *
 * @return bool
 */
function migration_connect_dist($status, $data){

	// inutile de checker une data :
	// si on est arrive jusque la c'est que la connexion marche
	// mais pour l'IHM, on attend l'url du site source comme data
	// ce n'est pas une secu, meme visuelle, car un man in the middle qui a trouvé la clé
	// pourrait envoyer cette info.

	$status['status'] = 'connect';
	$status['source'] = $data;
	update_migration_depuis($status);
	return true;
}