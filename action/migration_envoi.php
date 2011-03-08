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
 * Action appelee en direct
 * pour envoyer des donnees au site distant
 * la clé partagée migration_key permet de proteger l'envoi un minimum
 * La clé s'invalide automatiquement apres un delai d'inactivité
 *
 * @return void
 */
function action_migration_envoi_dist($action, $data){

	if (!$s = lire_migration_vers_status()){
		return migration_envoi_fail('pas de migration initialisee');
	}

	if (!isset($s['target']) OR !isset($s['key'])){
		return migration_envoi_fail('migration mal definie (pas de target ou de key)');
	}

	$data = array('action'=>$action,'data'=>$data);
	if (!$data = migration_encoder_data($data,$s['key'])){
		return migration_envoi_fail('echec de l\'encodage');
	}

	include_spip('inc/distant');
	// eviter une detection de boundary en la passant directement
	$boundary = substr(md5(rand().'spip'), 0, 8);
	$result = recuperer_page($s['target'],false,false,null,array('action'=>'migration_reception','data'=>$data),$boundary);

	spip_log('envoi : resultat '.$result,'migration');
	if (trim($result)=='OK')
		return true;
	else
		return false;
}

function migration_envoi_fail($raison){
	spip_log($raison,'migration');
	return false;
}