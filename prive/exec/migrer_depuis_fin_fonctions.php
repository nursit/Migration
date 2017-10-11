<?php
include_spip('inc/migration');

function verifier_auteur_session() {
	$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	$row = sql_fetsel('id_auteur,login,nom,statut,webmestre', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
	if (!$row
		or $row['login']!==$GLOBALS['visiteur_session']['login']
		or $row['statut']!==$GLOBALS['visiteur_session']['statut']
		or $row['webmestre']!==$GLOBALS['visiteur_session']['webmestre']
		or $row['nom']!==$GLOBALS['visiteur_session']['nom']) {
		return '';
	}

	if ($row['webmestre']!=='oui') {
			return '';
	}

	return ' ';
}
