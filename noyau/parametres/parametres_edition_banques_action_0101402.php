<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Vérification des données saisies dans le formulaire, puis lancement de
 * l'ESQL/C qui générera l'édition.
   ----------------------------------------------------------------------------
 * ENTREES :
 *    - $_POST['hid_int_term'] : N° de terminal
 *    - $_POST['hid_int_exercice'] : N° d'exercice par défaut
 *    - $_POST['txt_str_plageDebutBanque'] : code de début
 *    - $_POST['txt_str_plageFinBanque'] : code de fin
   ----------------------------------------------------------------------------
 * @author  Olivier BONVALET
 * @since   07/07/05
 * @package noyau/parametres
 * @see     norme 01-01-532/DT/03/JS//A
 * $Id: parametres_edition_banques_action_0101402.php 15755 2020-08-28 13:40:30Z AC75094922 $
   --------------------------------------------------------------------------*/

   /** Bibliothèque de gestion d'erreur */
   require_once("gestion_erreur_fct.php");
   /** Bibliothèque de connexion à la base de données*/
   require_once(FICHIER_CLASSE_BDD);

   /** Bibliothèque des utilitaires */
   require_once("utils_fct.php");
   /** Bibliothèque des fonctions communes au module noyau */
   require_once("noyau.inc.php");   

   /** Bibliothèque de fonctions communes aux modules de SICOMOR */
   require_once("sicomor_utils_fct.php");
   
   require_once(SCRIPT_EDITIONLISTE_PROJET); /** Bibliothèque d'édition */


   // ------------------------------------------------------------------------
   // Vérification des droits de l'utilisateur sur le script
   // ------------------------------------------------------------------------
   utils_VerifieMultiDroit( array('MAJ_CPT_BANQUES','EDILST_CPT_BANQUES') );

   // -----------------------------------------------------------------------
   // Connexion a la base
   // -----------------------------------------------------------------------
   $instanceBDD = INSTANCE_CLASSE_BDD;
   $connexion = new $instanceBDD;
   $connexion->Connecter();

   class EditionListeBanque extends EditionListeProjet
   {
      /*--------------------------------------------------------------------------
        FONCTION : AfficheEnteteListe (classe EditionListe)
        --------------------------------------------------------------------------
        DESCRIPTION :                                                       */ /**
      * Refinition de la fonction pour affiche l'entête de la liste
      *
        --------------------------------------------------------------------------
      * @author : Iler VIRARAGAVANE
      * @since  : 26/08/2020
        ------------------------------------------------------------------------*/
      function AfficheEnteteListe()
      {
         $this->reservePlace(3);
         
         $this->ecrit(' <font face="arial" size="1"> ');
         $this->ecrit(' <table border="1" width="100%" cellpadding="2" cellspacing="0"> ');
         $this->ecrit('    <tr bgcolor="' . $this->strCouleurEntete. '"> ');
         for($i=0; $i<$this->intNbColonnes; $i++) {
            $this->ecrit('    <td ' . $this->tabLargeurColonne[$i] . ' align="center"> ');
            $this->ecrit('       <b>' . $this->tabEnteteColonne[$i]. '</b> ');
            $this->ecrit('    </td> ');
         } // Fin de la boucle sur les entêtes
         $this->ecrit('    </tr> ');
         $this->intNumLigne++;
      }
   }

   // ------------------------------------------------------------------------
   // Initialisation de variables internes
   // ------------------------------------------------------------------------
   $str_nomTraitement 	= 'édition';
   $str_titrePage 		= 'Edition des banques';
   $nb_comptesBanques 	= 0;
   $tab_DonneesBanques	= array();
   $str_sql 			= "";
   $str_sqlOrder		= "";
   $str_sqlCount		= "";
   $str_sqlSelect		= "";

   // ------------------------------------------------------------------------
   // Récupération des données du formulaire
   // ------------------------------------------------------------------------
   $int_term                = (integer) utils_litParametre( 'hid_int_term' );
   $int_exercice            = (integer) utils_litParametre( 'hid_int_exercice' );
   $str_plageDebutBanque	= strtoupper(utils_transformeCaracteresSpeciaux(utils_litParametre( 'txt_str_plageDebutBanque' )));
   $str_plageFinBanque      = strtoupper(utils_transformeCaracteresSpeciaux(utils_litParametre( 'txt_str_plageFinBanque' )));

   $str_urlRetour           = utils_litParametre('urlRetour');
   
   // -----------------------------------------------------------------------
   // Contrôles sur les éléments récupérés du formulaire
   // -----------------------------------------------------------------------

   // Vérifie la validité du numéro de terminal fourni
   sicomor_verifValiditeNumTerminal( $int_term );


   // Initialisation des variables de gestion des erreurs
   $bln_OK = true;
   $tab_message = array( 'tab_messageRGErreur' => array() );


   // Vérification : test de fourchette
   if( strcmp($str_plageDebutBanque, $str_plageFinBanque ) > 0 )
   {
      $bln_OK = false;
      $tab_message['tab_messageRGErreur'][] = 'Le code de fin doit être supérieur ou égal à celui de début';
   }




   // Formattage et affichage d'un message d'avertissement si des anomalies ont
   // été détectées pendant les contrôles préliminaires.
   if( !$bln_OK )
   {
      $str_messageErreur = sicomor_construireMessage( $tab_message['tab_messageRGErreur'] );
      utils_JSAvertissementScroll( $str_messageErreur );
   }
?>
<html>
   <head><?php echo TAG_COMPATIBLE_IE5_QUIRKS;?>
      <title><?php utils_afficheTitreFenetre( $str_titrePage ); ?></title>
      <link type="text/css" rel="stylesheet" href="/framework/css/style.css" >
      <script type="text/javascript" src="/framework/js/ecran_fct.js"></script>
      <script type="text/javascript" src="/framework/modal_popup/js/popup_modal_fct.js"></script>
   </head>
<body>
<?php
   if( $bln_OK )
   {
      // -----------------------------------------------------------------------
      // Affichage du message d'attente durant l'éxécution de l'ESQL/C
      // -----------------------------------------------------------------------
      utils_patientez( $str_titrePage, $str_nomTraitement, 'frame_action' );
   }

   if( $bln_OK )
   {
	   	$str_sql =  " FROM bb_banques"
	   			   ." WHERE (bb_aj_compte >= ".$connexion->qstr($str_plageDebutBanque).")"
	   			   ." AND   (bb_aj_compte <= ".$connexion->qstr($str_plageFinBanque).")";
	   	
	   	$str_sqlOrder = " ORDER BY bb_aj_compte;";
	   	
	   	$str_sqlCount = " SELECT count(*) as nb_enreg ".$str_sql;

	   	if($bln_OK && $connexion->gererRequeteSQLSelection($str_sqlCount, "Erreur lors de la récupération du nombre des banques.", 1, $rst_selection))
	   	{
	   		$nb_comptesBanques    = (integer)$rst_selection->fields["nb_enreg"];
	   		$rst_selection->Close();
	   	}
	   	 
	   	if ($nb_comptesBanques>0)
	   	{
			// ----------------------------------------------------------------------
			// Création de la requête paramétrée
			// ----------------------------------------------------------------------
			if($bln_OK)
			{
			   	$str_sqlSelect = " SELECT bb_aj_compte,"
							   	." bb_nom,"
							   	." bb_adresse,"
							   	." bb_compadres,"
							   	." bb_codpostal,"
							   	." bb_ville,"
							   	." bb_type_emetteur,"
							   	." bb_banque_france,"
   								." bb_restitmntsepa,"
							   	." bb_codbanque,"
							   	." bb_codguiche,"
							   	." bb_numcompte,"
							   	." bb_clerib,"
							   	." bb_bic,"
							   	." bb_iban,"
							   	." bb_statut,"
							   	." bb_valid_forcage,"
                           ." bb_modetransfert,"
                           ." bb_userid,"
                           ." bb_hostid,"
                           ." bb_partnerid,"
                           ." bb_nombanque,"
                           ." bb_numfax,"
                           ." bb_numemetteur"
   							. $str_sql
						 		. $str_sqlOrder;

			   	// ---------------------------------------------------------------
			   	// Extraction des infos des factures
			   	// ---------------------------------------------------------------
			   	if($connexion->gererRequeteSQLSelection($str_sqlSelect, "ERREUR lors de la lecture des informations des banques.", "AUCUNE_VERIF", $rst_Banque))
			   	{
			   		while(!$rst_Banque->EOF)
			   		{
			   			$str_Cle = trim($rst_Banque->fields["bb_aj_compte"]);
			   			
			   			/*------------------------------------------------------------------------*/
			   			/* Première cellule : N° de la banque                                     */
			   			/*------------------------------------------------------------------------*/
			   			$tab_DonneesBanques[$str_Cle]["str_banque"]			=	trim($rst_Banque->fields["bb_aj_compte"])."\n\n\n";
			   			
			   			/*------------------------------------------------------------------------*/
			   			/* Deuxième cellule : Libellé et adresse de la banque                     */
			   			/*------------------------------------------------------------------------*/
			   			$tab_DonneesBanques[$str_Cle]["str_adresse"]		=	trim($rst_Banque->fields["bb_nom"])."\n"
			   																." ".trim($rst_Banque->fields["bb_adresse"])."\n"
			   																." ".trim($rst_Banque->fields["bb_compadres"])."\n"
			   																." ".trim($rst_Banque->fields["bb_codpostal"])." "
			   																." ".trim($rst_Banque->fields["bb_ville"]);
			   			
			   			/*------------------------------------------------------------------------*/
			   			/* Troisième cellule : Type d'emetteur                                    */
			   			/*------------------------------------------------------------------------*/
			   			if(trim($rst_Banque->fields["bb_type_emetteur"])=='U')
			   			{
			   				$str_typeEmetteur = "URSSAF";
			   			}
			   			else
			   			{
			   				$str_typeEmetteur = "RSI";
			   			}
			   			$tab_DonneesBanques[$str_Cle]["str_type_emetteur"]	=	$str_typeEmetteur."\n\n\n";

			   			/*------------------------------------------------------------------------*/
			   			/* Quatrième cellule : Banque de France                                   */
			   			/*------------------------------------------------------------------------*/
			   			if(trim($rst_Banque->fields["bb_banque_france"])=='O')
			   			{
			   				$str_banque_france = "OUI";
			   			}
			   			elseif(trim($rst_Banque->fields["bb_banque_france"])=='N')
			   			{
			   				$str_banque_france = "NON";
			   			}
			   			else
			   			{
			   				$str_banque_france = "";
			   			}
			   			$tab_DonneesBanques[$str_Cle]["str_banque_france"]	=	$str_banque_france."\n\n\n";

			   			/*------------------------------------------------------------------------*/
			   			/* Cinquième cellule : Restitution montant SEPA                                   */
			   			/*------------------------------------------------------------------------*/
			   			if(trim($rst_Banque->fields["bb_restitmntsepa"])=='U')
			   			{
			   				$str_restitutionMontantSepa = PARAM_BANQUE_RESTITMNTSEPA_UNITAIRE;
			   			}
			   			else
			   			{
			   				$str_restitutionMontantSepa = PARAM_BANQUE_RESTITMNTSEPA_GLOBAL;
			   			}
			   			$tab_DonneesBanques[$str_Cle]["str_restitutionMontantSepa"]	=	$str_restitutionMontantSepa."\n\n\n";
			   			
			   			/*------------------------------------------------------------------------*/
			   			/* Cinquième cellule : Identité bancaire                                  */
			   			/*------------------------------------------------------------------------*/
			   			if (strlen(trim($rst_Banque->fields["bb_numcompte"])) != 0)
			   			{
			   				/* Impression du RIB */
			   				$str_rib = "RIB : "
      								 . str_pad(trim($rst_Banque->fields["bb_codbanque"]), 5,'0',STR_PAD_LEFT)
      								 . str_pad(trim($rst_Banque->fields["bb_codguiche"]), 5,'0',STR_PAD_LEFT)
      								 . trim($rst_Banque->fields["bb_codguiche"])
      								 . str_pad(trim($rst_Banque->fields["bb_clerib"]), 2,'0',STR_PAD_LEFT);
			   			}
			   			else
			   			{
			   				$str_rib = "RIB :";
			   			}

			   			/* Impression du code BIC */
			   			$str_bic = "BIC : "
      							 . trim($rst_Banque->fields["bb_bic"]);
			   			
			   			/* Impression du numéro IBAN */
			   			$str_iban = "IBAN : "
      							 . noyau_formatNumeroIBAN(trim($rst_Banque->fields["bb_iban"]));
 	
					 	/* Impression du statut du bic-iban et du mode de validation */
					 	/* ----------------------------------------------------------*/
			   			$str_tempSatut = '';
			   			$str_tempForcage = '';
					 	if (trim($rst_Banque->fields["bb_statut"]=="V"))
					 	{
					 	   $str_tempSatut = "Validé";
					
					 	   if (trim($rst_Banque->fields["bb_valid_forcage"] == 1))
					 	      $str_tempForcage = " - Validé par forçage : Oui";
					 	   else
					 	      $str_tempForcage = " - Validé par forçage : Non";
					 	}
					 	if (trim($rst_Banque->fields["bb_statut"] == "I"))
					 	{
					 	 	$str_tempSatut = "Instance";
					 	}

					 	$str_satutBicIban = "Statut BIC-IBAN : "
										  . $str_tempSatut
										  . $str_tempForcage;

						$tab_DonneesBanques[$str_Cle]["str_identite_bancaire"] = $str_rib
						."\n".$str_bic
						."\n".$str_iban
						."\n".$str_satutBicIban;
						
						//------------------------------------------------------------------------
						// sixième cellule : transfert automatique                                
						//------------------------------------------------------------------------
						if(trim($rst_Banque->fields["bb_modetransfert"]) == "M"){
                     $str_modeTransfert = "Oui";
                     $str_userId        = "User ID : "."csq";//$rst_Banque->fields["bb_userid"];
                     $str_hostId        = "Host ID : "."csq";//.$rst_Banque->fields["bb_hostid"];
                     $str_partnerId     = "Partner ID : "."csq";//.$rst_Banque->fields["bb_partnerid"];
                     $str_nomBanque     = "Banque : "."csq";//.$rst_Banque->fields["bb_nombanque"];
                     $str_numFax        = "N° Fax : "."csq";//.$rst_Banque->fields["bb_numfax"];
                     $str_numEmetteur   = "N° Emetteur : "."csq";//.$rst_Banque->fields["bb_numemetteur"];
						}
						else{
                     $str_modeTransfert = "Non";
                     $str_userId        = " ";
                     $str_hostId        = " ";
                     $str_partnerId     = " ";
                     $str_nomBanque     = " ";
                     $str_numFax        = " ";
                     $str_numEmetteur   = " ";
						}
						
						//on tronque car sinon ça dépasse de l'écran
						if(strlen(trim($str_userId)) > 25){
						   $str_userId = substr($str_userId, 0,25)."...";
						}
						
						if(strlen(trim($str_hostId)) > 25){
						   $str_hostId = substr($str_hostId, 0,25)."...";
						}
						
						if(strlen(trim($str_partnerId)) > 28){
						   $str_partnerId = substr($str_partnerId, 0,28)."...";
						}

						if(strlen(trim($str_nomBanque)) > 25){
							$str_nomBanque = substr($str_nomBanque, 0,25)."...";
						}
						if(strlen(trim($str_numEmetteur)) > 25){
							$str_numEmetteur = substr($str_numEmetteur, 0,25)."...";
						}
						
						
						$str_transfertAutomatique = $str_modeTransfert
                             						."\n".$str_userId
                             						."\n".$str_hostId
                             						."\n".$str_partnerId;
						// Inutile de mettre 6 lignes vides qui prennent de la place.
						// On limite à 3 lignes, celles nécessaires pour les infos de l'identité bancaire.
						if (trim($rst_Banque->fields["bb_modetransfert"]) == "M") {
							$str_transfertAutomatique .=   "\n".$str_nomBanque
																	."\n".$str_numFax
																	."\n".$str_numEmetteur;
						}
						$tab_DonneesBanques[$str_Cle]["str_transfertAutomatique"] = $str_transfertAutomatique;
						
			   			$rst_Banque->MoveNext();
			   		}
			   	}
			   	else
			   	{
			   		$bln_OK = false;
			   	}
			   	$rst_Banque->Close();
			}
	   	}
	   	if(count($tab_DonneesBanques)>0)
	   	{
	   		// -----------------------------------------------------------------
	   		// Initialisation de l'édition
	   		// -----------------------------------------------------------------
	   		$edition = new EditionListeBanque($connexion);
	   		$edition->intMaxLigne = 35;
	   		$edition->strOrientation = "paysage";
	   		$edition->strCodeEdition = "0101402";
	   		$edition->strFichierPDF = CHEMIN_BASES . "/" . $_SESSION['vsBase'] .CHEMIN_RELATIF_IMPRES."/parametres_liste_banques_".date('Y-m-d-H-i-s').".pdf";
	   		$edition->AfficheExercice($int_exercice);
	   		//$edition->blnFichierPDFTemp = true;
	   		 
	   		$edition->debutEdition();
	   		$edition->intNumLigne = $edition->intMaxLigne + 1;
	   		 
	   		$edition->ajoutColonne("N°Cpte"				       , "30"	, "align=\"left\""	, COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("Libellé/Adresse"		    , "190", "align=\"left\""	   , COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("Type Emetteur" 			 , "40"	, "align=\"center\"" , COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("BDF"	                   , "30"	, "align=\"center\"" , COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("Restitution montant SEPA", "60", "align=\"center\""  , COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("Identité Bancaire"	 	 , "160", "align=\"left\""	   , COMPORTEMENT_COL_WRAP,60000);
	   		$edition->ajoutColonne("Transfert automatique"	 , "140", "align=\"left\""	, COMPORTEMENT_COL_WRAP,60000);
	   		 
	   		$i=0;
	   		 
	   		// initialisation
	   		$edition->strTitre = "SICOMOR - LISTE DES BANQUES";
	   		$edition->debutListe();
	   		foreach($tab_DonneesBanques as $tab_datBanques)
	   		{
	   			$edition->onTraiteLigne($tab_datBanques);
	   		}
	   		 
	   		$edition->finListe();
	   		$edition->finPage();
	   		$edition->finEdition();
	   	}
	   	//sinon
	   	else
	   	{
	   		utils_JSAvertissementScroll("Aucune banque ne correspond aux critères sélectionnés.");
	   		$bln_OK = false;
	   	}
   }

   // Traitement OK ?
   if( $bln_OK )
   {
      // -----------------------------------------------------------------------
      // Affichage du résultat
      // -----------------------------------------------------------------------
   	  $strFichierPDF = basename($edition->strFichierPDF);
   	
      // URL de l'édition
      $str_URLEdition = '/framework/edition/affiche_edition.php?'.$_SESSION['str_paramWID'].'&'
                        .'fichier='.urlencode( $strFichierPDF )
                        .'&urlRetour='.urlencode($str_urlRetour);

      // Redirection vers l'affichage de l'édition PDF
      utils_JSRedirectTop( $str_URLEdition );
   }
   // -----------------------------------------------------------------------
   // Masquage du message d'attente
   // -----------------------------------------------------------------------
   utils_JSCommande( 'window.parent.ecran_cache_attente( "frame_action" );' );

?>
</body>
</html>
