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
 * Ecrire un morceau du fichier de destination
 * @param string $file
 * @param string $d
 * @param string $dir_dest
 * @return bool
 */
function migration_envoi_fichier_ecrire_dist($file, $d, $dir_dest) {
	if ($dir_dest==_DIR_IMG) { $dir_dest='_DIR_IMG';
	}
	if ($dir_dest==_DIR_RACINE.'squelettes/') { $dir_dest='_DIR_SQUELETTES';
	}

	$data = array('file'=>$file,'d'=>$d,'dir_dest'=>$dir_dest);

	$migration_envoi = charger_fonction('migration_envoi', 'action');
	$res = $migration_envoi('fichier_ecrire', $data);
	if ($res===false or (!is_bool($res) and !is_numeric($res))) {
		// echec : stoppons la copie
		$s = lire_migration_vers_status();
		$s['status'] = 'abort';
		$s['debug'] = $GLOBALS['debug_migration'];
		ecrire_migration_status('vers', $s);
		return false;
	}
	return $res;
}
