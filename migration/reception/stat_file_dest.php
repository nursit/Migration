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
function migration_reception_stat_file_dest_dist($status, $data) {
	include_spip('base/dump');

	$status['status'] = 'statfile';

	$dir_dest = $data['dir_dest'];
	if (!in_array($dir_dest,array("_DIR_IMG","_DIR_SQUELETTES"))){
		// dossier pas prevu : refuser
		$res = 'FAIL';
		// notons le fichier comme ignore
		$status['ignore']['files'][$dir_dest.$data['file']]=$dir_dest.$data['file'];
	}
	else {
		@define('_DIR_SQUELETTES',_DIR_RACINE."squelettes/");
		$dir_dest = constant($dir_dest);
		$status['progress']['files'][$dir_dest.$data['file']] = 0;
		// verifier l'extension
		// strict en dehors du dossier skel,
		// un peu plus de types autorises dans le dossier skel
		$strict = ($data['dir_dest']!='_DIR_SQUELETTES');
		if (migration_type_fichier_autorise($data['file']),$strict){
			$res = base_stat_file_dest_dist($data['file'],$data['size'],$data['md5'],$dir_dest,$data['init']);
			if (intval($res))
				$status['progress']['files'][$dir_dest.$data['file']] = $res;
		}
		else {
			// renvoyons la taille comme si le fichier etait deja la
			// evite une tentative d'envoi, mais n'empeche pas de securiser
			// l'ecriture aussi car rien ne nous dit que l'envoyeur soit fair play
			$res = $data['size'];
			$status['progress']['files'][$dir_dest.$data['file']] = "X";
			// notons le fichier comme ignore
			$status['ignore']['files'][$dir_dest.$data['file']]=$dir_dest.$data['file'];
		}
	}
	update_migration_depuis($status);

	return $res;
}
