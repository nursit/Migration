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
 * ouvrir la connection depuis le site distant
 * pour verifier que ca fonctionne
 *
 * @return bool
 */
function migration_envoi_end_dist($status, $id_webmestre){

	$migration_envoi = charger_fonction('migration_envoi','action');
	$res = $migration_envoi('end',array('status'=>$status,'id_webmestre'=>$id_webmestre));
	return $res;
}