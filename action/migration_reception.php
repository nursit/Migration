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
 * Action non securisee: c'est une porte ouverte par un webmestre
 * et seule la clé partagée migration_key permet de se proteger.
 * La clé s'invalide automatiquement apres un delai d'inactivité
 * 
 * @return void
 */
function action_migration_reception_dist(){

	if (!$s = lire_migration_depuis_status()){
		migration_reponse_fail('pas de migration en cours ou migration echouee par timeout');
	}

	if (!$data = _request('data')){
		migration_reponse_fail('pas de "data" dans la requete');
	}

	if (!$data = migration_decoder_data($data,$s['key'])){
		migration_reponse_fail('signature data invalide');
	}

	/*
	 * data est un tableau
	 * 'action' => action demandee
	 * 'data' => donnees passees a l'action
	 */

	if (!isset($data['action'])){
		migration_reponse_fail('aucune action demandee');
	}
	if ($s['status']=='init' AND $data['action']!=='connect'){
		migration_reponse_fail('action demandee '.$data['action'].' avant action connect reussie');
	}


	if (!$action = charger_fonction($data['action'],'migration/reception',true)) {
		migration_reponse_fail("action inconnue : ".$data['action']);
	}

	$res = $action($s, $data['data']);

	// toujours serializer le retour
	$res = serialize($res);
	spip_log("action ".$data['action'].' resultat:'.$res,'migration');
	echo $res;
	exit;
}

function migration_reponse_fail($raison){
	spip_log($raison,'migration');
	echo 'FAIL';
	exit;
}