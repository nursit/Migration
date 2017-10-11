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

if (!defined('_ECRIRE_INC_VERSION')) { return;
}

if (!defined('_INC_DISTANT_VERSION_HTTP')) { define('_INC_DISTANT_VERSION_HTTP', 'HTTP/1.0');
}
if (!defined('_INC_DISTANT_CONTENT_ENCODING')) { define('_INC_DISTANT_CONTENT_ENCODING', 'gzip');
}
if (!defined('_INC_DISTANT_USER_AGENT')) { define('_INC_DISTANT_USER_AGENT', 'SPIP-' .$GLOBALS['spip_version_affichee']. ' (' .$GLOBALS['home_server']. ')');
}

// les fonctions non buggues natives du core
include_spip('inc/distant');

//
// Recupere une page sur le net
// et au besoin l'encode dans le charset local
//
// options : get_headers si on veut recuperer les entetes
// taille_max : arreter le contenu au-dela (0 = seulement les entetes ==>HEAD)
// Par defaut taille_max = 1Mo.
// datas, une chaine ou un tableau pour faire un POST de donnees
// boundary, pour forcer l'envoi par cette methode
// et refuser_gz pour forcer le refus de la compression (cas des serveurs orthographiques)
// date_verif, un timestamp unix pour arreter la recuperation si la page distante n'a pas ete modifiee depuis une date donnee
// uri_referer, preciser un referer different
// Le second argument ($trans) :
// * si c'est une chaine longue, alors c'est un nom de fichier
//   dans lequel on ecrit directement la page
// * si c'est true/null ca correspond a une demande d'encodage/charset
// http://doc.spip.org/@migration_recuperer_page
function migration_recuperer_page(
	$url,
	$trans = false,
	$get_headers = false,
	$taille_max = null,
	$datas = '',
	$boundary = '',
	$refuser_gz = false,
	$date_verif = '',
	$uri_referer = ''
) {
	$gz = false;

	// $copy = copier le fichier ?
	$copy = (is_string($trans) and strlen($trans) > 5); // eviter "false" :-)

	if (is_null($taille_max)) {
		$taille_max = $copy ? _COPIE_LOCALE_MAX_SIZE : 1048576;
	}

	// Accepter les URLs au format feed:// ou qui ont oublie le http://
	$url = preg_replace(',^feed://,i', 'http://', $url);
	if (!preg_match(',^[a-z]+://,i', $url)) { $url = 'http://'.$url;
	}

	if ($taille_max == 0) {
		$get = 'HEAD';
	} else {
		$get = 'GET';
	}

	if (!empty($datas)) {
		$get = 'POST';
		list($type, $postdata) = prepare_donnees_post($datas, $boundary);
		$datas = $type . 'Content-Length: '.strlen($postdata)."\r\n\r\n".$postdata;
	}

	// dix tentatives maximum en cas d'entetes 301...
	for ($i=0; $i<10; $i++) {
		$url = migration_recuperer_lapage($url, $trans, $get, $taille_max, $datas, $refuser_gz, $date_verif, $uri_referer);
		if (!$url) { return false;
		}
		if (is_array($url)) {
			list($headers, $result) = $url;
			return ($get_headers ? $headers."\n" : '').$result;
		} else {
			spip_log("recuperer page recommence sur $url");
		}
	}
}

// args comme ci-dessus (presque)
// retourne l'URL en cas de 301, un tableau (entete, corps) si ok, false sinon
// si $trans est null -> on ne veut que les headers
// si $trans est une chaine, c'est un nom de fichier pour ecrire directement dedans
// http://doc.spip.org/@migration_recuperer_lapage
function migration_recuperer_lapage($url, $trans = false, $get = 'GET', $taille_max = 1048576, $datas = '', $refuser_gz = false, $date_verif = '', $uri_referer = '') {
	// $copy = copier le fichier ?
	$copy = (is_string($trans) and strlen($trans) > 5); // eviter "false" :-)

	// si on ecrit directement dans un fichier, pour ne pas manipuler
	// en memoire refuser gz
	if ($copy) {
		$refuser_gz = true;
	}

	// ouvrir la connexion et envoyer la requete et ses en-tetes
	list($f, $fopen) = migration_init_http($get, $url, $refuser_gz, $uri_referer, $datas, _INC_DISTANT_VERSION_HTTP, $date_verif);
	if (!$f) {
		spip_log("ECHEC migration_init_http $url");
		return false;
	}

	// Sauf en fopen, envoyer le flux d'entree
	// et recuperer les en-tetes de reponses
	if ($fopen) {
		$headers = '';
	} else {
		$headers = migration_recuperer_entetes($f, $date_verif);
		if (is_numeric($headers)) {
			fclose($f);
			// Chinoisierie inexplicable pour contrer
			// les actions liberticides de l'empire du milieu
			if ($headers) {
				spip_log("HTTP status $headers pour $url");
				return false;
			} elseif ($result = @file_get_contents($url)) {
				return array('', $result);
			} else {
				return false;
			}
		}
		if (!is_array($headers)) { // cas Location
			fclose($f);
			include_spip('inc/filtres');
			return suivre_lien($url, $headers);
		}
		$headers = join('', $headers);
	}

	if ($trans === null) { return array($headers, '');
	}

	// s'il faut deballer, le faire via un fichier temporaire
	// sinon la memoire explose pour les gros flux

	$gz = preg_match(",\bContent-Encoding: .*gzip,is", $headers) ?
		(_DIR_TMP.md5(uniqid(mt_rand())).'.tmp.gz') : '';

#	spip_log("entete ($trans $copy $gz)\n$headers");
	$result = migration_recuperer_body($f, $taille_max, $gz ? $gz : ($copy ? $trans : ''));
	fclose($f);
	if (!$result) { return array($headers, $result);
	}

	// Decompresser au besoin
	if ($gz) {
		$result = join('', gzfile($gz));
		supprimer_fichier($gz);
	}
	// Faut-il l'importer dans notre charset local ?
	if ($trans === true) {
		include_spip('inc/charsets');
		$result = transcoder_page($result, $headers);
	}

	return array($headers, $result);
}

// http://doc.spip.org/@migration_recuperer_body
function migration_recuperer_body($f, $taille_max = 1048576, $fichier = '') {
	$taille = 0;
	$result = '';
	if ($fichier) {
		$fp = spip_fopen_lock($fichier, 'w', LOCK_EX);
		if (!$fp) { return false;
		}
		$result = 0; // on renvoie la taille du fichier
	}
	while (!feof($f) and $taille<$taille_max) {
		$res = fread($f, 16384);
		$taille += strlen($res);
		if ($fp) {
			fwrite($fp, $res);
			$result = $taille;
		} else {
			$result .= $res;
		}
	}
	if ($fp) {
		spip_fclose_unlock($fp);
	}
	return $result;
}

// Lit les entetes de reponse HTTP sur la socket $f et retourne:
// la valeur (chaine) de l'en-tete Location si on l'a trouvee
// la valeur (numerique) du statut si different de 200, notamment Not-Modified
// le tableau des entetes dans tous les autres cas

// http://doc.spip.org/@migration_recuperer_entetes
function migration_recuperer_entetes($f, $date_verif = '') {
	$s = @trim(fgets($f, 16384));

	if (!preg_match(',^HTTP/[0-9]+\.[0-9]+ ([0-9]+),', $s, $r)) {
		return 0;
	}
	$status = intval($r[1]);
	$headers = array();
	$not_modif = $location = false;
	while ($s = trim(fgets($f, 16384))) {
		$headers[]= $s."\n";
		preg_match(',^([^:]*): *(.*)$,i', $s, $r);
		list(,$d, $v) = $r;
		if (strtolower(trim($d)) == 'location' and $status >= 300 and $status < 400) {
			$location = $v;
		} elseif ($date_verif and ($d == 'Last-Modified')) {
			if ($date_verif>=strtotime($v)) {
				//Cas ou la page distante n'a pas bouge depuis
				//la derniere visite
				$not_modif = true;
			}
		}
	}

	if ($location) { return $location;
	}
	if ($status != 200 or $not_modif) { return $status;
	}
	return $headers;
}

//
// Lance une requete HTTP avec entetes
// retourne le descripteur sur lequel lire la reponse
//
// http://doc.spip.org/@migration_init_http
function migration_init_http($method, $url, $refuse_gz = false, $referer = '', $datas = '', $vers = 'HTTP/1.0', $date = '') {
	$user = $via_proxy = $proxy_user = '';
	$fopen = false;

	$t = @parse_url($url);
	$host = $t['host'];
	if ($t['scheme'] == 'http') {
		$scheme = 'http';
$noproxy = '';
	} elseif ($t['scheme'] == 'https') {
		$scheme = 'ssl';
$noproxy = 'ssl://';
		if (!isset($t['port']) || !($port = $t['port'])) { $t['port'] = 443;
		}
	} else {
		$scheme = $t['scheme'];
$noproxy = $scheme.'://';
	}
	if (isset($t['user'])) {
		$user = array($t['user'], $t['pass']);
	}

	if (!isset($t['port']) || !($port = $t['port'])) { $port = 80;
	}
	if (!isset($t['path']) || !($path = $t['path'])) { $path = '/';
	}
	if (@$t['query']) { $path .= '?' .$t['query'];
	}

	$f = migration_lance_requete($method, $scheme, $user, $host, $path, $port, $noproxy, $refuse_gz, $referer, $datas, $vers, $date);
	if (!$f) {
	  // fallback : fopen
		if (!_request('tester_proxy')) {
			$f = @fopen($url, 'rb');
			spip_log("connexion vers $url par simple fopen");
			$fopen = true;
		} else {
			$f = false;// echec total
		}
	}

	return array($f, $fopen);
}

// http://doc.spip.org/@migration_lance_requete
function migration_lance_requete($method, $scheme, $user, $host, $path, $port, $noproxy, $refuse_gz = false, $referer = '', $datas = '', $vers = 'HTTP/1.0', $date = '') {

	$proxy_user = '';
	$http_proxy = need_proxy($host);
	if ($user) { $user = urlencode($user[0]).':'.urlencode($user[1]);
	}

	if ($http_proxy) {
		$path = "$scheme://"
			. (!$user ? '' : "$user@")
			. "$host" . (($port != 80) ? ":$port" : '') . $path;
		$t2 = @parse_url($http_proxy);
		$first_host = $t2['host'];
		if (!($port = $t2['port'])) { $port = 80;
		}
		if ($t2['user']) {
			$proxy_user = base64_encode($t2['user'] . ':' . $t2['pass']);
		}
	} else {
		$first_host = $noproxy.$host;
	}

	$f = @fsockopen($first_host, $port);
	spip_log("Recuperer $path sur $first_host:$port par $f");
	if (!$f) { return false;
	}

	$site = $GLOBALS['meta']['adresse_site'];

	$req = "$method $path $vers\r\n"
	. "Host: $host\r\n"
	. 'User-Agent: ' . _INC_DISTANT_USER_AGENT . "\r\n"
	. ($refuse_gz ? '' : ('Accept-Encoding: ' . _INC_DISTANT_CONTENT_ENCODING . "\r\n"))
	. (!$site ? '' : "Referer: $site/$referer\r\n")
	. (!$date ? '' : 'If-Modified-Since: ' . (gmdate('D, d M Y H:i:s', $date)  ." GMT\r\n"))
	. (!$user ? '' : ('Authorization: Basic ' . base64_encode($user) ."\r\n"))
	. (!$proxy_user ? '' : "Proxy-Authorization: Basic $proxy_user\r\n")
	. (!strpos($vers, '1.1') ? '' : "Keep-Alive: 300\r\nConnection: keep-alive\r\n");

#	spip_log("Requete\n$req");
	fputs($f, $req);
	fputs($f, $datas ? $datas : "\r\n");
	return $f;
}
