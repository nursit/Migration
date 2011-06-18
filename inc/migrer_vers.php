<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('base/dump');

function inc_migrer_vers_dist($status_file, $redirect='') {
	if (!$status = migrer_vers_lire_status($status_file)) {
		// rien a faire ?
	}
	else {
		$status_file = _DIR_TMP.basename($status_file).".txt";
		$timeout = ini_get('max_execution_time');
		// valeur conservatrice si on a pas reussi a lire le max_execution_time
		if (!$timeout) $timeout=30; // parions sur une valeur tellement courante ...
		$timeout = min($timeout,30); // ne prolongeons pas plus que 30s car cela peut produire des erreur 500 et bloque le refresh
		$max_time = time()+$timeout/2;

		include_spip('inc/minipres');
		@ini_set("zlib.output_compression","0"); // pour permettre l'affichage au fur et a mesure

		switch ($status['etape']){
			case 'init':
				$titre = _T('migration:titre_debut_migration');
				break;
			case 'base':
			case 'basecopie':
				$titre = _T('migration:titre_migration_en_cours_base') . " (".count($status['tables']).") ";
				break;
			case 'fichiers':
			case 'fichierscopie':
				$titre = _T('migration:titre_migration_en_cours_fichiers') . " (".count($status['files']).") ";
				break;
			case 'squelettes':
			case 'squelettescopie':
				$titre = _T('migration:titre_migration_en_cours_squelettes') . " (".array_sum(array_map('count',$status['squelettes'])).") ";
				break;
		}
		$balise_img = chercher_filtre('balise_img');
		$titre .= $balise_img(chemin_image('searching.gif'));
		echo ( install_debut_html($titre));
		// script de rechargement auto sur timeout
		echo http_script("window.setTimeout('location.href=\"".$redirect."\";',".($timeout*1000).")");
		echo "<div style='text-align: left'>\n";

		include_spip('inc/migration');
		$s = lire_migration_vers_status();

		// au premier coup on ne fait rien sauf afficher l'ecran de sauvegarde
		switch ($status['etape']){
			case 'init':
				$status['etape'] = migrer_vers_etape_suivante($status['etape'],$s['quoi']);
				ecrire_fichier($status_file, serialize($status));
				break;
			case 'base':
			case 'basecopie':
				$options = array(
					'callback_progression' => 'migrer_vers_afficher_progres',
					'max_time' => $max_time,
					'no_erase_dest' => lister_tables_noerase(),
					'where' => $status['where']?$status['where']:array(),
					'racine_fonctions_dest' =>'migration/envoi',
					'data_pool' => 200,
				);
				$res = base_copier_tables($status_file, $status['tables'], '', '', $options);
				if ($res) {
					if ($res==="abort"){
						$s = lire_migration_vers_status();
						$s['status'] = 'abort';
						ecrire_migration_status('vers',$s);
					}
					if ($s['status'] != 'abort') {
						$status['etape'] = migrer_vers_etape_suivante($status['etape'],$s['quoi']);
						ecrire_fichier($status_file, serialize($status));
					}
				}
				break;
			case 'fichiers':
			case 'fichierscopie':
				$options = array(
					'callback_progression' => 'migrer_vers_afficher_progres',
					'max_time' => $max_time,
					'racine_fonctions_dest' =>'migration/envoi',
					'data_pool' => 100*1024,
				);
				$res = base_copier_files($status_file, $status['files'],_DIR_IMG,_DIR_IMG, $options);
				if ($res) {
					$status['etape'] = migrer_vers_etape_suivante($status['etape'],$s['quoi']);
					ecrire_fichier($status_file, serialize($status));
				}
				break;
			case 'squelettes':
			case 'squelettescopie':
				$options = array(
					'callback_progression' => 'migrer_vers_afficher_progres',
					'max_time' => $max_time,
					'racine_fonctions_dest' =>'migration/envoi',
					'data_pool' => 100*1024,
				);
				$res = true;
				if ($dir_sources = migration_determiner_dossier_squelette()
				  AND count($dir_sources = explode(':',$dir_sources))){
					foreach($dir_sources as $dir_source)
						$res = base_copier_files($status_file, $status['squelettes'][$dir_source],$dir_source,_DIR_RACINE."squelettes/", $options);
				}
				if ($res) {
					$status['etape'] = migrer_vers_etape_suivante($status['etape'],$s['quoi']);
					ecrire_fichier($status_file, serialize($status));
				}
				break;
		}
		// sortir si on a fini ou abandon demande
		$res = (in_array($status['etape'],array('fini','finition')) OR $s['status']=='abort');

		echo ( "</div>\n");

		if (!$res AND $redirect)
			echo migrer_vers_relance($redirect);
		echo (install_fin_html());
		while (ob_get_level())
			ob_end_flush();
		flush();

		return $res;
	}
}

/**
 * Determiner l'etape suivante en fonction de l'etape courante
 * et des actions demandees
 * @param string $etape
 * @param array $quoi
 * @return string
 */
function migrer_vers_etape_suivante($etape,$quoi){
	$done = false;
	$etapes = array('init','base','basecopie','fichiers','fichierscopie','squelettes','squelettescopie');
	foreach($etapes as $e){
		if ($done AND in_array($e,$quoi))
			return $e;
		if ($e==$etape) $done = true;
	}
	return 'finition';
}

/**
 * Initialiser une migration vers
 * @param string $status_file
 * @param array $tables
 * @param array $files
 * @param array $where
 * @param string $action
 * @return bool|string
 */
function migrer_vers_init($status_file, $tables=null, $files = null,$where=array(),$action='migration_vers'){
	$status_file = _DIR_TMP.basename($status_file).".txt";

	if (lire_fichier($status_file, $status)
		AND $status = unserialize($status)
		AND $status['etape']!=='fini'
		AND filemtime($status_file)>=time()-120) // si le fichier status est trop vieux c'est un abandon
		return _T("migration:erreur_{$action}_deja_en_cours");

	if (!$tables)
		list($tables,) = base_liste_table_for_dump(lister_tables_noexport());
	if (!$files){
		$files = preg_files(_DIR_IMG,'.');
	}
	$squelettes = '';
	$dir_squels = explode(':',migration_determiner_dossier_squelette());
	if (count($dir_squels)){
		$squelettes = array();
		foreach($dir_squels as $dir_squel)
			$squelettes[$dir_squel] = preg_files($dir_squel,'.');
	}
	$status = array('tables'=>$tables,'files'=>$files,'squelettes'=>$squelettes,'where'=>$where);

	$status['etape'] = 'init';
	if (!ecrire_fichier($status_file, serialize($status)))
		return _T('migration:avis_probleme_ecriture_fichier',array('fichier'=>$status_file));

	return true;
}


/**
 * Afficher l'avancement de la copie
 * @staticvar int $etape
 * @param int $courant
 * @param int $total
 * @param string $table
 */
function migrer_vers_afficher_progres($courant,$total,$table) {
	static $etape = 1;
	if (unique($table)) {
		if ($total<0 OR !is_numeric($total)){
			#echo "<br /><strong>".$etape. '. '."</strong>$table ";
			$etape++;
			return;
		}
		else
			echo "<br /><strong>".$etape. '. '."$table</strong> ".($courant?" <i>($courant)</i> ":"");
		$etape++;
	}
	if (is_numeric($total) AND $total>=0)
		echo ". ";
	else
		echo "(". (-intval($total)).")";
	flush();
}

/**
 * Ecrire le js pour relancer la procedure de dump
 * @param string $redirect
 * @return string
 */
function migrer_vers_relance($redirect){
	// si Javascript est dispo, anticiper le Time-out
	return "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout('location.href=\"$redirect\";',300);</script>\n";
}


/**
 * Marquer la procedure de dump comme finie
 * @param string $status_file
 * @param string $action
 * @return void
 */
function migrer_vers_end($status_file, $action=''){
	if (!$status = migrer_vers_lire_status($status_file))
		return;

	$s = lire_migration_vers_status();
	// signifier la fin au site distant
	$end = charger_fonction('end','migration/envoi');
	// passer l'id_auteur qui a fait la migration,
	// il faut s'assurer qu'il est bien webmestre a la fin de la migration ! 
	$s['distant'] = $end($s['status'],$GLOBALS['visiteur_session']['id_auteur'],isset($status['errors'])?$status['errors']:array());
	ecrire_migration_status('vers',$s);

	$status['etape'] = 'fini';
	ecrire_fichier(_DIR_TMP.basename($status_file).".txt", serialize($status));
}

/**
 * Lire le status de migration
 *
 * @param string $status_file
 * @return mixed|string
 */
function migrer_vers_lire_status($status_file) {
	$status_file = _DIR_TMP.basename($status_file).".txt";
	if (!lire_fichier($status_file, $status)
		OR !$status = unserialize($status))
		return '';

	return $status;
}


?>
