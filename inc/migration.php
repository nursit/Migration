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

function lire_migration_status($direction){
	if (!in_array($direction,array('depuis','vers')))
		return false;
	$meta = 'migration_'.$direction.'_status';
	if (!isset($GLOBALS['meta'][$meta]))
		return false;

	// verifier le timestamp
	if (!$s = unserialize($GLOBALS['meta'][$meta])
		OR !isset($s['status'])
		OR !isset($s['key'])
    OR ($direction=='depuis' AND !isset($s['timestamp']))
	  OR ($direction=='depuis' AND $s['timestamp']<time()-_MIGRATION_KEY_PERSISTENCE)){
		effacer_meta($meta);
		return false;
	}

	return $s;
}
function lire_migration_depuis_status(){return lire_migration_status('depuis');}
function lire_migration_vers_status(){return lire_migration_status('vers');}

function ecrire_migration_status($direction, $raz = false){
	if (!in_array($direction,array('depuis','vers')))
		return false;
	$meta = 'migration_'.$direction.'_status';
	if ($raz===true) {
		effacer_meta($meta);
		return false;
	}
	elseif(is_array($raz)){
		$s = $raz;
		ecrire_meta($meta,serialize($s));
	}
	elseif (!$s = lire_migration_status($meta)){
		include_spip('inc/acces');
		$s = array(
			'status'=>'init',
			'timestamp'=>time(),
			'key'=> substr(md5(creer_uniqid()),0,8),
		);
		ecrire_meta($meta,serialize($s));
	}
	return $s;
}
function initialiser_migration_depuis($raz = false){ecrire_migration_status('depuis',$raz?true:false);}
function update_migration_depuis($status){
	// mettre a jour le timestamp pour la continuite
	$status['timestamp'] = time();
	ecrire_migration_status('depuis',$status);
}
function initialiser_migration_vers($url,$key){ecrire_migration_status('vers',array('status'=>'init','target'=>$url,'key'=>$key));}


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


// http://doc.spip.org/@calculer_cle_action
function migration_signer_data($action, $key) {
	if (function_exists('sha1'))
		return sha1($action . $key);
	else
		return md5($action . $key);
}

/**
 * Encoder un contexte, le signer avec une cle, le crypter
 * avec la cle de migration, le gziper si possible...
 * l'entree peut etre serialisee
 *
 * @param array|string $c
 * @param string $key
 * @return string
 */
function migration_encoder_data($c, $key) {
	if (is_string($c)
	AND !is_null(@unserialize($c)))
		$c = unserialize($c);

	$cle = migration_signer_data(is_array($c)?serialize($c):$c, $key);
	$c = serialize(array($c,$cle));

	$c = gzdeflate($c);
	$c = migration_xor($c,$key);
	$c = base64_encode($c);

	return $c;
}

/**
 * la procedure inverse de migration_encoder_data()
 *
 * @param string $c
 * @param string $key
 * @return string|bool
 */
function migration_decoder_data($c, $key) {
	$c = @base64_decode($c);
	$c = migration_xor($c, $key);
	$c = @gzinflate($c);
	list($env, $cle) = @unserialize($c);

	if ($cle == migration_signer_data(is_array($env)?serialize($env):$env, $key))
		return $env;
	return false;
}

/**
 * encrypter/decrypter un message
 * http://www.php.net/manual/fr/language.operators.bitwise.php#81358
 *
 * @param string $message
 * @param string $key
 * @return string
 */
function migration_xor($message, $key){

	$keylen = strlen($key);
	$messagelen = strlen($message);
	for($i=0; $i<$messagelen; $i++)
		$message[$i] = ~($message[$i]^$key[$i%$keylen]);

	return $message;
}