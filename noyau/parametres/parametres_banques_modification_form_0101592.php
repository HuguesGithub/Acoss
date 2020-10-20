<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Affichage du formulaire permettant la modification d'une banque
 *
 * ENTREES :
 *    - $_GET["compte"] : compte comptable de la banque à modifier
 *    - $_GET["term"] : numéro de terminal
   ----------------------------------------------------------------------------
 * @author : Abdelmalek BOURTAL
 * @since  : 18/11/2005
 * @package noyau/parametres
 * @see     : norme 01-01-532/DT/03/JS//A
 * @version : $Id: parametres_banques_modification_form_0101592.php 15822 2020-09-11 12:47:00Z AC75095062 $
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
   /** Bibliothèque des fonctions du paramétrage FEX */
   require_once("parametrage_FEX_cst.php");
   
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
   //	Vaut false en cas d'erreurs lors de l'exécution
   $bln_OK = true;
   // Titre de la fenêtre
   $str_TitreFenetre = "Modification d'une banque";
   // compte comptable de la banque à modifier
   $str_Compte = "";
   // nom de la banque à modifier
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
	// Libellé du statut du BIC-IBAN
	$str_libelleStatut = "";
	// Validation par forçage
	$str_forcageValide = "";
   // mode de paiement
   $str_modePaiement = ""; 
   // Type emetteur
   $str_typeEmetteur = ""; 
	//Banque de France
	$str_banqueDeFrance = "";
	//mode de transfert des fichiers de virement
	$str_modeTransfert   = "M";
	$str_userID          = "";
	$str_hostID          = "";
	$str_partnerID       = "";
	$str_nomBanque       = "";
	$str_numFax          = "";
	$str_numEmetteur     = "";
	//tab_message
	$tab_message = array();
	$tab_message['tab_messageRGErreur'] = "";
	   
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
   
   //récupération des données de l'organisme
   $bln_baseReserve = 0;
   if (sicomor_type_organisme($connexion, $str_codeOrganisme, $str_typeOrganisme,  $_SESSION['vsExercice'], $tab_message)) {
      if(isset($GLOBALS['TABLE_ORGANISME_TRANSFERT_REGLEMENT_AUTOMATIQUE'][$str_codeOrganisme])) {
         $bln_baseReserve = 1;        
      }
   }

   // ---------------------------------------------------------------
   // Requête de sélection
   // ---------------------------------------------------------------
   if (!parametres_banques_select_par_code($connexion, $rst_Selection, 1, $str_Compte)) {
      // Le compte n'a pas été trouvé dans la base      
      $bln_OK = false;
   } 
   else
   {
      // ---------------------------------------------------------------
      // Lecture des infos de la table
      // ---------------------------------------------------------------       
	   $str_Nom = trim($rst_Selection->fields["bb_nom"]);	      		       
	   $str_Adresse = trim($rst_Selection->fields["bb_adresse"]);
	   $str_complementAdresse = trim($rst_Selection->fields["bb_compadres"]);            
	   $str_codePostal = trim($rst_Selection->fields["bb_codpostal"]);
	   $str_Ville = trim($rst_Selection->fields["bb_ville"]);
	   $str_typeEmetteur = trim($rst_Selection->fields["bb_type_emetteur"]);
	   
	   
	   if (trim($str_typeEmetteur) == 'U')
	   {
	      $str_checked_Urssaf = 'checked';
	      $str_checked_RSI = '';
	   }
	   else 
	   {
	      $str_checked_Urssaf = '';
	      $str_checked_RSI = 'checked';
	     
	   }

      // Banque de France
      $str_banqueDeFrance = trim($rst_Selection->fields["bb_banque_france"]);
		switch($str_banqueDeFrance)
		{
		   case 'O' :
		      $str_checked_banqueDeFrance_Oui = "checked";
		      $str_checked_banqueDeFrance_Non = "";
		      break;
		      
		   case 'N' :
		      $str_checked_banqueDeFrance_Oui = "";
		      $str_checked_banqueDeFrance_Non = "checked";
		      break;
		      
		   default :
		      $str_checked_banqueDeFrance_Oui = "";
		      $str_checked_banqueDeFrance_Non = "";
		      break;
		}	


		// Restitution montant Sepa (batchBooking)
		$str_restitutionMontantSepa = trim($rst_Selection->fields["bb_restitmntsepa"]);
		switch($str_restitutionMontantSepa)
		{
			case 'G' :
				$str_checked_restitutionMontantSepa_Global = "checked";
				$str_checked_restitutionMontantSepa_Unitaire = "";
				break;
		
			case 'U' :
				$str_checked_restitutionMontantSepa_Global = "";
				$str_checked_restitutionMontantSepa_Unitaire = "checked";
				break;
				
			default :
				$str_checked_restitutionMontantSepa_Global = "";
				$str_checked_restitutionMontantSepa_Unitaire = "";
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
	   $str_codeGuichet = trim($rst_Selection->fields["bb_codguiche"]);
	   if( $str_codeGuichet ) {
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
	   $str_numeroIBAN = trim($rst_Selection->fields["bb_iban"]);
	      
	   // Date virement SCT
	   $str_dateVirementSCT = str_replace("-", "/", trim($rst_Selection->fields["bb_datevirementsct"]));
	   
	   // Statut
      $str_statut = trim($rst_Selection->fields["bb_statut"]);
      $str_libelleStatut = $str_statut != "" ? $GLOBALS['TABLE_LIBELLE_BIC_IBAN_STATUT'][$str_statut] : "";
         
      // Validation par forçage
      $int_forcageValide = (integer)($rst_Selection->fields["bb_valid_forcage"]);
      $str_forcageValide = $int_forcageValide ? "oui" : "non";
	   
      $str_modePaiement = trim($rst_Selection->fields["bb_modpaieme"]);
      
      //mode de transfert des fichiers de virement
      $str_modeTransfert   = trim($rst_Selection->fields['bb_modetransfert']);
      $str_userID          = trim($rst_Selection->fields['bb_userid']);
      $str_hostID          = trim($rst_Selection->fields['bb_hostid']);
      $str_partnerID       = trim($rst_Selection->fields['bb_partnerid']);
      $str_nomBanque       = trim($rst_Selection->fields['bb_nombanque']);
      $str_numFax          = trim($rst_Selection->fields['bb_numfax']);
      $str_numEmetteur     = trim($rst_Selection->fields['bb_numemetteur']);
      
	   $rst_Selection->Close();
   }     
?>
<html>
   <head><?php echo TAG_COMPATIBLE_IE5_QUIRKS;?>
      <title><?php utils_afficheTitreFenetre($str_TitreFenetre)?></title>
      <link rel="stylesheet" href="/framework/css/style.css">
      <script type="text/javascript" src="/framework/js/valid_form_fct.js"></script>
      <script type="text/javascript" src="/framework/modal_popup/js/popup_modal_fct.js"></script>
      <script type="text/javascript" src="/multi_modules/include/zoom/js/zoom.inc.js"></script>
      <script type="text/javascript" src="/multi_modules/include/sicomor.inc.js"></script>
      <script type="text/javascript" src="/framework/js/ecran_fct.js"></script>
      <script type="text/javascript" src="/framework/js/jquery.min.js"></script>
      <script type="text/javascript" src="/framework/js/jquery-ui.min.js"></script>
      <script type="text/javascript" src="/framework/js/jquery.autocomplete.js"></script>
      <script type="text/javascript">

      // Sur le bouton OK, on valide les entrées saisies dans le formulaire
      function VerifFormulaire()
      {
   		// Le code banque doit être un numérique
   	   if ( !valid_EstEntierPositif(document.getElementById('txt_int_codeBanque').value)) {
   	   	popup_AvertissementScroll('Le code banque doit être un numérique.');
   	      document.getElementById('txt_int_codeBanque').focus();
   	      return false;
   	   }
   
   		// Le code guichet doit être un numérique
   	   if ( !valid_EstEntierPositif(document.getElementById('txt_int_codeGuichet').value)) {
   	   	popup_AvertissementScroll('Le code guichet doit être un numérique.');
   	      document.getElementById('txt_int_codeGuichet').focus();
   	      return false;
   	   }
   
   		// La clé RIB doit être un numérique
   	   if ( !valid_EstEntierPositif(document.getElementById('txt_int_cle').value)) {
   	   	popup_AvertissementScroll('La clé doit être un numérique.');
   	      document.getElementById('txt_int_cle').focus();
   	      return false;
   	   }
               
         // Envoi du formulaire
         return true;
      }
      
      function EnvoiFormulaire()
      {
         return VerifFormulaire();
         
      }
      
      function ClickAnnuler()
      {
         // rend les boutons radio accessibles dans fraListe
         valid_lock_radio('fraListe', false);
   
         // Rafraîchissement de la liste
         parent.fraListe.location.replace('parametres_banques_liste_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>&majDetail=O');
   
         // Retour sur la page détail      
         location.replace('parametres_banques_detail_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>');
      }      
   
      // Au chargement de la page, on met le focus sur le 1er champ
      function window_onLoad()
      {
         // met les boutons radio en grisé dans fraListe
         valid_lock_radio('fraListe', true); 
         
         document.getElementById('txt_str_nom').focus();
   
         // Ajout des accélérateurs sur les boutons
         valid_SetAccessKey();
         
         // Modification du titre du FrameSet
         parent.document.title = document.title;
   
         ecran_selectionnerBoutonRadio( 'opt_str_modeTransferFichier', "<?=$str_modeTransfert?>" );

         $("input[type='radio'][name='opt_str_modeTransferFichier']").each(function () {
            //on désactive le bouton radio si on n'est pas sur une base réserve
         	if(<?=$bln_baseReserve?> == 0){
         		$(this).attr("disabled", true);
            }
            
         	if ($(this).is(":checked")) {
         	   if(this.value == "M"){
            	   //on desactive les cases
                  $("#txt_str_userId").attr("disabled", true);
                  $("#txt_str_hostId").attr("disabled", true);
                  $("#txt_str_partnerId").attr("disabled", true);
                  $("#txt_str_nomBanque").attr("disabled", true);
                  $("#txt_str_numFax").attr("disabled", true);
                  $("#txt_str_numEmetteur").attr("disabled", true);
                  //on enlève la class obligatoire
                  $("#txt_str_userId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_hostId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_partnerId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_nomBanque").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numFax").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numEmetteur").removeClass("CHAMP_OBLIGATOIRE");
                  // on rajoute la classe gris
                  $("#txt_str_userId").addClass("GRISE");
                  $("#txt_str_hostId").addClass("GRISE");
                  $("#txt_str_partnerId").addClass("GRISE");
                  $("#txt_str_nomBanque").addClass("GRISE");
                  $("#txt_str_numFax").addClass("GRISE");
                  $("#txt_str_numEmetteur").addClass("GRISE");
               }
            }

            // Gestion des boutons radio
            $(this).click(function () {
            	if(this.value == "M"){
            		//on desactive les cases
                  $("#txt_str_userId").attr("disabled", true);
                  $("#txt_str_hostId").attr("disabled", true);
                  $("#txt_str_partnerId").attr("disabled", true);
                  $("#txt_str_nomBanque").attr("disabled", true);
                  $("#txt_str_numFax").attr("disabled", true);
                  $("#txt_str_numEmetteur").attr("disabled", true);
                  //on enlève la class obligatoire
                  $("#txt_str_userId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_hostId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_partnerId").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_nomBanque").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numFax").removeClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numEmetteur").removeClass("CHAMP_OBLIGATOIRE");
                  // on rajoute la classe gris
                  $("#txt_str_userId").addClass("GRISE");
                  $("#txt_str_hostId").addClass("GRISE");
                  $("#txt_str_partnerId").addClass("GRISE");
                  $("#txt_str_nomBanque").addClass("GRISE");
                  $("#txt_str_numFax").addClass("GRISE");
                  $("#txt_str_numEmetteur").addClass("GRISE");
					} else{
               	//on réactive les champs
            		$("#txt_str_userId").removeAttr("disabled");
                  $("#txt_str_hostId").removeAttr("disabled");
                  $("#txt_str_partnerId").removeAttr("disabled");
          		   $("#txt_str_nomBanque").removeAttr("disabled");
                  $("#txt_str_numFax").removeAttr("disabled");
                  $("#txt_str_numEmetteur").removeAttr("disabled");
                  //on enlève la classe gris
                  $("#txt_str_userId").removeClass("GRISE");
                  $("#txt_str_hostId").removeClass("GRISE");
                  $("#txt_str_partnerId").removeClass("GRISE");
                  $("#txt_str_nomBanque").removeClass("GRISE");
                  $("#txt_str_numFax").removeClass("GRISE");
                  $("#txt_str_numEmetteur").removeClass("GRISE");
                  //on rajoute la class obligatoire
                  $("#txt_str_userId").addClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_hostId").addClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_partnerId").addClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_nomBanque").addClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numFax").addClass("CHAMP_OBLIGATOIRE");
                  $("#txt_str_numEmetteur").addClass("CHAMP_OBLIGATOIRE");
					}
            });
            
         });
      }
      
      // Passage en masjuscule des champs BIC et IBAN
      function majuscule_onChange(objet)
      {
         objet.value = valid_majuscule(objet.value);  
      }

      </script>
   </head>
   <body onload="window_onLoad()" >

   <?php
      if (!$bln_OK) 
      {
      	// Affichage du message d'erreur
      	utils_JSAvertissementScroll("La banque " . $str_Compte  . " n'existe pas.");
      	
      	// redirection sur la frame
         utils_JSRedirectTop("parametres_banques_frame_0101592.php?".$_SESSION['str_paramWID']."&term=".$int_term);      
      }    
   ?>

<form id="modificationform" name="modificationform" 
		method="post" action="parametres_banques_modification_action_0101592.php?<?php echo $_SESSION['str_paramWID']?>&term=<?php print $int_term; ?>" 
		target="fraListe" onsubmit="return EnvoiFormulaire();">

   <input type="hidden" name="compte" value="<?php print $str_Compte; ?>"/>

<table width="100%" cellspacing="0">
   <!-- 1ere ligne : titre de la page -->
   <tr class="titre">
      <td colspan="2">
         <?php print htmlspecialchars($str_TitreFenetre);?>
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
                  			<td class="LABEL">Compte comptable :</td>
                  			<td>
			                     <input class="GRISE" type="text" name="txt_str_gestion" size="1" value="<?php print CODE_GESTION_BANQUES ?>" READONLY />
                     			<input type="text" readonly
                     					 name="txt_str_compteComptable" id="txt_str_compteComptable" 
                     					 size="15" maxlength="15"
                     					 class="GRISE" value="<?= htmlspecialchars($str_Compte) ?>" />
                  			</td>
	                  		<td class="LABEL">Mode de paiement par défaut :</td>
	                  		<td>
	                  			<select id="txt_str_modePaiement" name="txt_str_modePaiement">	                  		
	                  			<?php print parametres_listeHtmlModesPaiement($str_modePaiement); ?>
	                  			</select>
	                  		</td>
               			</tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Nom :</td>
			                  <td colspan="3">
			                     <input type="text" name="txt_str_nom" id="txt_str_nom" size="70" maxlength="50" value="<?= htmlspecialchars($str_Nom) ?>" />
			                  </td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Adresse :</td>
			                  <td><input type="text" name="txt_str_adresse" id="txt_str_adresse" size="43" maxlength="30" value="<?= htmlspecialchars($str_Adresse) ?>" /></td>
			                  <td class="LABEL">Complément d'adresse :</td>
			                  <td><input type="text" name="txt_str_complementAdresse" id="txt_str_complementAdresse" size="43" maxlength="30" value="<?= htmlspecialchars($str_complementAdresse) ?>" /></td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Code postal :</td>
			                  <td><input type="text" name="txt_str_codePostal" id="txt_str_codePostal" size="5" maxlength="5" value="<?= htmlspecialchars($str_codePostal) ?>" /></td>
			                  <td class="LABEL">Ville :</td>
			                  <td><input type="text" name="txt_str_ville" id="txt_str_ville" size="43" maxlength="30" value="<?= htmlspecialchars($str_Ville) ?>" /></td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Type Emetteur :</td>
			                  <td><input type="radio" id="opt_str_typeEmetteurUrssaf" name="opt_str_typeEmetteur" value="U" <?php print $str_checked_Urssaf;?> > URSSAF
                               <input type="radio" id="opt_str_typeEmetteurRSI" name="opt_str_typeEmetteur" value="R" <?php print $str_checked_RSI;?>>RSI
                           </td>
			                  <td class="LABEL">Type Banque de France :</td>
			                  <td><input type="radio" id="opt_str_banqueDeFranceOui" name="opt_str_banqueDeFrance" value="O" <?php print $str_checked_banqueDeFrance_Oui;?>> Oui
                               <input type="radio" id="opt_str_banqueDeFranceNon" name="opt_str_banqueDeFrance" value="N" <?php print $str_checked_banqueDeFrance_Non;?>> Non
                           </td>
			               </tr>	
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td> </td>
		                     <td> </td>
			                  <td class="LABEL">Restitution montant SEPA :<br />(batchBooking)</td>
			                  <td>
			                  	<input type="radio" id="opt_str_restitutionMontantSepaOui" name="opt_str_restitutionMontantSepa" value="G" checked> <?php print PARAM_BANQUE_RESTITMNTSEPA_GLOBAL;?>
                            	<input type="radio" id="opt_str_restitutionMontantSepaNon" name="opt_str_restitutionMontantSepa" value="U" > <?php print PARAM_BANQUE_RESTITMNTSEPA_UNITAIRE;?>
                           </td>
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
               		                  <td class="consultation">
               		                     <input type="radio" name="opt_str_modeTransferFichier" value="M">Récupération manuelle sur poste &nbsp;&nbsp;&nbsp;
               		                     <input type="radio" name="opt_str_modeTransferFichier" value="A">Transfert automatique vers la banque 
            		                     </td>
               		               </tr>
               		               <tr class="HAUTEUR_CHAMP_SAISIE">
               		                  <td class="LABEL">User ID :</td>
               		                  <td class="consultation">
               		                     <input type="text" name="txt_str_userId" id="txt_str_userId" value="<?=$str_userID?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
            		                     </td>
							                  <td class="LABEL">Nom de la banque : </td>
													<td class="consultation">
														<input type="text" name="txt_str_nomBanque" id="txt_str_nomBanque" value="<?=$str_nomBanque?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="35">
               		                  </td>
            		                  </tr>
               		               <tr class="HAUTEUR_CHAMP_SAISIE">
               		                  <td class="LABEL">Host ID :</td>
               		                  <td class="consultation">
               		                     <input type="text" name="txt_str_hostId" id="txt_str_hostId" value="<?=$str_hostID?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
            		                     </td>
							                  <td class="LABEL">Numéro de fax :</td>
							                  <td class="consultation">
							                  	<input type="text" name="txt_str_numFax" id="txt_str_numFax" value="<?=$str_numFax?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="15">
               		                  </td>
            		                  </tr>
               		               <tr class="HAUTEUR_CHAMP_SAISIE">
               		                  <td class="LABEL">Partner ID :</td>
               		                  <td class="consultation">
               		                     <input type="text" name="txt_str_partnerId" id="txt_str_partnerId" value="<?=$str_partnerID?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
               		                  </td>
							                  <td class="LABEL">Numéro émetteur :</td>
													<td class="consultation">
														<input type="text" name="txt_str_numEmetteur" id="txt_str_numEmetteur" value="<?=$str_numEmetteur?>"
               		                            class="CHAMP_OBLIGATOIRE" size="65" maxlength="35">
               		                  </td>
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
											      <col width="25%" />
											      <col width="05%" />
											      <col width="15%" />
											      <col width="15%" />
											      <col width="20%" />
												</colgroup>
												<tr class="HAUTEUR_CHAMP_SAISIE">
													<td class="label">BIC :</td>
													<td><input class="" type="text" id="txt_str_codeBIC"
													   onChange="majuscule_onChange(this);" 
														name="txt_str_codeBIC" maxlength="11" size="13" value="<?=$str_codeBIC?>" /></td>
													<td class="label">IBAN :</td>
													<td colspan="3"><input class="" type="text"
														id="txt_str_numeroIBAN1" name="txt_str_numeroIBAN1" maxlength="4"
														size="2" value="<?= substr($str_numeroIBAN, 0, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN2')" />
													<input class="" type="text" id="txt_str_numeroIBAN2"
														name="txt_str_numeroIBAN2" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 4, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN3')" />
													<input class="" type="text" id="txt_str_numeroIBAN3"
														name="txt_str_numeroIBAN3" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 8, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN4')" />
													<input class="" type="text" id="txt_str_numeroIBAN4"
														name="txt_str_numeroIBAN4" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 12, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN5')" />
													<input class="" type="text" id="txt_str_numeroIBAN5"
														name="txt_str_numeroIBAN5" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 16, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN6')" />
													<input class="" type="text" id="txt_str_numeroIBAN6"
														name="txt_str_numeroIBAN6" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 20, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN7')" />
													<input class="" type="text" id="txt_str_numeroIBAN7"
														name="txt_str_numeroIBAN7" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 24, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN8')" />
													<input class="" type="text" id="txt_str_numeroIBAN8"
														name="txt_str_numeroIBAN8" maxlength="4" size="2" value="<?= substr($str_numeroIBAN, 28, 4) ?>"
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN9')" />
													<input class="" type="text" id="txt_str_numeroIBAN9"
														name="txt_str_numeroIBAN9" maxlength="2" size="2" value="<?= substr($str_numeroIBAN, 32, 2) ?>" 
														onChange="majuscule_onChange(this);" /></td>
												</tr>
												<tr class="HAUTEUR_CHAMP_SAISIE">
													<td class="LABEL">Date virement SCT (Monaco) :</td>
													<td><input class="GRISE" type="text" id="txt_str_dateVirementSCT"
														name="txt_str_dateVirementSCT" maxlength="10" size="11"
														value="<?= $str_dateVirementSCT ?>"
														tabindex=-1 readonly onkeyup="javascript:valid_masqueSaisieDate(this);" /></td>
													<td class="LABEL">Statut :</td>
													<td class="consultation"><?= $str_libelleStatut ?></td>
													<td class="LABEL">Validé par forçage :</td>
													<td class="consultation"><?= $str_forcageValide ?></td>
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
													<td><input class="CHAMP_NOMBRE" type="text"
														name="txt_int_codeBanque" id="txt_int_codeBanque" size="10"
														maxlength="5"
														value="<?= htmlspecialchars(str_pad($str_codeBanque, 5, "0")) ?>"></td>
													<td class="label">Code guichet :</td>
													<td><input class="CHAMP_NOMBRE" type="text"
														name="txt_int_codeGuichet" id="txt_int_codeGuichet" size="10"
														maxlength="5"
														value="<?= htmlspecialchars(str_pad($str_codeGuichet, 5, "0")) ?>"></td>
													<td class="label">Numéro de compte :</td>
													<td><input class="CHAMP_NOMBRE" type="text"
														id="txt_str_numeroCompte" name="txt_str_numeroCompte"
														maxlength="11" size="20"
														value="<?= htmlspecialchars(str_pad($str_numCompte, 11, "0")) ?>"></td>
													<td class="label">Clé :</td>
													<td><input class="CHAMP_NOMBRE" type="text" id="txt_int_cle"
														name="txt_int_cle" maxlength="2" size="2"
														value="<?= htmlspecialchars(str_pad($str_cleRib, 2, "0")) ?>"></td>
												</tr>
											</table>
										</fieldset>
									</td>
								</tr>
				</table>
            &nbsp;<br>
            <div align="right">
               <button class="action" type="submit" title="Enregistre les données de la banque sélectionnée" id="cmdOK" name="cmdOK" value="OK">
               <u>O</u>K
               </button>     
               &nbsp;
               <button class="action" id="cmdAnnuler" name="cmdAnnuler" title="Retourne à l'écran détail de la banque sélectionnée" value="Annuler" onclick="javascript:ClickAnnuler();">
               <u>A</u>nnuler
               </button>               
            </div>
         </div>  <!-- Fin du div pour gérer la marge-->
      </td>
   </tr>
</table>
</form>
</body>
</html>
