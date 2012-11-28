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
 * @param array $status
 * @param array $data
 * @return bool
 */
function migration_reception_end_dist($status, $data){


	spip_log('fin de migration. Resultat:'.var_export($data,1),'migration');
	$status['status'] = 'end';
	if ($data['status']=='abort'){
		$status = abandonner_migration_depuis($status);
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

	if ((count($status['ignore']) OR count($data['errors']))
	  AND defined('_MIGRATION_EMAIL_NOTIFY_IGNORE')
	  AND $email = _MIGRATION_EMAIL_NOTIFY_IGNORE){
		$sujet = "[Migration-ERR] ".$GLOBALS['meta']['adresse_site'];
		$texte = "";
		if (count($status['ignore']))
			$texte .= var_export($status['ignore'],true);
		if (count($data['errors']))
			$texte .= implode("\n",$data['errors']);
		job_queue_add('envoyer_mail','Erreur migration',array($email, $sujet, $texte),'inc/');
	}

	update_migration_depuis($status);
	finir_migration_status_depuis();

	// si on a pas d'upgrade a suivre, vidons les cache
	$version_installee = sql_getfetsel('valeur','spip_meta',"nom='version_installee'");
	if ($GLOBALS['spip_version']==$version_installee) {
		// supprimer les cache pour forcer la mise a jour du site
		include_spip('inc/invalideur');
		spip_log("purger le site","migration");
		supprime_invalideurs();
		@spip_unlink(_CACHE_RUBRIQUES);
		@spip_unlink(_CACHE_PIPELINES);
		@spip_unlink(_CACHE_PLUGINS_PATH);
		@spip_unlink(_CACHE_PLUGINS_OPT);
		@spip_unlink(_CACHE_PLUGINS_FCT);
		@spip_unlink(_CACHE_PLUGINS_VERIF);
		@spip_unlink(_CACHE_CHEMIN);
		#purger_repertoire(_DIR_CACHE,array('subdir'=>true));
		#purger_repertoire(_DIR_AIDE);
		purger_repertoire(_DIR_VAR.'cache-css');
		purger_repertoire(_DIR_VAR.'cache-js');
	}
	@spip_unlink(_FILE_META);

	// on renvoit le bilan pour affichage sur le site source
	return $status;
}