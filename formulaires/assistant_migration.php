<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */
include_spip('inc/migration');

function formulaires_assistant_migration_charger_dist(){

	$valeurs = array(
		'_etapes'=>3,
		'editable' => autoriser('webmestre'),
		'direction' => '',
		'_depuis_status' => '',
		'url_cible' => '',
		'migration_key' => '',
	);

	if (_request('direction')=='depuis'){
		$valeurs['_depuis_status'] = lire_migration_depuis_status();
	}
	else {
		initialiser_migration_depuis(true);
	}

	return $valeurs;
}

function formulaires_assistant_migration_verifier_1_dist(){

	$erreurs = array();
	if (!autoriser('webmestre')){
		$erreurs['message_erreur'] = 'Vous devez être webmestre pour migrer depuis ou vers un autre site SPIP';
	}
	// si on fait annuler a un moment :
	// on revient a l'etape 1 en reinitialisant tout
	if (_request('cancel')){
		initialiser_migration_depuis(true);
		$erreurs['cancel'] = ' ';
	}

	return $erreurs;

}

function formulaires_assistant_migration_verifier_2_dist(){

	$erreurs = array();
	if (!$direction=_request('direction')
	  OR !in_array($direction,array('depuis','vers'))){
		$erreurs['message_erreur'] = 'Choisissez dans quelle direction vous souhaitez transférer vos données';
	}
	// initialiser la cle de migration si on importe depuis un autre site
	elseif ($direction=='depuis') {
		initialiser_migration_depuis();
	}

	return $erreurs;

}

function formulaires_assistant_migration_verifier_3_dist(){

	$erreurs = array();
	if (_request('direction')=='depuis'){
		$s = lire_migration_depuis_status();
		if ($s AND $s['status']!=='ended')
			$erreurs['waiting'] = ' ';
	}
	else {
		foreach(array('url_cible','migration_key') as $obli)
			if (!_request($obli))
				$erreurs[$obli] = _T('info_obligatoire');
		if (!count($erreurs)){
			initialiser_migration_vers(_request('url_cible'),_request('migration_key'));
			$connect = charger_fonction('connect','migration/envoi');
			$res = $connect($GLOBALS['meta']['adresse_site']);
			$erreurs['message_erreur'] = ($res?'Connexion OK':'Impossible de se connecter au site distant. Veuillez verifier l\'URL du site et la clé de migration.');
		}
	}

	return $erreurs;
}
