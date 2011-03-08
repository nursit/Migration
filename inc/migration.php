<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */

// securite : la cle reste valable 10min apres la reception des dernieres donnees
// ou de son init
if (!defined('_MIGRATION_KEY_PERSISTENCE'))
	define('_MIGRATION_KEY_PERSISTENCE',600);

function lire_migration_depuis_status(){
	if (!isset($GLOBALS['meta']['migration_status']))
		return false;

	// verifier le timestamp
	if (!$s = unserialize($GLOBALS['meta']['migration_status'])
		OR !isset($s['timestamp'])
		OR !isset($s['status'])
		OR !isset($s['key'])
	  OR $s['timestamp']<time()-_MIGRATION_KEY_PERSISTENCE){
		effacer_meta('migration_status');
		return false;
	}

	return $s;
}

function initialiser_migration_depuis($raz = false){
	if ($raz) {
		effacer_meta('migration_status');
		return false;
	}

	if (!$s = lire_migration_depuis_status()){

		include_spip('inc/acces');
		$s = array(
			'status'=>'init',
			'timestamp'=>time(),
			'key'=> substr(md5(creer_uniqid()),0,8),
		);
		ecrire_meta('migration_status',serialize($s));
	}
	return $s;
}


function migration_afficher_status($status){
	$s = "?";
	switch($status['status']){
		case 'init':
			$s = 'En attente de connexion du site distant';
			break;
		default:
			$s = $status['status'];
			break;
	}
	return $s . ' ' . time();
}
?>