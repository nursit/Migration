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
function action_migration_envoi_dist($action, $data = '') {

	if (!$s = lire_migration_vers_status()) {
		return migration_envoi_fail('pas de migration initialisee');
	}

	if (!isset($s['target']) or !isset($s['key'])) {
		return migration_envoi_fail('migration mal definie (pas de target ou de key)');
	}

	$data = array('action'=>$action,'data'=>$data);
	if (!$data = migration_encoder_data($data, $s['key'])) {
		return migration_envoi_fail('echec de l\'encodage');
	}

	include_spip('inc/migration_distant');
	// eviter une detection de boundary en la passant directement
	$boundary = substr(md5(rand().'spip'), 0, 8);
	$result = migration_recuperer_page($s['target'], false, false, null, array('action'=>'migration_reception','data'=>$data), $boundary, true);

	$result = trim($result);
	spip_log("envoi : action $action resultat =|".$result.'|=', 'migration');

	$GLOBALS['debug_migration'] = $result;
	if ($result==='FAIL') {
		return false;
	} else {
		return unserialize($result);
	}
}

function migration_envoi_fail($raison) {
	spip_log($raison, 'migration');
	return false;
}
