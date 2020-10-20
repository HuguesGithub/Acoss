<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Affichage du formulaire permettant la création d'une banque
 *
 * ENTREES :
 *    - $_GET["compte"] : L'enregistrement sur lequel on est positionné, ce qui 
 *                        permettra de mettre la ligne correspondant  
 *                        en tête de liste quand celle-ci sera réaffichée.
 *    - $_GET["term"] : numéro de terminal
   ----------------------------------------------------------------------------
 * @author : Abdelmalek BOURTAL
 * @since  : 21/11/05
 * @package noyau/parametres
 * @see     : norme 01-01-532/DT/03/JS//A
 * @version : $Id: parametres_banques_creation_form_0101592.php 15822 2020-09-11 12:47:00Z AC75095062 $
 * $Source$
   --------------------------------------------------------------------------*/
   
   /** Bibliothèque de gestion d'erreur */
   require_once("gestion_erreur_fct.php");
   /** Bibliothèque de fonctions communes aux projets de sicomor */
   require_once("sicomor_utils_fct.php");
	/** Bibliothèque des fonctions communes au module */
   require_once("parametres.inc.php");   
   /** Bibliothèque de connexion à la base de données*/
   require_once(FICHIER_CLASSE_BDD);
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
   // Titre de la fenêtre
   $str_TitreFenetre = "Création d'une banque";
	// Compte comptable de la banque sélectionnée dans la liste
	$str_Compte = "";
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

?>

<html>
<head><?php echo TAG_COMPATIBLE_IE5_QUIRKS;?>
<title><?php utils_afficheTitreFenetre($str_TitreFenetre)?></title>
<link rel="stylesheet" href="/framework/css/style.css">
<link rel="stylesheet" href="/framework/include/ajax/js/ajax_style.css">

<script type="text/javascript" src="/framework/js/valid_form_fct.js"></script>
<script type="text/javascript" src="/framework/modal_popup/js/popup_modal_fct.js"></script>
<script type="text/javascript" src="/multi_modules/include/zoom/js/zoom.inc.js"></script>
<script type="text/javascript" src="/framework/js/ecran_fct.js"></script>
<script type="text/javascript" src="/framework/js/XML_fct.js"></script>
<script type="text/javascript" src="/framework/js/URL.js"></script>      
<script type="text/javascript" src="/framework/include/ajax/js/ajaxHTTP_XML_fct.js"></script>
<script type="text/javascript" src="/multi_modules/include/sicomor.inc.js"></script>
<script type="text/javascript" src="/framework/js/ecran_fct.js"></script>
<script type="text/javascript" src="/framework/js/jquery.min.js"></script>
<script type="text/javascript" src="/framework/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="/framework/js/jquery.autocomplete.js"></script>
<script type="text/javascript">

	/* Déclaration zoom ajax sur la zone "compte comptable" */
   var ajax_ZoomCompte = new AjaxZoom();

   // Sur le bouton OK, on valide les entrées saisies dans le formulaire
   function VerifFormulaire()
   {
      // On vérifie que le champ compte comptable est saisi
      if (valid_EstVide(document.getElementById('txt_str_compteComptable').value)) {
         popup_AvertissementScroll('Le compte comptable est obligatoire.');
         document.getElementById('txt_str_compteComptable').focus();
         return false;
      }
	   
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
   	window_initAjax();
   	
   	window_onResize();
      
      // met les boutons radio en grisé dans fraListe
      valid_lock_radio('fraListe', true); 
      
      document.getElementById('txt_str_compteComptable').focus();
      
      // Ajout des accélérateurs sur les boutons
      valid_SetAccessKey();
      
      // Modification du titre du FrameSet
      parent.document.title = document.title;

      ecran_selectionnerBoutonRadio( 'opt_str_modeTransferFichier', "M");

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
      
      $("input[type='radio'][name='opt_str_modeTransferFichier']").each(function () {
         //on désactive le bouton radio si on n'est pas sur une base réserve
      	if(<?=$bln_baseReserve?> == 0){
      		$(this).attr("disabled", true);
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
				}
         	else{
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
   
   // Redimensionnement de la bordure du formulaire
   function window_onResize()
   {
   	ajax_ZoomCompte.repositionner();
   
      document.getElementById('creationform').txt_str_compteComptable.focus();
   }
   
   function window_initAjax()
   {
		// Définition de la zone dynamique et du zoom sur le BIC-IBAN et si le champ n'est pas en readonly          
      if(document.getElementById("txt_str_compteComptable") && ! document.getElementById("txt_str_compteComptable").readOnly)
      {          
         ajax_ZoomCompte.attachElement(document.getElementById("txt_str_compteComptable"));               
         ajax_ZoomCompte.attachPosition(document.getElementById("txt_str_compteComptable") , +2 , 0);               
         ajax_ZoomCompte.setTaille(470,100);
         ajax_ZoomCompte.setUrl("../include/ajax_zoom/ajax_liste_comptes_imputables_general_ouverts.php?<?php echo $_SESSION['str_paramWID']?>", 
             					Array("strGestion=>URLEncode('<?=CODE_GESTION_BANQUES?>')",
             					      "strCompte=>URLEncode(document.getElementById('txt_str_compteComptable').value)"));
		}
   }  
   
   // ---------------------------------------------------------------
   // Fonction de mise à jour du zoom compte
   // ---------------------------------------------------------------
   function majCompte( str_nom_champ )
   {
  		document.getElementById( "frame_action" ).src = "./parametres_banques_maj_num_compte_affiche.php?<?php echo $_SESSION['str_paramWID']?>&"
		               + "valeur1=" + document.getElementById( "txt_str_compteComptable" ).value;
   }
   
   // Passage en masjuscule des champs BIC et IBAN
   function majuscule_onChange(objet)
   {
      objet.value = valid_majuscule(objet.value);  
   }

</script>
</head>

<body onload="window_onLoad()" onresize="window_onResize()">

<!-- Tableau pour centrer le formulaire -->

<form id="creationform" name="creationform" method="post" 
      action="parametres_banques_creation_action_0101592.php?<?php echo $_SESSION['str_paramWID']?>&compte=<?php print urlencode($str_Compte); ?>&term=<?php print $int_term; ?>"        
      target="fraListe" onsubmit="return EnvoiFormulaire();">
      
      <input type="hidden" id="txt_str_gestion" name="txt_str_gestion">
      
<table width="100%" cellspacing="0">
   <!-- 1ere ligne : titre de la page -->
   <tr class="titre">
      <td>
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
			                  <td >
						         	<input class="GRISE" type="text" name="txt_str_gestion" size="1" value="<?php print CODE_GESTION_BANQUES ?>" READONLY />
			                     <input type="text" 
			                     		 name="txt_str_compteComptable" id="txt_str_compteComptable" 
			                     		 size="15" maxlength="15"
			                     		 onchange="javascript:majCompte();" 
			                     		 class="CHAMP_ZOOM" />
			                     		 
			                     		 &nbsp;<span id="txt_str_message_compte">&nbsp;</span>
			                  </td>
			                  <td class="LABEL">Mode de paiement par défaut :</td>
				               <td>
				               	<select id="txt_str_modePaiement" name="txt_str_modePaiement">	                  		
				                  	<?php print parametres_listeHtmlModesPaiement(""); ?>
				                  </select>
				               </td>
               			</tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Nom :</td>
			                  <td colspan="3">
			                     <input type="text" name="txt_str_nom" id="txt_str_nom" size="70" maxlength="50" />
			                  </td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Adresse :</td>
			                  <td><input type="text" name="txt_str_adresse" id="txt_str_adresse" size="43" maxlength="30" /></td>
			                  <td class="LABEL">Complément d'adresse :</td>
			                  <td><input type="text" name="txt_str_complementAdresse" id="txt_str_complementAdresse" size="43" maxlength="30" /></td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Code postal :</td>
			                  <td><input type="text" name="txt_str_codePostal" id="txt_str_codePostal" size="5" maxlength="5" /></td>
			                  <td class="LABEL">Ville :</td>
			                  <td><input type="text" name="txt_str_ville" id="txt_str_ville" size="43" maxlength="30" /></td>
			               </tr>
			               <tr class="HAUTEUR_CHAMP_SAISIE">
			                  <td class="LABEL">Type Emetteur :</td>
			                  <td><input type="radio" id="opt_str_typeEmetteurUrssaf" name="opt_str_typeEmetteur" value="U" checked> URSSAF
                               <input type="radio" id="opt_str_typeEmetteurRSI" name="opt_str_typeEmetteur" value="R" >RSI
                           </td>
			                  <td class="LABEL">Type Banque de France :</td>
			                  <td><input type="radio" id="opt_str_banqueDeFranceOui" name="opt_str_banqueDeFrance" value="O"> Oui
                               <input type="radio" id="opt_str_banqueDeFranceNon" name="opt_str_banqueDeFrance" value="N"> Non
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
               		                     <input type="text" name="txt_str_userId" id="txt_str_userId" class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
            		                     </td>
							                  <td class="LABEL">Nom de la banque : </td>
													<td class="consultation">
														<input type="text" name="txt_str_nomBanque" id="txt_str_nomBanque" class="CHAMP_OBLIGATOIRE" size="65" maxlength="35">
													</td>
            		                  </tr>
               		               <tr class="HAUTEUR_CHAMP_SAISIE">
               		                  <td class="LABEL">Host ID :</td>
               		                  <td class="consultation">
               		                     <input type="text" name="txt_str_hostId" id="txt_str_hostId" class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
            		                     </td>
							                  <td class="LABEL">Numéro de fax :</td>
							                  <td class="consultation">
							                  	<input type="text" name="txt_str_numFax" id="txt_str_numFax" class="CHAMP_OBLIGATOIRE" size="65" maxlength="15">
							                  </td>
            		                  </tr>
               		               <tr class="HAUTEUR_CHAMP_SAISIE">
               		                  <td class="LABEL">Partner ID :</td>
               		                  <td class="consultation">
               		                     <input type="text" name="txt_str_partnerId" id="txt_str_partnerId" class="CHAMP_OBLIGATOIRE" size="65" maxlength="30">
               		                  </td>
							                  <td class="LABEL">Numéro émetteur :</td>
													<td class="consultation">
														<input type="text" name="txt_str_numEmetteur" id="txt_str_numEmetteur" class="CHAMP_OBLIGATOIRE" size="65" maxlength="35">
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
													<col width="50%" />
												</colgroup>
												<tr class="HAUTEUR_CHAMP_SAISIE">
													<td class="label">BIC :</td>
													<td><input class="" type="text" id="txt_str_codeBIC"
													   onChange="majuscule_onChange(this);" 
														name="txt_str_codeBIC" maxlength="11" size="13" /></td>
													<td class="label">IBAN :</td>
													<td colspan="3"><input class="" type="text"
														id="txt_str_numeroIBAN1" name="txt_str_numeroIBAN1" maxlength="4"
														size="2" value="" 
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN2')" />
													<input class="" type="text" id="txt_str_numeroIBAN2"
														name="txt_str_numeroIBAN2" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN3')" />
													<input class="" type="text" id="txt_str_numeroIBAN3"
														name="txt_str_numeroIBAN3" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN4')" />
													<input class="" type="text" id="txt_str_numeroIBAN4"
														name="txt_str_numeroIBAN4" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN5')" />
													<input class="" type="text" id="txt_str_numeroIBAN5"
														name="txt_str_numeroIBAN5" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN6')" />
													<input class="" type="text" id="txt_str_numeroIBAN6"
														name="txt_str_numeroIBAN6" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN7')" />
													<input class="" type="text" id="txt_str_numeroIBAN7"
														name="txt_str_numeroIBAN7" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN8')" />
													<input class="" type="text" id="txt_str_numeroIBAN8"
														name="txt_str_numeroIBAN8" maxlength="4" size="2" value=""
														onChange="majuscule_onChange(this);" 
														onKeyUp="selectionChampsSuivant_onKeyUp(this,'txt_str_numeroIBAN9')" />
													<input class="" type="text" id="txt_str_numeroIBAN9"
														name="txt_str_numeroIBAN9" maxlength="2" size="2" value="" 
														onChange="majuscule_onChange(this);" /></td>
												</tr>
												<tr class="HAUTEUR_CHAMP_SAISIE">
													<td class="LABEL">Date virement SCT (Monaco) :</td>
													<td><input class="GRISE" type="text" id="txt_str_dateVirementSCT"
														name="txt_str_dateVirementSCT" maxlength="10" size="11"
														tabindex=-1 readonly onkeyup="javascript:valid_masqueSaisieDate(this);" /></td>
													<td class="LABEL">Statut :</td>
													<td class="consultation"></td>
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
														value=""></td>
													<td class="label">Code guichet :</td>
													<td><input class="CHAMP_NOMBRE" type="text"
														name="txt_int_codeGuichet" id="txt_int_codeGuichet" size="10"
														maxlength="5"
														value=""></td>
													<td class="label">Numéro de compte :</td>
													<td><input class="CHAMP_NOMBRE" type="text"
														id="txt_str_numeroCompte" name="txt_str_numeroCompte"
														maxlength="11" size="20"
														value=""></td>
													<td class="label">Clé :</td>
													<td><input class="CHAMP_NOMBRE" type="text" id="txt_int_cle"
														name="txt_int_cle" maxlength="2" size="2"
														value=""></td>
												</tr>
											</table>
										</fieldset>
									</td>
								</tr>
				</table>
            &nbsp;<br>
            <div align="right">
               <button class="action" type="submit" title="Enregistre les données de la nouvelle banque" id="cmdOK" name="cmdOK" value="OK">
               <u>O</u>K
               </button>     
               &nbsp;
               <button class="action" id="cmdAnnuler" name="cmdAnnuler" title="Retourne à l'écran détail de la banque sélectionnée" value="Annuler" onclick="javascript:ClickAnnuler();">
               <u>A</u>nnuler
               </button>               
            </div>
         </div>  <!-- Fin du div pour gérer la marge -->
      </td>
   </tr>
</table>
</form>
<!-- iframe où va s'éxecuter le script action du formulaire -->
<iframe height="0" width="100%" frameborder="0" id="frame_action" name="frame_action" src="/framework/commun/vide.html" ></iframe>

</body>
</html>
