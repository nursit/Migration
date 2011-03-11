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
	if ($data['status']=='abort'){
		$status['status'] = 'aborted';
		if (migration_restore_base_si_possible())
			$status['status'] = 'basereverted';
	}
	else {
		// s'assurer que l'auteur qui migre est bien webmestre a l'arrivee
		$id_webmestre = $data['id_webmestre'];
		include_spip('base/abstract_sql');
		$row = sql_fetsel('*','spip_auteurs','id_auteur='.intval($id_webmestre));
		if ($row AND $row['statut']=='0minirezo'){
			// y a-t-il un champ webmestre dans la base ?
			if (!isset($row['webmestre'])){
				sql_alter("TABLE spip_auteurs ADD webmestre varchar(3) DEFAULT 'non' NOT NULL");
				$row['webmestre']='non';
			}
			if ($row['webmestre']!='oui'){
				sql_updateq("spip_auteurs",array('webmestre'=>'oui'),'id_auteur='.intval($id_webmestre));
			}
		}
	}

	update_migration_depuis($status);

	// supprimer le cache des metas pour forcer la mise a jour
	spip_unlink(_FILE_META);

	// on renvoit le bilan pour affichage sur le site source
	return $status;
}