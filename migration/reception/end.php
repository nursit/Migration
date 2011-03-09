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
 * fermer la connection depuis le site distant
 * quand on a fini
 *
 * @return bool
 */
function migration_reception_end_dist($status, $data){

	// inutile de checker une data :
	// si on est arrive jusque la c'est que la connexion marche
	// mais pour l'IHM, on attend l'url du site source comme data
	// ce n'est pas une secu, meme visuelle, car un man in the middle qui a trouvé la clé
	// pourrait envoyer cette info.

	$status['status'] = 'end';
	update_migration_depuis($status);
	return true;
}