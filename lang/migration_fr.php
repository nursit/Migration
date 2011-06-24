<?php
// -- This is a SPIP language file  --  Ceci est un fichier langue de SPIP
if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(
	'attention_session_perimee_deconnecter' => 'Attention : le login avec lequel vous êtes connecté n\'existe plus. Vous devez vous deconnecter puis vous identifier à nouveau avec le login présent dans le site que vous venez d\'importer.',
	'bouton_continuer' => 'Continuer',
	'bouton_revenir' => 'Revenir',
	'bouton_commencer_migration' => 'Commencer la migration',

	'texte_introduction_assistant' => 'Vous pouvez utiliser l\'assistant de migration
			pour transferer les données de votre site SPIP
			vers une autre installation de SPIP. Vous pouvez par exemple transférer :',
	'texte_liste_base' => 'la base de données',
	'texte_liste_documents' => 'les documents joints',
	'texte_liste_squelettes' => 'les squelettes',

	'titre_assistant_migration'=>'Assistant de migration',
	'titre_debut_migration' => 'Début de la migration',
	'titre_etape_intoduction' => 'Introduction',
	'titre_etape_methode' => 'Méthode de migration',
	'titre_comment_transferer' => 'Comment voulez-vous transférer vos données ?',
	'titre_migration_status' => 'Avancement&nbsp;:',
	'titre_etape_migration_depuis' => 'Migration depuis un autre site',
	'titre_etape_migration_vers' => 'Connexion à l\'autre site',
	'titre_migration_en_cours_base' => 'Migration des tables en cours',
	'titre_migration_en_cours_fichiers' => 'Migration des documents en cours',
	'titre_migration_en_cours_squelettes' => 'Migration des squelettes en cours',
	'titre_echec_migration' => 'Echec de la migration',
	'titre_abandon_migration' => 'Abandon de la migration',
	'titre_fin_migration'=>'Migration terminée !',

	'resultat_erreur_migration' => 'Une erreur est survenue lors de la migration.',
	'resultat_derniere_reponse_distante'=>'La dernière réponse du serveur distant a été&nbsp;:',
	'resultat_succes_migration_depuis' => 'La migration <b>depuis</b> le site distant a été achevée avec succés.',
	'resultat_succes_migration_vers' => 'La migration <b>vers</b> le site distant a été achevée avec succés.',
	'resultat_champs_ignores_distant' => 'Le serveur distant a ignoré les champs suivants&nbsp;:',
	'resultat_fichiers_ignores_distant' => 'Le serveur distant a refusé les fichiers suivants&nbsp;:',
	'resultat_champs_ignores' => 'Les champs suivants ont été ignorés&nbsp;:',
	'resultat_fichiers_ignores' => 'Les fichiers suivants ont été refusés&nbsp;:',
	'resultat_backup_retabli' => 'Votre base de données a été rétablie dans son état avant la tentative de migration.',

	'label_direction_depuis' => 'Importer depuis un autre site SPIP',
	'label_url_cible' => 'URL de migration du site cible',
	'label_migration_key' => 'Clé de migration',
	'explications_direction_depuis' => 'Transférer sur ce site des données provenant d\'un site SPIP distant. Ce site doit être accessible par Internet depuis le site distant.',
	'label_direction_vers' => 'Exporter vers un autre site SPIP',
	'explications_direction_vers' => 'Transférer des données de ce site vers un autre site SPIP. Le site SPIP distant doit être accessible par Internet depuis ce site.',
	'explication_connexion_vers' => 'Sur le site de destination, lancez l\'assistant de migration et sélectionnez la migration <i>Depuis un autre site SPIP</i>.
	Renseignez ci-dessous l\'URL et la clé de migration qui vous sont indiquées.',
	'explications_dossier_squelettes' => 'Si le squelette du site est dans un dossier qui n\'apparaît pas ci-dessus,
	ajoutez le à la variable <tt>$dossier_squelettes</tt> dans votre fichier <tt>mes_options.php</tt>
	<a class="spip_out" href="http://www.spip.net/fr_article1825.html#doss_squel">comme indiqué dans la documentation de SPIP</a>.',
	'label_quoi_base' => 'Envoyer la base de données',
	'label_quoi_fichiers' => 'Envoyer les documents de <tt>@dir@</tt>',
	'label_quoi_squelettes' => 'Envoyer les squelettes de <tt>@dir@</tt>',

	'erreur_echec_connexion_init' => 'Impossible de se connecter au site distant. Veuillez verifier l\'URL de migration fournie par le site distant.',
	'erreur_echec_connexion_version' => 'La version de ce site est trop récente pour migrer <b>vers</b> le site cible',
	'erreur_droits_webmestre' => 'Vous devez avoir les droits de webmestre sur le site pour utiliser l\'assistant de migration.',
	'erreur_direction_obligatoire' => 'Choisissez dans quelle direction vous souhaitez transférer vos données',
	'erreur_choisissez_quoi' => 'Indiquez ce que vous voulez transférer vers l\'autre site',
	'erreur_migration_vers_deja_en_cours' => 'Il y a déjà une migration vers un autre site en cours !',
	'erreur_url_incorrecte' => 'Le format de l\'URL de migration est incorrect. Vérifiez l\'URL fournie par le site distant.',
	'message_connexion_ok' => 'Connexion OK',

	'status_waiting' => 'En attente de connexion du site distant',
	'status_connected' => 'Connexion depuis @source@',
	'status_nom_table' => 'Table @table@&nbsp;: ',
	'status_nom_fichier' => 'Fichier @fichier@&nbsp;: ',
	'status_nom_fichier_refuse' => 'Fichier <i>@fichier@</i>&nbsp;: <strong>refusé</strong>',
	'status_nb_lignes' => '@nb@ ligne(s)',

	'tables_transferees' => 'Tables transférées',
	'fichiers_transferes' => 'Fichiers transférés',

	'voir_le_detail' => 'Voir le détail',

);

?>
