<?php
// -- This is a SPIP language file  --  Ceci est un fichier langue de SPIP
if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(
	'attention_session_perimee_deconnecter' => 'Attention : le login avec lequel vous êtes connect&eacute; n\'existe plus. Vous devez vous deconnecter puis vous identifier &agrave; nouveau avec le login pr&eacute;sent dans le site que vous venez d\'importer.',
	'bouton_continuer' => 'Continuer',
	'bouton_revenir' => 'Revenir',
	'bouton_commencer_migration' => 'Commencer la migration',

	'texte_introduction_assistant' => 'Vous pouvez utiliser l\'assistant de migration
			pour transferer les donn&eacute;es de votre site SPIP
			vers une autre installation de SPIP. Vous pouvez par exemple transf&eacute;rer :',
	'texte_liste_base' => 'la base de donn&eacute;es',
	'texte_liste_documents' => 'les documents joints',
	'texte_liste_squelettes' => 'les squelettes',

	'titre_assistant_migration'=>'Assistant de migration',
	'titre_debut_migration' => 'D&eacute;but de la migration',
	'titre_etape_intoduction' => 'Introduction',
	'titre_etape_methode' => 'M&eacute;thode de migration',
	'titre_comment_transferer' => 'Comment voulez-vous transf&eacute;rer vos donn&eacute;es ?',
	'titre_migration_status' => 'Avancement&nbsp;:',
	'titre_etape_migration_depuis' => 'Migration depuis un autre site',
	'titre_etape_migration_vers' => 'Connexion &agrave; l\'autre site',
	'titre_migration_en_cours_base' => 'Migration des tables en cours',
	'titre_migration_en_cours_fichiers' => 'Migration des documents en cours',
	'titre_migration_en_cours_squelettes' => 'Migration des squelettes en cours',
	'titre_echec_migration' => 'Echec de la migration',
	'titre_abandon_migration' => 'Abandon de la migration',
	'titre_fin_migration'=>'Migration termin&eacute;e !',

	'resultat_erreur_migration' => 'Une erreur est survenue lors de la migration.',
	'resultat_derniere_reponse_distante'=>'La derni&egrave;re r&eacute;ponse du serveur distant a &eacute;t&eacute;&nbsp;:',
	'resultat_succes_migration_depuis' => 'La migration <b>depuis</b> le site distant a &eacute;t&eacute; achev&eacute;e avec succ&eacute;s.',
	'resultat_succes_migration_vers' => 'La migration <b>vers</b> le site distant a &eacute;t&eacute; achev&eacute;e avec succ&eacute;s.',
	'resultat_champs_ignores_distant' => 'Le serveur distant a ignor&eacute; les champs suivants&nbsp;:',
	'resultat_fichiers_ignores_distant' => 'Le serveur distant a refus&eacute; les fichiers suivants&nbsp;:',
	'resultat_champs_ignores' => 'Les champs suivants ont &eacute;t&eacute; ignor&eacute;s&nbsp;:',
	'resultat_fichiers_ignores' => 'Les fichiers suivants ont &eacute;t&eacute; refus&eacute;s&nbsp;:',
	'resultat_backup_retabli' => 'Votre base de donn&eacute;es a &eacute;t&eacute; r&eacute;tablie dans son &eacute;tat avant la tentative de migration.',

	'label_direction_depuis' => 'Importer depuis un autre site SPIP',
	'label_url_cible' => 'URL de migration du site cible',
	'label_migration_key' => 'Cl&eacute; de migration',
	'explications_direction_depuis' => 'Transf&eacute;rer sur ce site des donn&eacute;es provenant d\'un site SPIP distant. Ce site doit être accessible par Internet depuis le site distant.',
	'label_direction_vers' => 'Exporter vers un autre site SPIP',
	'explications_direction_vers' => 'Transf&eacute;rer des donn&eacute;es de ce site vers un autre site SPIP. Le site SPIP distant doit être accessible par Internet depuis ce site.',
	'explication_connexion_vers' => 'Sur le site de destination, lancez l\'assistant de migration et s&eacute;lectionnez la migration <i>Depuis un autre site SPIP</i>.
	Renseignez ci-dessous l\'URL et la cl&eacute; de migration qui vous sont indiqu&eacute;es.',
	'explications_dossier_squelettes' => 'Si le squelette du site est dans un dossier qui n\'apparaît pas ci-dessus,
	ajoutez le &agrave; la variable <tt>$dossier_squelettes</tt> dans votre fichier <tt>mes_options.php</tt>
	<a class="spip_out" href="http://www.spip.net/fr_article1825.html#doss_squel">comme indiqu&eacute; dans la documentation de SPIP</a>.',
	'label_quoi_base' => 'Envoyer la base de donn&eacute;es',
	'label_quoi_fichiers' => 'Envoyer les documents de <tt>@dir@</tt>',
	'label_quoi_squelettes' => 'Envoyer les squelettes de <tt>@dir@</tt>',

	'erreur_echec_connexion_init' => 'Impossible de se connecter au site distant. Veuillez verifier l\'URL de migration fournie par le site distant.',
	'erreur_echec_connexion_version' => 'La version de ce site est trop r&eacute;cente pour migrer <b>vers</b> le site cible',
	'erreur_droits_webmestre' => 'Vous devez avoir les droits de webmestre sur le site pour utiliser l\'assistant de migration.',
	'erreur_direction_obligatoire' => 'Choisissez dans quelle direction vous souhaitez transf&eacute;rer vos donn&eacute;es',
	'erreur_choisissez_quoi' => 'Indiquez ce que vous voulez transf&eacute;rer vers l\'autre site',
	'erreur_migration_vers_deja_en_cours' => 'Il y a d&eacute;j&agrave; une migration vers un autre site en cours !',
	'erreur_url_incorrecte' => 'Le format de l\'URL de migration est incorrect. V&eacute;rifiez l\'URL fournie par le site distant.',
	'message_connexion_ok' => 'Connexion OK',

	'status_waiting' => 'En attente de connexion du site distant',
	'status_connected' => 'Connexion depuis @source@',
	'status_nom_table' => 'Table @table@&nbsp;: ',
	'status_nom_fichier' => 'Fichier @fichier@&nbsp;: ',
	'status_nom_fichier_refuse' => 'Fichier <i>@fichier@</i>&nbsp;: <strong>refus&eacute;</strong>',
	'status_nb_lignes' => '@nb@ ligne(s)',

	'tables_transferees' => 'Tables transf&eacute;r&eacute;es',
	'fichiers_transferes' => 'Fichiers transf&eacute;r&eacute;s',

	'voir_le_detail' => 'Voir le d&eacute;tail',

);

?>
