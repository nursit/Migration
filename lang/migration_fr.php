<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(
	'bouton_continuer' => 'Continuer',
	'bouton_revenir' => 'Revenir',
	'bouton_commencer_migration' => 'Commencer la migration',


	'titre_assistant_migration'=>'Assistant de migration',
	'titre_etape_intoduction' => 'Introduction',
	'titre_etape_methode' => 'Méthode de migration',
	'titre_comment_transferer' => 'Comment voulez-vous transférer vos données ?',
	'titre_migration_status' => 'Avancement&nbsp;:',
	'titre_etape_migration_depuis' => 'Migration depuis un autre site',
	'titre_etape_migration_vers' => 'Connexion à l\'autre site',
	'titre_migration_en_cours_base' => 'Migration des tables en cours',
	'titre_migration_en_cours_fichiers' => 'Migration des fichiers en cours',

	'label_direction_depuis' => 'Depuis un autre site SPIP',
	'label_url_cible' => 'URL du site cible',
	'label_migration_key' => 'Clé de migration',
	'explications_direction_depuis' => 'Transférer sur ce site des données provenant d\'un site SPIP distant. Ce site doit être accessible par Internet depuis le site distant.',
	'label_direction_vers' => 'Vers un autre site SPIP',
	'explications_direction_vers' => 'Transférer des données de ce site vers un autre site SPIP. Le site SPIP distant doit être accessible par Internet depuis ce site.',
	'explication_connexion_vers' => 'Sur le site de destination, lancez l\'assistant de migration et sélectionnez la migration <i>Depuis un autre site SPIP</i>.
	Renseignez ci-dessous l\'URL et la clé de migration qui vous sont indiquées.',
	'label_quoi_base' => 'Envoyer la base de données',
	'label_quoi_fichiers' => 'Envoyer les documents de <i>IMG/</i>',

	'erreur_echec_connexion_init' => 'Impossible de se connecter au site distant. Veuillez verifier l\'URL du site et la clé de migration.',
	'erreur_droits_webmestre' => 'Vous devez avoir les droits de webmestre sur le site pour utiliser l\'assistant de migration.',
	'erreur_direction_obligatoire' => 'Choisissez dans quelle direction vous souhaitez transférer vos données',
	'erreur_choisissez_quoi' => 'Indiquez ce que vous voulez transférer vers l\'autre site',
	'message_connexion_ok' => 'Connexion OK',

	'status_waiting' => 'En attente de connexion du site distant',
	'status_connected' => 'Connexion depuis @source@',

);

?>
