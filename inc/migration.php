<?php
/**
 * Plugin Migration
 * Assistant de Migration d'un site SPIP
 * (c) 2011 Cedric pour Nursit.net
 * Licence GPL
 *
 */

// securite : la cle reste valable 10min apres la reception des dernieres donnees
// ou de son init
if (!defined('_MIGRATION_KEY_PERSISTENCE'))
	define('_MIGRATION_KEY_PERSISTENCE',600);

function lire_migration_status($direction){
	if (!in_array($direction,array('depuis','vers')))
		return false;
	$meta = 'migration_'.$direction.'_status';
	$file = _DIR_TMP.$meta.".txt";
	lire_fichier($file,$s);
	if (!$s)
		return false;

	// verifier le timestamp
	if (!$s = unserialize($s)
		OR !isset($s['status'])
		OR !isset($s['key'])
    OR ($direction=='depuis' AND !isset($s['timestamp']))
	  OR ($direction=='depuis' AND $s['timestamp']<time()-_MIGRATION_KEY_PERSISTENCE)){
		spip_unlink($file);
		return false;
	}

	return $s;
}
function lire_migration_depuis_status(){return lire_migration_status('depuis');}
function lire_migration_vers_status(){return lire_migration_status('vers');}

function ecrire_migration_status($direction, $raz = false){
	if (!in_array($direction,array('depuis','vers')))
		return false;
	$meta = 'migration_'.$direction.'_status';
	$file = _DIR_TMP.$meta.".txt";
	if ($raz===true) {
		spip_unlink($file);
		return false;
	}
	elseif(is_array($raz)){
		$s = $raz;
		ecrire_fichier($file,serialize($s));
	}
	elseif (!$s = lire_migration_status($direction)){
		include_spip('inc/acces');
		$s = array(
			'status'=>'init',
			'timestamp'=>time(),
			'key'=> substr(md5(creer_uniqid()),0,8),
		);
		ecrire_fichier($file,serialize($s));
	}
	return $s;
}
function initialiser_migration_depuis($raz = false){ecrire_migration_status('depuis',$raz?true:false);}
function update_migration_depuis($status){
	// mettre a jour le timestamp pour la continuite
	$status['timestamp'] = time();
	ecrire_migration_status('depuis',$status);
}
function initialiser_migration_vers($url,$key,$quoi=array('base','fichiers','squelettes')){
	ecrire_migration_status('vers',
		array(
			'status'=>'init',
			'target'=>$url,
			'key'=>$key,
			'quoi'=>$quoi
		)
	);
}


function migration_afficher_status($status){
	$s = "?";
	switch($status['status']){
		case 'init':
			$s = _T('migration:status_waiting');
			break;
		case 'end':
		case 'basereverted':
			$s = "[end]".redirige_formulaire(generer_url_ecrire('migrer_depuis_fin'));
			break;
		default:
			if (isset($status['source']))
				$s = _T('migration:status_connected',array('source'=>"<strong>".$status['source']."</strong>"));
			if (isset($status['progress'])){
				if (isset($status['progress']['tables']))
					$status['progress']['tables'] = migration_afficher_status_tables($status['progress']['tables']);
				if (isset($status['progress']['files']))
					$status['progress']['files'] = migration_afficher_status_files($status['progress']['files']);
				$s.= '<br />'.implode('<br />',$status['progress']);
			}
			$s .= '<br />';
			$s .= "[".$status['status']."]";
			break;
	}
	return $s;
}

/**
 * Afficher le statut d'avancement de la copie des tables
 * 
 * @param array $tables
 * @return string
 */
function migration_afficher_status_tables($tables){
	$s = array();
	foreach($tables as $t=>$n){
		$s[] = _T('migration:status_nom_table',array('table'=>$t))._T('migration:status_nb_lignes',array('nb'=>$n));
	}
	return implode('<br />',$s);
}

/**
 * Afficher le statut d'avancement de la copie des fichiers
 *
 * @param array $files
 * @return string
 */
function migration_afficher_status_files($files){
	include_spip('inc/filtres');
	$s = array();
	foreach($files as $f=>$size){
		if (is_numeric($size))
			$s[] = _T('migration:status_nom_fichier',array('fichier'=>$f)).taille_en_octets($size);
		else
			$s[] = _T('migration:status_nom_fichier_refuse',array('fichier'=>$f));
	}
	return implode('<br />',$s);
}

/**
 * Signer un contenu avec une cle
 * Comme la cle n'est valable que 5min, cela laisse peu de temps pour la casser
 * et un sha1 suffit
 * 
 * @param string $action
 * @param string $key
 * @return string
 */
function migration_signer_data($action, $key) {
	if (function_exists('sha1'))
		return sha1($action . $key);
	else
		return md5($action . $key);
}

/**
 * Encoder un contexte, le signer avec une cle, le crypter
 * avec la cle de migration, le gziper si possible...
 * l'entree peut etre serialisee
 *
 * @param array|string $c
 * @param string $key
 * @return string
 */
function migration_encoder_data($c, $key) {
	if (is_string($c)
	AND !is_null(@unserialize($c)))
		$c = unserialize($c);

	$cle = migration_signer_data(is_array($c)?serialize($c):$c, $key);
	$c = serialize(array($c,$cle));

	$c = gzdeflate($c);
	$c = migration_xor($c,$key);
	$c = base64_encode($c);

	return $c;
}

/**
 * la procedure inverse de migration_encoder_data()
 *
 * @param string $c
 * @param string $key
 * @return string|bool
 */
function migration_decoder_data($c, $key) {
	$c = @base64_decode($c);
	$c = migration_xor($c, $key);
	$c = @gzinflate($c);
	list($env, $cle) = @unserialize($c);

	if ($cle == migration_signer_data(is_array($env)?serialize($env):$env, $key))
		return $env;
	return false;
}

/**
 * encrypter/decrypter un message
 * http://www.php.net/manual/fr/language.operators.bitwise.php#81358
 *
 * @param string $message
 * @param string $key
 * @return string
 */
function migration_xor($message, $key){

	$keylen = strlen($key);
	$messagelen = strlen($message);
	for($i=0; $i<$messagelen; $i++)
		$message[$i] = ~($message[$i]^$key[$i%$keylen]);

	return $message;
}

/**
 * Si le site local est sqlite, faisons une copie de la base
 * avant la migration, ca peut toujours servir
 * 
 * @return bool
 */
function migration_backup_base_si_possible(){
	// si jamais la base est sqlite, faire une copie de backup
	// au cas ou le transfert foire
	include_spip('base/abstract_sql');
	sql_version();
	if (strncmp($GLOBALS['connexions'][0]['type'],'sqlite',6)==0){
		if ($db =$GLOBALS['connexions'][0]['db']
			AND is_file($f = _DIR_DB . $db . '.sqlite')) {
			$s = lire_migration_depuis_status();
			@copy($f,_DIR_DB. ($g=$db.".sqlite.migration.backup"));
			if (@file_exists(_DIR_DB.$g)){
				$s['backup'] = $g;
				ecrire_migration_status('depuis',$s);
				spip_log("base $f copiee dans $g avant migration",'migration');
				return true;
			}
		}
	}
	return false;
}

/**
 * Si la migration a echouee, peut on revenir a une base que l'on avait sauvegarde ?
 * (cas sqlite uniquement)
 * 
 * @return bool
 */
function migration_restore_base_si_possible(){
	spip_log("tentative de restauration de la base",'migration');
	// si jamais la base est sqlite, et qu'on a un backup
	// le restaurer
	include_spip('base/abstract_sql');
	sql_version();
	if (strncmp($GLOBALS['connexions'][0]['type'],'sqlite',6)==0){
		if ($db =$GLOBALS['connexions'][0]['db']
			AND is_file($f = _DIR_DB . $db . '.sqlite')) {
			$s = lire_migration_depuis_status();
			spip_log("base SQLITE, le status indique backup=".$s['backup'],'migration');
			if ($g = $s['backup']
				AND @file_exists(_DIR_DB.$g)){
				spip_log("base $g restauree dans $f suite a l'echec de la migration",'migration');
				@copy(_DIR_DB.$g,$f);
				return true;
			}
		}
	}
	return false;
}

/**
 * Verifier que le fichier est d'un type autorise dans IMG/
 * ie dans la table des documents
 * Les fichiers non autorises ne seront pas transferes
 *
 * @param string $file
 * @param bool $strict
 * @return bool
 */
function migration_type_fichier_autorise($file, $strict=true){
	// pas de bras, pas de chocolat
	if (!preg_match(',\.([a-z0-9]+)$,i', $file, $rext))
		return false;

	$extension = strtolower($rext[1]);

	// type autorise dans les documents de SPIP ? ok
	if (sql_fetsel("extension", "spip_types_documents", "extension=" . sql_quote($extension)))
		return $extension;

	// type supplementaire utilise et autorise dans les squelettes
	if (!$strict
	    AND in_array($extension,
		      array('js','ttf','otf','eot','svg','woff','ico')
		  ))
		return $extension;

	return false;
}


function migration_affiche_champs_ignores($ignores){
	if (!is_array($ignores) OR !count($ignores))
		return '';
	$res = array();
	foreach($ignores as $table=>$champs){
		$res[] = "Table $table&nbsp;: ".implode(', ',$champs);
	}
	return implode('<br />',$res);
}

function migration_affiche_fichiers_ignores($ignores){
	if (!is_array($ignores) OR !count($ignores))
		return '';
	$res = array();
	foreach($ignores as $file=>$dummy){
		$res[] = $file;
	}
	return implode('<br />',$res);
}

/**
 * Determiner le ou les dossies squelettes valides :
 * on explode, verifie leur existence, et les renvois valides,
 * dans l'ordre de copie (inverse de l'ordre du path)
 *
 * @return string
 */
function migration_determiner_dossier_squelette(){
	$skels = ((isset($GLOBALS['dossier_squelettes']) AND $GLOBALS['dossier_squelettes'])?$GLOBALS['dossier_squelettes']:_DIR_RACINE.'squelettes');
	$skels = explode(':',$skels);
	$skels = array_reverse($skels);
	foreach($skels as $k=>$s){
		if (_DIR_RACINE AND strncmp($s,_DIR_RACINE,strlen(_DIR_RACINE))!==0)
			$s = _DIR_RACINE . $s;
		if (is_dir($s))
			$skels[$k] = rtrim($s,"/")."/";
		else
			unset($skels[$k]);
	}
	return count($skels)?implode(':',$skels):'';
}