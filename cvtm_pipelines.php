<?php
/*
 * Plugin CVT Multi etapes
 * (c) 2010 Cedric Morin Yterium.net
 * Distribue sous licence GPL
 *
 * Formulaires multi etapes :
 *
 * #FORMULAIRE_TRUC
 *
 * Squelette :
 * Chaque etape est representee par un squelette independant qui doit
 * implementer un formulaire autonome pour les saisies de l'etape n
 * formulaires/truc.html pour l'etape 1
 * formulaires/truc_2.html pour l'etape 2
 * formulaires/truc_n.html pour l'etape n
 *
 * Charger :
 * formulaires_truc_charger_dist() :
 *	passer '_etapes' => nombre total d'etapes de saisies (>1 !)
 *  indiquer toutes les valeurs a saisir sur toutes les pages
 *  comme si il s'agissait d'un formulaire unique
 *
 * Verifier :
 * le numero d'etape courante est disponible dans $x=_request('_etape'), si necessaire
 * _request() permet d'acceder aux saisies effectuees depuis l'etape 1,
 * comme si les etapes 1 a $x avaient ete saisies en une seule fois
 *
 * formulaires_truc_verifier_1_dist() : verifier les saisies de l'etape 1 uniquement
 * formulaires_truc_verifier_2_dist() : verifier les saisies de l'etape 2
 * formulaires_truc_verifier_n_dist() : verifier les saisies de l'etape n
 *
 * A chaque etape x, les etapes 1 a x sont appelees en verification
 * pour verifier l'absence de regression dans la validation (erreur, tentative de reinjection ...)
 * en cas d'erreur, la saisie retourne a la premiere etape en erreur.
 * en cas de succes, l'etape est incrementee, sauf si c'est la derniere.
 * Dans ce dernier cas on declenche traiter()
 *
 * Traiter
 * formulaires_truc_traiter_dist() : ne sera appele que lorsque *toutes*
 * les etapes auront ete saisies sans erreur.
 * Traite donc l'ensemble des saisies comme si il s'agissait d'un formulaire unique
 *
 *
 */

/**
 * Reinjecter dans _request() les valeurs postees
 * dans les etapes precedentes
 *
 * @param string $form
 * @return array
 */
function migration_recuperer_post_precedents($form){
	include_spip('inc/filtres');
	if ($form
	  AND $c = _request('cvtm_prev_post')
		AND $c = decoder_contexte_ajax($c, $form)){
		#var_dump($c);
		
		# reinjecter dans la bonne variable pour permettre de retrouver
		# toutes les saisies dans un seul tableau
		if ($_SERVER['REQUEST_METHOD']=='POST')
			$store = &$_POST;
		else
			$store = &$_GET;

		foreach($c as $k=>$v)
			if (!isset($store[$k])) // on ecrase pas si saisi a nouveau !
				$_REQUEST[$k] = $store[$k] = $v;

		// vider pour eviter un second appel a verifier_n
		// en cas de double implementation (unipotence)
		set_request('cvtm_prev_post');
		return array($c['_etape'],$c['_etapes']);
	}
	return false;
}

/**
 * Sauvegarder les valeurs postees dans une variable encodee
 * pour les recuperer a la prochaine etape
 *  
 * @param string $form
 * @param bool $je_suis_poste
 * @param array $valeurs
 * @return array
 */
function migration_sauver_post($form, $je_suis_poste, &$valeurs){
	if (!isset($valeurs['_cvtm_prev_post'])){
		$post = array('_etape'=>$valeurs['_etape'],'_etapes'=>$valeurs['_etapes']);
		foreach(array_keys($valeurs) as $champ){
			if (substr($champ,0,1)!=='_'){
				if ($je_suis_poste || (isset($valeurs['_forcer_request']) && $valeurs['_forcer_request'])) {
					if (($v = _request($champ))!==NULL)
						$post[$champ] = $v;
				}
			}
		}
		include_spip('inc/filtres');
		$c = encoder_contexte_ajax($post,$form);
		if (!isset($valeurs['_hidden']))
			$valeurs['_hidden'] = '';
		$valeurs['_hidden'] .= "<input type='hidden' name='cvtm_prev_post' value='$c' />";
		// marquer comme fait, pour eviter double encodage (unipotence)
		$valeurs['_cvtm_prev_post'] = true;
	}
	return $valeurs;
}


/**
 * Reperer une demande de formulaire CVT multi page
 * et la reformater
 * 
 * @param <type> $flux
 * @return <type> 
 */
function migration_formulaire_charger($flux){
	#var_dump($flux['data']['_etapes']);
	if (isset($flux['data']['_etapes'])){
		$form = $flux['args']['form'];
		$je_suis_poste = (isset($flux['args']['je_suis_poste'])?$flux['args']['je_suis_poste']:true);
		$nb_etapes = $flux['data']['_etapes'];
		$etape = _request('_etape');
		$etape = min(max($etape,1),$nb_etapes);
		set_request('_etape',$etape);
		$flux['data']['_etape'] = $etape;

		// sauver les posts de cette etape pour les avoir a la prochaine etape
		$flux['data'] = migration_sauver_post($form, $je_suis_poste, $flux['data']);
		#var_dump($flux['data']);
	}
	return $flux;
}


/**
 * Verifier les etapes de saisie
 *
 * @param array $flux
 * @return array
 */
function migration_formulaire_verifier($flux){
	#var_dump('Pipe verifier');
	
	if ($form = $flux['args']['form']
	  AND ($e = migration_recuperer_post_precedents($form))!==false){
		// recuperer l'etape saisie et le nombre d'etapes total
		list($etape,$etapes) = $e;
		$etape_demandee = _request('aller_a_etape'); // possibilite de poster en entier dans aller_a_etape

		// lancer les verifs pour chaque etape deja saisie de 1 a $etape
		$erreurs = array();
		$derniere_etape_ok = 0;
		$e = 0;
		while ($e<$etape AND $e<$etapes){
			$e++;
			$erreurs[$e] = array();
			if ($verifier = charger_fonction("verifier_$e","formulaires/$form/",true))
				$erreurs[$e] = call_user_func_array($verifier, $flux['args']['args']);
			elseif ($verifier = charger_fonction("verifier_etape","formulaires/$form/",true)){
				$args = $flux['args']['args'];
				array_unshift($args, $e);
				$erreurs[$e] = call_user_func_array($verifier, $args);
			}
			if ($derniere_etape_ok==$e-1 AND !count($erreurs[$e]))
				$derniere_etape_ok = $e;
			// possibilite de poster dans _retour_etape_x
			if (_request("_retour_etape_$e"))
				$etape_demandee = $e;
		}

		// si la derniere etape OK etait la derniere
		// on renvoie le flux inchange et ca declenche traiter
		if ($derniere_etape_ok==$etapes AND !$etape_demandee){
			return $flux;
		}
		else {
			$etape = $derniere_etape_ok+1;
			if ($etape_demandee>0 AND $etape_demandee<$etape)
				$etape = $etape_demandee;
			$etape = min($etape,$etapes);
			#var_dump("prochaine etape $etape");
			// retourner les erreurs de l'etape ciblee
			$flux['data'] = $erreurs[$etape];
			$flux['data']['_etapes'] = "etape suivante $etape";
			set_request('_etape',$etape);
		}
	}
	return $flux;
}

/**
 * Selectionner le bon fond en fonction de l'etape
 * L'etape 1 est sur le fond sans suffixe
 * Les autres etapes x sont sur le fond _x
 * 
 * @param array $flux
 * @return array
 */
function migration_styliser($flux){
	if (strncmp($flux['args']['fond'],'formulaires/',12)==0
	  AND isset($flux['args']['contexte']['_etapes'])
	  AND isset($flux['args']['contexte']['_etape'])
	  AND ($e=$flux['args']['contexte']['_etape'])>1
		AND $ext = $flux['args']['ext']
		AND $f=$flux['data']
		AND file_exists($f."_$e.$ext"))
		$flux['data'] = $f."_$e";
	return $flux;
}

/*
	$squelette = pipeline('styliser', array(
		'args' => array(
			'id_rubrique' => $id_rubrique,
			'ext' => $ext,
			'fond' => $fond,
			'lang' => $lang,
			'contexte' => $contexte, // le style d'un objet peut dependre de lui meme
			'connect' => $connect
		),
		'data' => $squelette,
	));
*/
?>