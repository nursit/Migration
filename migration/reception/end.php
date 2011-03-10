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
 * fermer la connection depuis le site distant
 * quand on a fini
 *
 * @return bool
 */
function migration_reception_end_dist($status, $data){


	spip_log('fin de migration. Resultat:'.$data,'migration');
	$status['status'] = 'end';
	if ($data=='abort'){
		$status['status'] = 'aborted';
		if (migration_restore_base_si_possible())
			$status['status'] = 'basereverted';
	}

	update_migration_depuis($status);
	return true;
}