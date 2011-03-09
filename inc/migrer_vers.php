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
		$max_time = time()+$timeout/2;

		include_spip('inc/minipres');
		@ini_set("zlib.output_compression","0"); // pour permettre l'affichage au fur et a mesure

		$titre = _T('migration:titre_migration_en_cours') . " (".count($status['tables']).") ";
		$balise_img = chercher_filtre('balise_img');
		$titre .= $balise_img(chemin_image('searching.gif'));
		echo ( install_debut_html($titre));
		// script de rechargement auto sur timeout
		echo http_script("window.setTimeout('location.href=\"".$redirect."\";',".($timeout*1000).")");
		echo "<div style='text-align: left'>\n";

		// au premier coup on ne fait rien sauf afficher l'ecran de sauvegarde
		if (_request('step')) {
			$options = array(
				'callback_progression' => 'migration_vers_afficher_progres',
				'max_time' => $max_time,
				'no_erase_dest' => lister_tables_noerase(),
				'where' => $status['where']?$status['where']:array(),
				'racine_fonctions_dest' =>'migration/envoi',
			);
			$res = base_copier_tables($status_file, $status['tables'], '', '', $options);
		}

		echo ( "</div>\n");

		if (!$res AND $redirect)
			echo migrer_vers_relance($redirect);
		echo (install_fin_html());
		ob_end_flush();
		flush();

		return $res;
	}
}


/**
 * Initialiser une migration vers
 * @param string $status_file
 * @param array $tables
 * @param array $where
 * @return bool/string
 */
function migration_vers_init($status_file, $tables=null, $where=array(),$action='migration_vers'){
	$status_file = _DIR_TMP.basename($status_file).".txt";

	if (lire_fichier($status_file, $status)
		AND $status = unserialize($status)
		AND $status['etape']!=='fini'
		AND filemtime($status_file)>=time()-120) // si le fichier status est trop vieux c'est un abandon
		return _T("migration:erreur_{$action}_deja_en_cours");

	if (!$tables)
		list($tables,) = base_liste_table_for_dump(lister_tables_noexport());
	$status = array('tables'=>$tables,'where'=>$where);

	$status['etape'] = 'init';
	if (!ecrire_fichier($status_file, serialize($status)))
		return _T('migration:avis_probleme_ecriture_fichier',array('fichier'=>$status_file));

	return true;
}


/**
 * Afficher l'avancement de la copie
 * @staticvar int $etape
 * @param <type> $courant
 * @param <type> $total
 * @param <type> $table
 */
function migration_vers_afficher_progres($courant,$total,$table) {
	static $etape = 1;
	if (unique($table)) {
		if ($total<0 OR !is_numeric($total))
			echo "<br /><strong>".$etape. '. '."</strong>$table ";
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
 * @return <type>
 */
function migrer_vers_end($status_file, $action=''){
	if ($status = migrer_vers_lire_status($status_file))
		return;

	switch($action) {
		#case 'restaurer':
			// supprimer la structure qui etait stockee dans le dump
		#	sql_delete('spip_meta',"nom='dump_structure_temp'");
		#	break;
		case 'sauvegarder':
			// stocker dans le dump la structure de la base source
			$structure = array();
			foreach($status['tables_copiees'] as $t=>$n)
				$structure[$t] = sql_showtable($t,true);
			dump_serveur($status['connect']);
			spip_connect('dump');
			sql_insertq('spip_meta',array('nom'=>'dump_structure_temp','valeur'=>serialize($structure),'impt'=>'non'),array(),'dump');
			break;
	}

	$status['etape'] = 'fini';
	ecrire_fichier($status_file, serialize($status));
}


function migrer_vers_lire_status($status_file) {
	$status_file = _DIR_TMP.basename($status_file).".txt";
	if (!lire_fichier($status_file, $status)
		OR !$status = unserialize($status))
		return '';

	return $status;
}


?>