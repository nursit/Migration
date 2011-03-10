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
 * Verifier si le fichier de destination est deja la et OK
 * @param string $file
 * @param string $size
 * @param string $md5
 * @param string $dir_dest
 * @param bool $init
 * @return bool
 */
function migration_envoi_stat_file_dest_dist($file,$size,$md5,$dir_dest,$init) {
	$data = array('file'=>$file,'size'=>$size,'md5'=>$md5,'dir_dest'=>$dir_dest, 'init'=>$init);

	$migration_envoi = charger_fonction('migration_envoi','action');
	$res = $migration_envoi('stat_file_dest',$data);
	if (is_string($res) AND !is_numeric($res)){
		// echec : stoppons la copie
		$s = lire_migration_vers_status();
		$s['status'] = 'abort';
		$s['debug'] = $GLOBALS['debug_migration'];
		ecrire_migration_status('vers',$s);
		return false;
	}
	return $res;
}