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

	$squelette = migration_determiner_dossier_squelette();
	$valeurs = array(
		'_etapes'=>3,
		'editable' => autoriser('webmestre'),
		'direction' => '',
		'_depuis_status' => '',
		'url_cible' => '',
		'migration_key' => '',
		'quoi' => array('base','fichiers','squelettes'),
		'_auth_depuis' => verifier_auth_depuis()?' ':'',
		'_dir_img' => joli_repertoire(_DIR_IMG),
		'_dir_skel' => $squelette?joli_repertoire($squelette):'',
	);

	if (_request('direction')=='depuis'){
		$valeurs['_depuis_status'] = lire_migration_depuis_status();
	}
	else {
		initialiser_migration_depuis(true);
	}

	return $valeurs;
}

function verifier_auth_depuis(){
	// verifier la version de SPIP pour autoriser la migration depuis un autre
	// reserve a SPIP>= 2.1.x
	$_auth_depuis = false;
	if (!isset($GLOBALS['spip_version_branche']))
		return false;
	$v = explode('.',$GLOBALS['spip_version_branche']);
	if ($v[0]>2 OR ($v[0]==2 AND $v[1]>0))
		$_auth_depuis = true;
	return $_auth_depuis;
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
		if (verifier_auth_depuis()){
			initialiser_migration_depuis();
			migration_backup_base_si_possible();
		}
		else {
			// hack ?
			$erreurs['message_erreur'] = _T('migration:erreur_direction_depuis_interdite');
		}
	}
	elseif ($direction=='vers') {
		if (!$quoi=_request('quoi')
		  OR !is_array($quoi)
			OR (!in_array('base',$quoi) AND !in_array('fichiers',$quoi) AND !in_array('squelettes',$quoi))){
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
		foreach(array('url_cible') as $obli)
			if (!_request($obli))
				$erreurs[$obli] = _T('info_obligatoire');
		if (strpos(_request('url_cible'),'+')===false)
			$erreurs['url_cible'] = _T('migration:erreur_url_incorrecte');
		if (!count($erreurs)){
			$url_cible = _request('url_cible');
			$url_cible = explode('+',$url_cible);
			$migration_key = array_pop($url_cible);
			$url_cible = implode('+',$url_cible);

			initialiser_migration_vers($url_cible,$migration_key,_request('quoi'));
			$connect = charger_fonction('connect','migration/envoi');
			$res = $connect($GLOBALS['meta']['adresse_site']);
			if ($res!==true){
				$erreurs['message_erreur'] = _T(is_string($res)?$res:'migration:erreur_echec_connexion_init');
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

