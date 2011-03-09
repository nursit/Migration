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
 * Effacement de la bidouille ci-dessus
 * Toutefois si la table des auteurs ne contient plus qu'elle
 * c'est que la copie etait incomplete et on restaure le compte
 * pour garder la connection au site
 *
 * (mais il doit pas etre bien beau
 * et ca ne marche que si l'id_auteur est sur moins de 3 chiffres)
 *
 * @param string $serveur
 */
function migration_reception_detruire_copieur_si_besoin($status, $data) {
	include_spip('base/dump');
	$status['status'] = 'clean';

	$res = base_detruire_copieur_si_besoin('');
	update_migration_depuis($status);
	return $res;
}