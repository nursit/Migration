<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */

function formulaires_assistant_migration_charger_dist(){

	$valeurs = array(
		'_etapes'=>3,
		'editable' => autoriser('webmestre'),
		'direction' => '',
	);

	return $valeurs;
}

function formulaires_assistant_migration_verifier_1_dist(){

	$erreurs = array();
	if (!autoriser('webmestre')){
		$erreurs['message_erreur'] = 'Vous devez être webmestre pour migrer depuis ou vers un autre site SPIP';
	}

	return $erreurs;

}
