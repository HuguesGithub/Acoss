<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Affichage du détail de la banque sélectionnée
 * Ce détail est affiché dans la frame "fraDetail"
 *
 * ENTREES :
 *    - $_GET["compte"] : compte comptable de la banque à visualiser
 *    - $_GET["term"] : numéro de terminal
   ----------------------------------------------------------------------------
 * @author : Abdelmalek BOURTAL
 * @since  : 18/11/2005
 * @package noyau/parametres
 * @see     : norme 01-01-532/DT/03/JS//A
 * @version : $Id: parametres_banques_detail_0101592.php 15733 2020-08-21 12:54:24Z AC75095062 $
 * $Source$
   --------------------------------------------------------------------------*/

   /** Bibliothèque de gestion d'erreur */
   require_once("gestion_erreur_fct.php");
   /** Bibliothèque de connexion à la base de données*/
   require_once(FICHIER_CLASSE_BDD);
   /** Bibliothèque de fonctions communes aux projets de sicomor */
   require_once("sicomor_utils_fct.php");
   /** Bibliothèque des fonctions communes au module */
   require_once("parametres.inc.php");
   /** Bibliothèque des fonctions communes au module noyau */
   require_once("noyau.inc.php");


   // -----------------------------------------------------------------------
   // Récupération du N° de terminal
   // -----------------------------------------------------------------------
   $int_term = (integer) utils_litParametre("term");
   sicomor_verifValiditeNumTerminal($int_term);


   // -----------------------------------------------------------------------
   // Vérification des droits
   // -----------------------------------------------------------------------
   utils_VerifieDroit("MAJ_CPT_BANQUES");


   // -----------------------------------------------------------------------
   // Initialisation des variables
   // -----------------------------------------------------------------------
	//	flag d'exécution
	$bln_OK = true;
   // titre de la fenêtre
   $str_TitreFenetre = "Gestion des banques";
   // compte de la banque
   $str_Compte = "";
   // nom de la banque
   $str_Nom = "";
   // adresse
  	$str_Adresse = "";
  	// adresse complémentaire
	$str_complementAdresse = "";
	// code postal
	$str_codePostal = "";
	// ville
	$str_Ville = "";
	// code banque
	$str_codeBanque = "";
	// code guichet
	$str_codeGuichet = "";
	// numéro de compte
	$str_numCompte = "";
	// clé du RIB
	$str_cleRib = "";
	// Code BIC
	$str_codeBIC = "";
	// Numéro IBAN
	$str_numeroIBAN = "";
   // Date virement SCT
	$str_dateVirementSCT = "";
	// Libellé du statut du BIC-IBAN
	$str_libelleStatut = "";
	// Validation par forçage
	$str_forcageValide = "";
	//mode de paiement
	$str_modePaiement = "";
	//Type d'emetteur
	$str_typeEmetteur = "";
	//Type d'emetteur affiché
	$str_typeEmetteurAffiche = "";
	//Banque de France
	$str_banqueDeFrance = "";
	//Banque de France affiché
	$str_banqueDeFranceAffiche = "";
	// Montant SEPA
	$str_restitutionMontantSepa = "";
	// Montant SEPA affiché
	$str_restitutionMontantSepaAffiche = "";
	//mode de transfert des fichiers de virement
	$str_modeTransfert   = "M";
	$str_userID          = "";
	$str_hostID          = "";
	$str_partnerID       = "";
	$str_nomBanque       = "";
	$str_numFax          = "";
	$str_numEmetteur     = "";
	// ---------------------------------------------------------------
   // Récupération des infos de l'URL
   // ---------------------------------------------------------------
   $str_Compte = utils_litParametre("compte");


   // ---------------------------------------------------------------
   // Connexion à la base
   // ---------------------------------------------------------------
   $instanceBDD = INSTANCE_CLASSE_BDD;
   $connexion = new $instanceBDD;
   $connexion->Connecter();


	// ---------------------------------------------------------------
	// Requête de sélection de l'enregistrement sélectionné
	// ---------------------------------------------------------------
	if ( $str_Compte != '')
	{
		if (!parametres_banques_select_par_code($connexion, $rst_Selection, 1, $str_Compte))
		{
			// Le code n'a pas été trouvé dans la base
			$bln_OK = false;
		}
		else
		{
			// ---------------------------------------------------------------
			// Lecture des infos
			// ---------------------------------------------------------------
	      $str_Nom = trim($rst_Selection->fields["bb_nom"]);
	      $str_Adresse = trim($rst_Selection->fields["bb_adresse"]);
	      $str_complementAdresse = trim($rst_Selection->fields["bb_compadres"]);
	      $str_codePostal = trim($rst_Selection->fields["bb_codpostal"]);
	      $str_Ville = trim($rst_Selection->fields["bb_ville"]);

	      // Type Emmetteur
	      $str_typeEmetteur = trim($rst_Selection->fields["bb_type_emetteur"]);
			if( $str_typeEmetteur=='U' ) {
				$str_typeEmetteurAffiche = "URSSAF";
			}
			else {
			   $str_typeEmetteurAffiche = "RSI";
			}

	      // Banque de France
	      $str_banqueDeFrance = trim($rst_Selection->fields["bb_banque_france"]);
			switch($str_banqueDeFrance)
			{
			   case 'O' :
			      $str_banqueDeFranceAffiche = "Oui";
			      break;

			   case 'N' :
			      $str_banqueDeFranceAffiche = "Non";
			      break;

			   default :
			      $str_banqueDeFranceAffiche = "";
			      break;
			}

		  // Restitution montant Sepa (batchBooking)
		  $str_restitutionMontantSepa = trim($rst_Selection->fields["bb_restitmntsepa"]);
		  switch($str_restitutionMontantSepa)
	  	  {
	  	  	   case 'G' :
				  $str_restitutionMontantSepaAffiche = PARAM_BANQUE_RESTITMNTSEPA_GLOBAL;
				  break;

			   case 'U' :
				  $str_restitutionMontantSepaAffiche = PARAM_BANQUE_RESTITMNTSEPA_UNITAIRE;
				  break;

			   default :
				  $str_restitutionMontantSepaAffiche = "";
			 	  break;
		  }

	      // code banque
	      $str_codeBanque = trim($rst_Selection->fields["bb_codbanque"]);
	      if( $str_codeBanque ) {
	      	$str_codeBanque = utils_completeChaine($str_codeBanque, 5, "0");
	      }
	      else {
	      	$str_codeBanque = "";
	      }
	      // code guichet
	      $str_codeGuichet = $rst_Selection->fields["bb_codguiche"];
	      // On test par rapport au code banque car un code guichet peut être renseigné qu'avec des 0 (banque populaire)
	      if( $str_codeBanque != 0 ) {
	      	$str_codeGuichet = utils_completeChaine($str_codeGuichet, 5, "0");
	      }
	      else {
	      	$str_codeGuichet = "";
	      }
	      // numéro de compte
	      $str_numCompte = trim($rst_Selection->fields["bb_numcompte"]);
	      if( $str_numCompte ) {
	      	$str_numCompte = utils_completeChaine($str_numCompte, 11, "0");
	      }
	      else {
	      	$str_numCompte = "";
	      }
	      // clé RIB
	      $str_cleRib = trim($rst_Selection->fields["bb_clerib"]);
	      if( $str_cleRib ) {
	      	$str_cleRib = utils_completeChaine($str_cleRib, 2, "0");
	      }
	      else {
	      	$str_cleRib = "";
	      }

		   // Code BIC
	      $str_codeBIC = trim($rst_Selection->fields["bb_bic"]);

	      // Numéro IBAN
	      $str_numeroIBAN = noyau_formatNumeroIBAN(trim($rst_Selection->fields["bb_iban"]));

	      // Date virement SCT
	      $str_dateVirementSCT = str_replace("-", "/", trim($rst_Selection->fields["bb_datevirementsct"]));

	      // Statut
         $str_statut = trim($rst_Selection->fields["bb_statut"]);
         $str_libelleStatut = $str_statut != "" ? $GLOBALS['TABLE_LIBELLE_BIC_IBAN_STATUT'][$str_statut] : "";

         // Validation par forçage
         $int_forcageValide = (integer)($rst_Selection->fields["bb_valid_forcage"]);
         $str_forcageValide = $int_forcageValide ? "oui" : "non";

			// mode de paiement
			$str_modePaiement = trim($rst_Selection->fields["bb_modpaieme"]);
			if( $str_modePaiement ) {
				$str_modePaiement = parametres_libelleModePaiement($str_modePaiement);
			}

			//mode de transfert des fichiers de virement
			$str_modeTransfert   = trim($rst_Selection->fields['bb_modetransfert']);
			if($str_modeTransfert == "A"){
			   $str_libelleModeTransfert = "Automatique";
			}
			else{
			   $str_libelleModeTransfert = "Manuel";
			}
			$str_userID          = trim($rst_Selection->fields['bb_userid']);
			$str_hostID          = trim($rst_Selection->fields['bb_hostid']);
			$str_partnerID       = trim($rst_Selection->fields['bb_partnerid']);
			$str_nomBanque       = trim($rst_Selection->fields['bb_nombanque']);
			$str_numFax          = trim($rst_Selection->fields['bb_numfax']);
			$str_numEmetteur     = trim($rst_Selection->fields['bb_numemetteur']);
				
			$rst_Selection->Close();
		}
	}

?>
<html>
<head><?php echo TAG_COMPATIBLE_IE5_QUIRKS;?>
<title><?php utils_afficheTitreFenetre($str_TitreFenetre)?></title>
<link rel="stylesheet" href="/framework/css/style.css">
<script type="text/javascript" src="/framework/modal_popup/js/popup_modal_fct.js"></script>
<script type="text/javascript" src="/framework/js/valid_form_fct.js"></script>

<script type="text/javascript">
   function ClicImprimer()
   {
      top.location.replace("parametres_edition_banques_filtre_form_0101402.php?<?php echo $_SESSION['str_paramWID']?>&term=<?php print $int_term; ?>&urlRetour=<?php print urlencode('parametres_banques_frame_0101592.php?'.$_SESSION['str_paramWID'].'&compte=' . $str_Compte); ?>");
   }

   function ClicCreer()
   {
      window.location.replace('parametres_banques_creation_form_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>');
   }

   function ClicModifier()
   {
      window.location.replace('parametres_banques_modification_form_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>');
   }

   function ClicSupprimer()
   {
      // On demande confirmation avant de supprimer
      if (popup_Question('<?php print "Etes-vous sûr de vouloir supprimer la banque " . CODE_GESTION_BANQUES . " " . $str_Compte; ?> ?')) {
         // Lancement de la suppression dans la frame fraListe
         parent.fraListe.location.replace('parametres_banques_suppression_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>');
		}
   }

   // Au chargement de la page, lance la procédure de redimensionnement
   function window_onLoad()
   {

      // Ajout des accélérateurs sur les boutons
      valid_SetAccessKey();

      // Modification du titre du FrameSet
      parent.document.title = document.title;

      // On met le focus sur le radio bouton de la liste
      valid_setfocus_radio("fraListe");
   }
</script>
</head>
<body onload="window_onLoad()">

	<?php
		if (!$bln_OK) {
	      utils_JSAvertissementScroll( "La banque sélectionnée n'a pas été trouvée.");
			utils_JSRedirectTop(
				"./parametres_banques_frame_0101592.php?".$_SESSION['str_paramWID'].""
				. "&term=" . $int_term
			);
		}
	?>

<!-- Tableau pour centrer le formulaire -->
<table width="100%" cellspacing="0" border="0">
   <colgroup>
      <col width="60%" />
      <col />
   </colgroup>
   <!-- 1ere ligne : titre de la page -->
   <tr class="titre">
      <td colspan="2">
         <?php print htmlspecialchars($str_TitreFenetre); ?>
      </td>
   </tr>
   <!-- 2eme ligne : pour contenir le reste de la page -->
   <tr>
      <td>
         <br>
         <!-- div pour gérer la marge -->
         <div style="margin-left:20;margin-right:20;width:100%">
				<table class="bordure_formulaire" width="100%" id="table_formulaire" cellspacing="3" cellpadding="0" border="0">
					<colgroup>
						<col width="15%" />
						<col width="33%" />
				      <col width="22%" />
				      <col width="30%" />
					</colgroup>
							<tr class="HAUTEUR_CHAMP_SAISIE">
								<td class="label">Compte comptable :</td>
								<td class="consultation"><?php print CODE_GESTION_BANQUES ?>&nbsp;&nbsp;<?php print htmlspecialchars($str_Compte)?></td>
								<td class="label">Mode de paiement par défaut :</td>
								<td class="consultation"><?php print htmlspecialchars($str_modePaiement); ?></td>
							</tr>
							<tr class="HAUTEUR_CHAMP_SAISIE">
								<td class="label">Nom :</td>
								<td class="consultation" colspan="3"><?php print htmlspecialchars($str_Nom); ?></td>
							</tr>
							<tr class="HAUTEUR_CHAMP_SAISIE">
								<td class="label">Adresse :</td>
								<td class="consultation"><?php print htmlspecialchars($str_Adresse); ?></td>
								<td class="label">Complément d'adresse :</td>
								<td class="consultation"><?php print htmlspecialchars($str_complementAdresse); ?></td>
							</tr>
							<tr class="HAUTEUR_CHAMP_SAISIE">
								<td class="label">Code postal :</td>
								<td class="consultation"><?php print htmlspecialchars($str_codePostal); ?></td>
								<td class="label">Ville :</td>
								<td class="consultation"><?php print htmlspecialchars($str_Ville); ?></td>
							</tr>
							<tr class="HAUTEUR_CHAMP_SAISIE">
								<td class="label">Type Emetteur :</td>
								<td class="consultation"><?php print htmlspecialchars($str_typeEmetteurAffiche); ?></td>
								<td class="label">Type Banque de France :</td>
								<td class="consultation"><?php print htmlspecialchars($str_banqueDeFranceAffiche); ?></td>
							</tr>
		               <tr class="HAUTEUR_CHAMP_SAISIE">
		                  <td class="LABEL"></td>
		                  <td class="consultation"></td>
		                  <td class="LABEL">Restitution montant SEPA :<br />(batchBooking)</td>
								<td class="consultation"><?php print htmlspecialchars($str_restitutionMontantSepaAffiche); ?></td>
		               </tr>
		               <tr>
								<td colspan="4">
								<fieldset><legend>Transfert des fichiers de virement :</legend>
								   <table width="100%">
   								   <colgroup>
							                  <col width="15%" />
													<col width="33%" />
											      <col width="22%" />
											      <col width="30%" />
   								   </colgroup>
            		               <tr class="HAUTEUR_CHAMP_SAISIE">
            		                  <td class="LABEL">Mode :</td>
            		                  <td class="consultation"><?=$str_libelleModeTransfert?></td>
            		               </tr>
            		               <tr class="HAUTEUR_CHAMP_SAISIE">
            		                  <td class="LABEL">User ID :</td>
            		                  <td class="consultation"><?=$str_userID?></td>
						                  <td class="LABEL">Nom de la banque : </td>
												<td class="consultation"><?=$str_nomBanque?></td>
            		               </tr>
            		               <tr class="HAUTEUR_CHAMP_SAISIE">
            		                  <td class="LABEL">Host ID :</td>
            		                  <td class="consultation"><?=$str_hostID?></td>
						                  <td class="LABEL">Numéro de fax :</td>
						                  <td class="consultation"><?=$str_numFax?></td>
            		               </tr>
            		               <tr class="HAUTEUR_CHAMP_SAISIE">
            		                  <td class="LABEL">Partner ID :</td>
            		                  <td class="consultation"><?=$str_partnerID?></td>
						                  <td class="LABEL">Numéro émetteur :</td>
												<td class="consultation"><?=$str_numEmetteur?></td>
						               </tr>
						               </table>
								</fieldset>
								</td>
							</tr>
							<tr>
								<td colspan="4">
								<fieldset><legend>BIC - IBAN</legend>
								<table width="100%">
									<colgroup>
										<col width="21%" />
										<col width="30%" />
										<col width="10%" />
										<col width="15%" />
										<col width="15%" />
										<col width="20%" />
									</colgroup>
									<tr class="HAUTEUR_CHAMP_SAISIE">
										<td class="label">BIC :</td>
										<td class="consultation"><?php print htmlspecialchars($str_codeBIC); ?></td>
										<td class="label">IBAN :</td>
										<td class="consultation" colspan="3"><?=htmlspecialchars($str_numeroIBAN);?></td>
									</tr>
									<tr class="HAUTEUR_CHAMP_SAISIE">
										<td class="LABEL">Date virement SCT (Monaco) :</td>
										<td class="consultation"><?php print htmlspecialchars($str_dateVirementSCT); ?></td>
										<td class="LABEL">Statut :</td>
										<td class="consultation"><?php print htmlspecialchars($str_libelleStatut); ?></td>
										<td class="LABEL">Validé par forçage :</td>
										<td class="consultation"><?php print htmlspecialchars($str_forcageValide); ?></td>
									</tr>
								</table>
								</fieldset>
								</td>
							</tr>
							<tr>
								<td colspan="4">
								<fieldset><legend>RIB</legend>
								<table width="100%">
									<colgroup>
										<col width="10%" />
										<col width="10%" />
										<col width="10%" />
										<col width="15%" />
										<col width="15%" />
										<col width="20%" />
										<col width="5%" />
										<col width="5%" />
									</colgroup>
									<tr class="HAUTEUR_CHAMP_SAISIE">
										<td class="label">Code banque :</td>
										<td class="consultation"><?php print htmlspecialchars($str_codeBanque); ?></td>
										<td class="label">Code guichet :</td>
										<td class="consultation"><?php print htmlspecialchars($str_codeGuichet); ?></td>
										<!--  </tr>
										               <tr class="HAUTEUR_CHAMP_SAISIE">-->
										<td class="label">Numéro de compte :</td>
										<td class="consultation"><?php print htmlspecialchars($str_numCompte); ?></td>
										<td class="label">Clé :</td>
										<td class="consultation"><?php print htmlspecialchars($str_cleRib); ?></td>
									</tr>
								</table>
								</fieldset>
								</td>
							</tr>
				</table>
            &nbsp;<br>
            <div align="right">
               <button class="action" id="cmdImprimer" name="cmdImprimer" value="Imprimer" title="Edite la liste des banques" onclick="javascript:ClicImprimer();">
               <u>I</u>mprimer
               </button>
               &nbsp;
               <button class="action" id="cmdCreer" name="cmdCreer" value="Créer" title="Crée une nouvelle banque" onclick="javascript:ClicCreer();">
               <u>C</u>réer
               </button>
               <?php // Les boutons 'modifier' et 'supprimer' ne sont accessibles que si on est positionné sur un vrai code ?>
               <?php if ($str_Compte) { ?>
                  &nbsp;
                  <button class="action" id="cmdModifier" name="cmdModifier" value="Modifier" title="Modifie la banque sélectionnée" onclick="javascript:ClicModifier();">
                  <u>M</u>odifier
                  </button>
                  &nbsp;
                  <button class="action" id="cmdSupprimer" name="cmdSupprimer" value="Supprimer" title="Supprime la banque sélectionnée" onclick="javascript:ClicSupprimer();">
                  <u>S</u>upprimer
                  </button>
               <?php } ?>
               &nbsp;
               <button class="action" id="cmdFermer" name="cmdFermer" value="Fermer" title="Retour au menu" onclick="javascript:top.location.replace('<?php print URL_PAGE_ACCUEIL_NOYAU ?>')">
               <u>F</u>ermer
               </button>
            </div>
         </div>  <!-- Fin du div pour gérer la marge-->
      </td>
   </tr>
</table>
</body>
</html>