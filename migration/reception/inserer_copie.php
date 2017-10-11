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
 * fonction d'insertion en base lors de la copie de base a base
 *
 * @param string $table
 * @param array $row
 * @param array $desc_dest
 * @param string $serveur_dest
 * @return int/bool
 */
function migration_reception_inserer_copie_dist($status, $data) {
	include_spip('base/dump');

	$status['status'] = 'copier';
	if (!isset($status['compteurs'])) {
		$status['compteurs'] = array();
	}
	if (!isset($status['compteurs']['table'])) {
		$status['compteurs']['table'] = array();
	}
	if (!isset($status['compteurs']['table'][$data['table']])) {
		$status['compteurs']['table'][$data['table']] = 0;
	}
	if (!isset($status['progress'])) {
		$status['progress'] = array();
	}
	if (!isset($status['progress']['tables'])) {
		$status['progress']['tables'] = array();
	}
	$status['compteurs']['table'][$data['table']] += count($data['rows']);
	$status['progress']['tables'][$data['table']] = $status['compteurs']['table'][$data['table']];

	foreach ($data['rows'] as $r => $row) {
		foreach ($row as $k => $v) {
			if (!isset($data['desc_dest']['field'][$k])
			  // attention, dans la desc les champs peuvent se retrouver tous en minuscule
			  and !isset($data['desc_dest']['field'][strtolower($k)])) {
				unset($data['rows'][$r][$k]);
				$status['ignore']['tables'][$data['table']][$k]=$k;
			}
		}
	}

	$res = base_inserer_copie($data['table'], $data['rows'], $data['desc_dest'], '');
	update_migration_depuis($status);
	return $res;
}
