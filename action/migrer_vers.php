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

/**
 *
 * On arrive ici depuis le #FORMULAIRE_ASSISTANT_MIGRATION
 * - l'initialisation a ete faite avant redirection
 * - on enchaine sur inc/migrer_vers, qui remplit le dump et renvoie ici a chaque timeout
 * - a chaque coup on relance inc/migrer_vers
 * - lorsque inc/migrer_vers a fini, il retourne true
 * - on renvoie vers exec=migrer_vers pour afficher le resume
 *
 */

include_spip('base/dump');
include_spip('inc/migrer_vers');

/**
 * Migrer par morceaux
 *
 * @param string $arg
 */
function action_migrer_vers_dist($arg=null){
	if (!$arg) {
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	$status_file = $arg;
	$redirect = parametre_url(generer_action_auteur('migrer_vers',$status_file),"step",intval(_request('step')+1),'&');

	// lancer la migration qui va se relancer jusqu'a sa fin
	$migrer_vers = charger_fonction('migrer_vers', 'inc');
	utiliser_langue_visiteur();
	// quand on sort de $export avec true c'est qu'on a fini
	if ($migrer_vers($status_file,$redirect)) {
		migrer_vers_end($status_file,'migrer_vers');
		include_spip('inc/headers');
		echo redirige_formulaire(generer_url_ecrire("migrer_vers_fin",'status='.$status_file,'',true, true));
	}

}

?>