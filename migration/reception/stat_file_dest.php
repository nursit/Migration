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
	$status['progress']['files'][$data['file']] = 0;

	$res = base_stat_file_dest_dist($data['file'],$data['size'],$data['md5'],_DIR_IMG,$data['init']);
	if (intval($res))
		$status['progress']['files'][$data['file']] = $res;
	update_migration_depuis($status);

	return $res;
}
