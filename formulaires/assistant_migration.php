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
		'quoi' => array('base','docs'),
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
		$erreurs['message_erreur'] = _T('migration:erreur_droits_webmestre');
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
		$erreurs['message_erreur'] = _T('migration:erreur_direction_obligatoire');
	}
	// initialiser la cle de migration si on importe depuis un autre site
	elseif ($direction=='depuis') {
		initialiser_migration_depuis();
		migration_backup_base_si_possible();
	}
	elseif ($direction=='vers') {
		if (!$quoi=_request('quoi')
		  OR !is_array($quoi)
			OR (!in_array('base',$quoi) AND !in_array('fichiers',$quoi))){
			$erreurs['quoi'] = _T('migration:erreur_choisissez_quoi');
		}
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
			initialiser_migration_vers(_request('url_cible'),_request('migration_key'),_request('quoi'));
			$connect = charger_fonction('connect','migration/envoi');
			$res = $connect($GLOBALS['meta']['adresse_site']);
			if (!$res){
				$erreurs['message_erreur'] = _T('migration:erreur_echec_connexion_init');
			}
		}
	}

	return $erreurs;
}


function formulaires_assistant_migration_traiter_dist(){
	$s = lire_migration_vers_status();
	include_spip('base/dump');
	$status_file = base_dump_meta_name(substr(md5($s['target']),0,8));

	// ici on prend toutes les tables sauf celles exclues par defaut
	// (tables de cache en pratique)
	$exclude = lister_tables_noexport();
	list($tables,) = base_liste_table_for_dump($exclude);
	$tables = base_lister_toutes_tables('',$tables,$exclude);

	include_spip('inc/migrer_vers');
	$res = migrer_vers_init($status_file, $tables);

	if ($res===true) {
		// on lance l'action sauvegarder qui va realiser la sauvegarde
		// et finira par une redirection vers la page sauvegarde_fin
		include_spip('inc/actions');
		$redirect = generer_action_auteur('migrer_vers', $status_file);
		return array('message_ok'=>_T('migration:message_connexion_ok'),'redirect'=>$redirect);
	}
	else
		return array('message_erreur'=>$res);
}

