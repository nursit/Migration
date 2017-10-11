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
function migration_reception_fichier_ecrire_dist($status, $data) {
	include_spip('base/dump');

	$status['status'] = 'cp';

	$dir_dest = $data['dir_dest'];
	if (!in_array($dir_dest, array('_DIR_IMG','_DIR_SQUELETTES'))) {
		// dossier pas prevu : refuser
		$res = 'FAIL';
		// notons le fichier comme ignore
		$status['ignore']['files'][$dir_dest.$data['file']]=$dir_dest.$data['file'];
		spip_log('tentative de copie fichier '.$data['file'].' vers repertoire interdit '.$dir_dest, 'migration');
	} else {
		@define('_DIR_SQUELETTES', _DIR_RACINE.'squelettes/');
		$dir_dest = constant($dir_dest);
		// strict en dehors du dossier skel,
		// un peu plus de types autorises dans le dossier skel
		$strict = ($data['dir_dest']!='_DIR_SQUELETTES');
		if (migration_type_fichier_autorise($data['file'], $strict)) {
			$res = base_fichier_ecrire_dist($data['file'], $data['d'], $dir_dest);
			if ($res) {
				$status['progress']['files'][$dir_dest.$data['file']] = $res;
			}
		} else {
			// on ne devrait pas arriver la car le fichier a ete refuse au moment du stat
			// on peut presumer que c'est une tentative de passage en force.
			// Est-ce qu'on ignore juste, ou est-ce qu'on abandonne tout ?
			$res = 'FAIL';
			// notons le fichier comme ignore
			$status['ignore']['files'][$dir_dest.$data['file']]=$dir_dest.$data['file'];
			spip_log('tentative ecriture fichier '.$data['file'].' : type interdit dans '.$dir_dest, 'migration');
		}
	}

	update_migration_depuis($status);
	return $res;
}
