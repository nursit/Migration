<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */
include_spip('inc/migration');
include_spip('inc/actions');

function action_migration_depuis_status_dist(){
	#$securiser_action = charger_fonction('securiser_action','inc');
	#$securiser_action();

	$s = lire_migration_depuis_status();
	// verifier qu'un verrou n'etait pas sur le fichier
	// et qu'on arrive pas a le lire en attendant un peu
	if (!$s){
		usleep(333);
		$s = lire_migration_depuis_status();
	}
	// verifier qu'un verrou n'etait pas sur le fichier
	// et qu'on arrive pas a le lire en attendant un peu
	if (!$s){
		usleep(333);
		$s = lire_migration_depuis_status();
	}

	if (!$s)
		ajax_retour("Echec : le site distant n'a pas réussi à se connecter, la migration a été abandonnée.");
	else{
		if ($s['key']!=_request('key')){
			ajax_retour("La clé de migration n'est plus valable, recommmencez la migration");
		}
		else {
			ajax_retour(migration_afficher_status($s));
		}
	}
	exit;
}