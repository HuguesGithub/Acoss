<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Lancemement de la modification de la banque � partir des 
 * param�tres saisis dans le formulaire
 *
 * ENTREES :
 *    - $_GET["compte"] : compte comptable de la banque � modifier
 *    - $_GET["term"] : num�ro de terminal
 *    - $_POST["txt_str_compte"] : compte comptable de la banque � modifier
 *    - $_POST["txt_str_nom"] :  nom de la banque 
 *    - $_POST["txt_str_adresse"] :  adresse de la banque 
 *    - $_POST["txt_str_complementAdresse"] :  compl�ment de l'adresse de la banque 
 *    - $_POST["txt_str_codePostal"] :  code postal de la banque 
 *    - $_POST["txt_str_ville"] :  ville de la banque 
 *    - $_POST["txt_int_codeBanque"] :  code banque du RIB de la banque 
 *    - $_POST["txt_int_codeGuichet"] :  code guichet du RIB de la banque 
 *    - $_POST["txt_str_numeroCompte"] :  num�ro de compte du RIB de la banque 
 *    - $_POST["txt_int_cle"] :  cl� du RIB de la banque 
 *    - $_POST["txt_str_modePaiement"] :  mode de paiement de la banque 
 *    - $_POST["txt_str_typeEmetteur"] :  Type emetteur 
 *    - $_POST["opt_str_banqueDeFrance"] :  Banque de France Oui ou Non
 *    - $_POST["opt_str_restitutionMontantSepa"] :  Restitution montant SEPA Global ou Unitaire
   ----------------------------------------------------------------------------
 * @author : Abdelmalek BOURTAL
 * @since  : 18/11/2005
 * @package noyau/parametres
 * @see     : norme 01-01-532/DT/03/JS//A
 * @version : $Id: parametres_banques_modification_action_0101592.php 15755 2020-08-28 13:40:30Z AC75094922 $
 * $Source$
   --------------------------------------------------------------------------*/
   
   /** Biblioth�que de gestion d'erreur */
   require_once("gestion_erreur_fct.php");
   /** Biblioth�que de connexion � la base de donn�es*/
   require_once(FICHIER_CLASSE_BDD);
   /** Biblioth�que de fonctions communes aux projets de sicomor */
   require_once("sicomor_utils_fct.php");
	/** Biblioth�que de fonctions des signatures magn�tiques */
	require_once("signatures_fct.php");
	/** Biblioth�que des fonctions communes au module */
   require_once("parametres.inc.php");   
   /** Biblioth�que des fonctions communes au module noyau */
   require_once("noyau.inc.php");
   /** Biblioth�que du module controle des pi�ces comptables */
   require_once("pieces_comptables_controler.inc.php"); 
	      

   // -----------------------------------------------------------------------
   // R�cup�ration du N� de terminal
   // -----------------------------------------------------------------------
   $int_term = (integer) utils_litParametre("term");
   sicomor_verifValiditeNumTerminal($int_term);
   

   // -----------------------------------------------------------------------
   // V�rification des droits
   // -----------------------------------------------------------------------
   utils_VerifieDroit("MAJ_CPT_BANQUES");


   // -----------------------------------------------------------------------
   // Initialisation des variables
   // -----------------------------------------------------------------------
   //	Vaut false en cas d'erreurs lors de l'ex�cution
   $bln_OK = true;
   $bln_alerte = false;
   // Tableau des messages d'erreur
   $tab_message['tab_messageRGErreur'] = array();
   $tab_message['tab_messageRGAlerte'] = array();
   // Titre de la fen�tre
   $str_TitreFenetre = "Modification d'une banque"; 

   
   // -----------------------------------------------------------------
   // R�cup�ration et mise en forme des param�tres
   // -----------------------------------------------------------------
	$bln_alertesConfirmees = ((utils_litParametre("alertesConfirmees") == 1) ? true : false);
	$str_nomBanque = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_nom"))));
	$str_Adresse = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_adresse"))));
	$str_complementAdresse = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_complementAdresse"))));
	$str_codePostal = trim(utils_litParametre("txt_str_codePostal"));
	$str_Ville = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_ville"))));
	$str_codeBanque = trim(utils_litParametre("txt_int_codeBanque"));
	$str_codeGuichet = trim(utils_litParametre("txt_int_codeGuichet"));
	$str_numCompte = strtoupper(trim(utils_litParametre("txt_str_numeroCompte")));
	$str_cleRib = trim(utils_litParametre("txt_int_cle"));
	$str_codeBIC				= trim(utils_litParametre( "txt_str_codeBIC" ));
	$str_typeEmetteur				= trim(utils_litParametre( "opt_str_typeEmetteur" ));
	$str_banqueDeFrance = trim(utils_litParametre("opt_str_banqueDeFrance")); 
	$str_restitutionMontantSepa = trim(utils_litParametre("opt_str_restitutionMontantSepa"));  
   // Construction du num�ro IBAN � partir des 9 champs de 4 caract�res
   utils_recupereParametre("txt_str_numeroIBAN");
   $str_numeroIBAN = "";
	foreach($GLOBALS as $str_NomVariable => $str_ValeurVariable)
	{
	   if(substr($str_NomVariable, 0, strlen("txt_str_numeroIBAN")) == "txt_str_numeroIBAN")
	   {
	      $str_numeroIBAN .= strtoupper(utils_transformeCaracteresSpeciaux($str_ValeurVariable));
	   }
	}
	$str_dateVirementSCT		= str_replace("-", "/", trim(utils_litParametre( "txt_str_dateVirementSCT" )));
	$str_modePaiement = trim(utils_litParametre("txt_str_modePaiement"));
   $str_Compte = utils_litParametre("compte");
   $int_exercice = (integer) $_SESSION['vsExercice'];
   $str_statutBIC = "";
	$int_forcageValide = 0;
	
	//mode de transfert des fichiers de virement
	$str_modeTransfert   = trim(utils_litParametre("opt_str_modeTransferFichier"));  
	$str_userID          = trim(utils_litParametre("txt_str_userId"));  
	$str_hostID          = trim(utils_litParametre("txt_str_hostId"));  
	$str_partnerID       = trim(utils_litParametre("txt_str_partnerId"));  
	$str_nomBanqueVir    = trim(utils_litParametre("txt_str_nomBanque"));  
	$str_numFax          = trim(utils_litParametre("txt_str_numFax"));  
	$str_numEmetteur     = trim(utils_litParametre("txt_str_numEmetteur"));  
	// ---------------------------------------------------------------
   // Connexion � la base
   // ---------------------------------------------------------------
   $instanceBDD = INSTANCE_CLASSE_BDD;
   $connexion = new $instanceBDD;
   $connexion->Connecter();   
?>

<html>
<head><?php echo TAG_COMPATIBLE_IE5_QUIRKS;?>
      <script>
         // ---------------------------------------------------------------
         // - pour relancer le script en cas de confirmation des alertes
         // ---------------------------------------------------------------
         function relancePHP(parametres)
         {
            self.location.replace(self.location.href + parametres + '&alertesConfirmees=1'); 
         }
      </script>
<title><?php utils_afficheTitreFenetre($str_TitreFenetre)?></title>
<link rel="stylesheet" href="/framework/css/style.css">
</head>
<body>
<?php
   // ---------------------------------------------------------------
   // D�but de transaction
   // ---------------------------------------------------------------
   $bln_OK = $connexion->BeginTrans();
   if (!$bln_OK) 
   {
   	utils_JSBloquant("Erreur : impossible d'ouvrir la transaction.");
   }
	
	if ( $bln_OK )
	{
		$bln_OK = $connexion->LockTable("bb_banques", false);
		if( !$bln_OK ) 
		{
			utils_JSBloquant("La table des banques est utilis�e par un autre utilisateur."
                         	. "Veuillez r�essayer dans quelques instants.");	
		}
	}   
	if ( $bln_OK )
	{
		$bln_OK = $connexion->LockTable("aj_pcl0", false);
		if( !$bln_OK ) 
		{
			utils_JSBloquant("La table du plan comptable local est utilis�e par un autre utilisateur."
                         	. "Veuillez r�essayer dans quelques instants.");	
		}
	}   
   
   //lock des tables m�tiers
	include("noyau_lock_tables_metiers.inc.php");
   
   // ---------------------------------------------------------------
	// Contr�les fonctionnels et traitements m�tiers
	// ---------------------------------------------------------------
	if ($bln_OK)
	{
	   // V�rification : champ obligatoire - Le compte comptable
	   if ($str_Compte == '')
	   {
	      $bln_OK = false;
	      $tab_message[ 'tab_messageRGErreur' ][] = 'Le compte comptable est obligatoire.';
	   }
	
	   // VERIFICATION DE LA SAISIE D'UN BIC-IBAN
	   if ($str_codeBIC    != "" ||
	       $str_numeroIBAN != "")
	   {
	      // V�rification : code BIC obligatoire
	      if( $str_codeBIC == '' )
	      {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le code BIC est obligatoire.";
	      }
	      // V�rifier la syntaxe du code BIC
	      else if (!noyau_controleBIC($str_codeBIC, &$tab_message))
	      {
	         $bln_OK = false;
	      }
	       
	      // V�rification : num�ro IBAN obligatoire
	      if( $str_numeroIBAN == '' ) 
	      {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le num�ro IBAN est obligatoire.";
	      }
	      // V�rifier la syntaxe du numero IBAN
	      else if (!noyau_controleIBAN($str_numeroIBAN, &$tab_message))
	      {
	         $bln_OK = false;
	      }
	   }
	
	   // VERIFICATION DE LA SAISIE D'UN RIB
	   if ($str_codeBanque   != "" ||
	       $str_codeGuichet  != "" ||
	       $str_numCompte    != "" ||
	       $str_cleRib       != "")
	   {
	      // V�rifier que le code banque est un num�rique
	      if ($str_codeBanque == "")
	      {
	         $str_codeBanque = '0';
	      }
	      else if (!utils_estNumerique($str_codeBanque))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le code banque doit �tre un num�rique.";
	      }
	       
	      // V�rifier que le code guichet est un num�rique
	      if ($str_codeGuichet == "")
	      {
	         $str_codeGuichet = '0';
	      }
	      else if (!utils_estNumerique($str_codeGuichet))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le code guichet doit �tre un num�rique.";
	      }
	      	
	      // V�rifier que la cl� du RIB est un num�rique
	      if ($str_cleRib == "")
	      {
	         $str_cleRib = '0';
	      }
	      else if (!utils_estNumerique($str_cleRib))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "La cl� doit �tre un num�rique.";
	      }
	      	
	      // V�rifier que le RIB est bien form�
	      if (!noyau_controle_rib($str_codeBanque, $str_codeGuichet, $str_numCompte, $str_cleRib))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le RIB saisi est incorrect.";
	      }
	   }
      
      
      if (trim($str_typeEmetteur) !='U' && trim($str_typeEmetteur)!='R')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le type d'�metteur doit �tre URSSAF ou RSI.";
	   }

	   if (trim($str_banqueDeFrance) !='O' && trim($str_banqueDeFrance)!='N')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le Type Banque de France doit �tre renseign�.";
	   }

	   if (trim($str_restitutionMontantSepa) !='G' && trim($str_restitutionMontantSepa)!='U')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "La restitution du montant SEPA doit �tre renseign�.";
	   }
	   	
   	// V�rifier si la banque existe 
		if (!parametres_banques_select_par_code($connexion, $rst_Selection, 1, $str_Compte)) 
		{                                             
         $bln_OK = false;
         $tab_message['tab_messageRGErreur'][] = "Le compte bancaire $str_Compte n'existe pas.";      	
		}
		else 
		{
		   // Initialisation du statut, du flag de validation par for�age du BIC-IBAN  
			if(strcmp($str_codeBIC, trim($rst_Selection->fields["bb_bic"])) != 0 ||
			   strcmp($str_numeroIBAN, trim($rst_Selection->fields["bb_iban"])) != 0)
			{
			   $str_statutBIC = BIC_IBAN_STATUT_INSTANCE;
			   $int_forcageValide = 0;
			   $str_dateVirementSCT = "";
			}
			else
			{
			   $str_statutBIC = trim($rst_Selection->fields["bb_statut"]);
			   $int_forcageValide = (integer)($rst_Selection->fields["bb_valid_forcage"]);
			}
			$rst_Selection->Close();	
		}

      /* V�rifier le compte comptable */
      $tab_pcl = noyau_extraireDonneesUnitairesComptePCL($connexion, CODE_GESTION_BANQUES, $str_Compte, $int_exercice);
      if (count($tab_pcl) == 0) 
      {
         $bln_OK = false;
         $tab_message['tab_messageRGErreur'][] = "Le compte comptable $str_Compte n'existe pas.";
      }
		else {
			
			$str_Statut = $tab_pcl["aj_statut"];
			$str_imputGenerale = $tab_pcl["aj_imputgene"];
			
			// V�rifier si le compte est ouvert 
			if( $str_Statut != 'O' ) 
			{
         	$bln_OK = false;
         	$tab_message['tab_messageRGErreur'][] = "Le compte $str_Compte n'est pas ouvert.";				
			}
			
			// V�rifier si le compte n'est pas imputable en comptabilit� g�n�rale
			if( $str_imputGenerale != 'I' ) 
			{
         	$bln_OK = false;
         	$tab_message['tab_messageRGErreur'][] = "Le compte $str_Compte n'est pas imputable en comptabilit� g�n�rale.";				
			}
			
			// Le compte et aucun de ses p�re Minimum au PCN ne doit avoir la propri�t� ACOSS. 
         if ($bln_OK)
         {
            $bln_OK = pieces_comptables_controler_compte_acoss(&$connexion, $int_exercice, CODE_GESTION_BANQUES, $str_Compte,  &$tab_message);
            
         }	
		}
	}


   // V�rification de l'�galit� entre le code pays du BIC et le code pays de l'IBAN s'il sont renseign�s et
   // si l'alerte n'a pas �t� confirm�e par l'utilisateur
   if ($bln_OK && $bln_alertesConfirmees==false && trim($str_codeBIC)<>'' && trim($str_numeroIBAN)<>'')
   {
      // Si la valeur "pays" de l'IBAN est �gal � 'FR', alors la valeur "pays" du BIC doit �tre �gale � 'FR' ou 'NC' ou 'PF' ou 'WF'
      // Si la valeur "pays" de l'IBAN est diff�rente de 'FR', alors la valeur "pays" du BIC doit �tre �gale � la valeur "pays" de l'IBAN
       if((substr($str_codeBIC, 4, 2) == "FR" 
			|| substr($str_codeBIC, 4, 2) == "NC" || substr($str_codeBIC, 4, 2) == "PF" || substr($str_codeBIC, 4, 2) == "WF"
			|| substr($str_codeBIC, 4, 2) == "GP" || substr($str_codeBIC, 4, 2) == "GF" || substr($str_codeBIC, 4, 2) == "MQ"
			|| substr($str_codeBIC, 4, 2) == "RE" || substr($str_codeBIC, 4, 2) == "YT" || substr($str_codeBIC, 4, 2) == "MC"
			|| substr($str_codeBIC, 4, 2) == "PM" || substr($str_codeBIC, 4, 2) == "TF"
			)
            && substr($str_numeroIBAN, 0, 2) == "FR")
         {
         // On ne fait rien
      }
      else
      {
         if (substr($str_codeBIC, 4, 2) <> substr($str_numeroIBAN, 0, 2))
         {
            $bln_OK  = false;
            $tab_message['tab_messageRGErreur'][] = "Le code pays du BIC est diff�rent du code pays de l'IBAN.";
         }
      }
   }
   
   //v�rification sur les information sur les mode de transfert automatique
   //si c'est une banque de France, on ne doit pas �tre en automatique
   if($str_banqueDeFrance == "O" && $str_modeTransfert == "A"){
      $bln_OK  = false;
      $tab_message['tab_messageRGErreur'][] = "L'activation de l'option transfert automatique est interdite" 
	                                          ." si le compte de banque a le type 'Banque de France' � 'Oui'.";
   }
   
   // contr�le des champs ID, et ne doivent pas comporter des caract�re sp�ciaux et ne doivent pas �tre vide
   else if($str_modeTransfert == "A"){
      if($str_userID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'User ID' ne doit pas �tre vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_userID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'User ID' ne doit pas contenir des caract�res sp�ciaux.";
      }
      
      if($str_hostID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Host ID' ne doit pas �tre vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_hostID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Host ID' ne doit pas contenir des caract�res sp�ciaux.";
      }
      
      if($str_partnerID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Partner ID' ne doit pas �tre vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_partnerID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Partner ID' ne doit pas contenir des caract�res sp�ciaux.";
      }

      if($str_nomBanqueVir == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Nom de la banque' ne doit pas �tre vide.";
      }
      if($str_numFax == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Num�ro de fax' ne doit pas �tre vide.";
      }
      if($str_numEmetteur == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Num�ro �metteur' ne doit pas �tre vide.";
      }
   }
   
   //contr�le qu'il n'existe pas de regl�ment non transmis
   if($str_modeTransfert == "M"){
      $str_SQL = " SELECT count(*) as nombrehisto FROM bl_lotshisto, bj_reglement"
	             ." WHERE bl_exercice   = ".$_SESSION['vsExercice']
                ." AND   bl_exercice   = bj_bl_exercice "
                ." AND   bl_numero     = bj_bl_numero "
                ." AND   bj_bb_gestion = ".$connexion->qstr(CODE_GESTION_BANQUES)
                ." AND   bj_bb_compte  = ".$connexion->qstr($str_Compte)
                ." AND   bl_nomfic     LIKE ".$connexion->qstr("sicomor-axwgwi-urtobank%")
                ." AND   bl_statuttransf != ".$connexion->qstr("OK")
                ." AND   bl_statuttransf != ".$connexion->qstr("AN");
      
      if ( !$connexion->gererRequeteSQLSelection($str_SQL, "ERREUR lors de la recherche de l'historique des fichiers.",
                                                 1, $rst_histoFichier) ) {
            $bln_OK = false;
            $tab_message["tab_messageRGErreur"][] = "ERREUR lors de la recherche de l'historique des fichiers.";
      }
      else {
         if($rst_histoFichier->fields['nombrehisto'] > 0){
            $bln_OK  = false;
            $tab_message['tab_messageRGErreur'][] = "Le mode de transfert ne peut �tre mis � manuel car il existe des r�glements"
	                                                ." qui n'ont pas �t� transmis.";
         }
      }
      $rst_histoFichier->Close();
   }
	
	// Formattage et affichage d'un message d'avertissement si des anomalies ont
	// �t� d�tect�es pendant les contr�les pr�liminaires.
	if( !$bln_OK )
	{
	   $str_messageErreur = sicomor_construireMessage( $tab_message['tab_messageRGErreur'] );
	   utils_JSAvertissementScroll( $str_messageErreur );
	}
	
	if($bln_OK )
	{
	   // mise en forme des coordonn�es bancaires
	   // code banque
	   if( $str_codeBanque == '' ) {
	      $str_codeBanque = utils_completeChaine($str_codeBanque, 5, "0");
	   }
	   // code guichet
	   if( $str_codeGuichet == '') {
	      $str_codeGuichet = utils_completeChaine($str_codeGuichet, 5, "0");
	   }
	   // num�ro de compte
	   if ( $str_numCompte ) {
	      $str_numCompte = utils_completeChaine($str_numCompte, 11, "0");
	   }
	   // cl� RIB
	   if( $str_cleRib == '') {
	      $str_cleRib = utils_completeChaine($str_cleRib, 2, "0");
	   }

	   // ---------------------------------------------------------------
	   // Modification de l'enregistrement dans la base
	   // ---------------------------------------------------------------
	   $str_SQL = " UPDATE bb_banques "
               . " SET "
	      		. " bb_nom="             . $connexion->qstr($str_nomBanque) . ","
	      		. " bb_adresse="         . $connexion->qstr($str_Adresse) . ","
	      		. " bb_compadres="       . $connexion->qstr($str_complementAdresse) . ","
	      		. " bb_codpostal="       . $connexion->qstr($str_codePostal) . ","
	      		. " bb_ville="           . $connexion->qstr($str_Ville) . ","
	      		. " bb_codbanque="       . $str_codeBanque . "," 
	      		. " bb_codguiche="       . $str_codeGuichet . ","
	      		. " bb_numcompte="       . $connexion->qstr($str_numCompte) . ","
	      		. " bb_clerib="          . $str_cleRib . ","
	      		. " bb_modpaieme="       . $connexion->qstr($str_modePaiement) . ","                  
               . " bb_datemodif="       . $connexion->qstr(date("d/m/y")) . ","                  
               . " bb_bic="             . $connexion->qstr($str_codeBIC) . ","                  
		         . " bb_iban="            . $connexion->qstr($str_numeroIBAN) . ","                  
		         . " bb_datevirementsct=" . $connexion->qstr($str_dateVirementSCT) . "," 
		         . " bb_statut="          . $connexion->qstr($str_statutBIC) . "," 
		         . " bb_type_emetteur="   . $connexion->qstr($str_typeEmetteur) . "," 
		         . " bb_banque_france="   . $connexion->qstr($str_banqueDeFrance) . ","
               . " bb_valid_forcage="   . $int_forcageValide . ","
	      		. " bb_restitmntsepa="   . $connexion->qstr($str_restitutionMontantSepa) . ","
	      		. " bb_modetransfert="   . $connexion->qstr($str_modeTransfert) . ","
	      		. " bb_userid="          . $connexion->qstr($str_userID) . ","
	      		. " bb_hostid="          . $connexion->qstr($str_hostID) . ","
	      		. " bb_partnerid="       . $connexion->qstr($str_partnerID) . ","
	      		. " bb_nombanque="       . $connexion->qstr($str_nomBanqueVir) . ","
	      		. " bb_numfax="          . $connexion->qstr($str_numFax) . ","
	      		. " bb_numemetteur="     . $connexion->qstr($str_numEmetteur)
	            . " WHERE bb_aj_gestion=". $connexion->qstr(CODE_GESTION_BANQUES)
	            . " AND bb_aj_compte="   . $connexion->qstr($str_Compte);
	    
	   if (  $connexion->gererRequeteSQLMiseAJour($str_SQL, "Erreur lors de la modification de la banque.") != 1 )
	   {
	      utils_JSBloquant("Erreur : la modification de la banque ne s'est pas effectu�e.");
	      $bln_OK = false;
	   }
	}
	   
	// ---------------------------------------------------------------
	// Signature Magn�tique
	// ---------------------------------------------------------------
   if ($bln_OK) 
   {
      if (!signatures_banques(&$connexion, SIGN_ACTION_MODIFICATION, $_SESSION['vsExercice'], $str_Compte, $_SESSION['vsLogin'], &$tab_message)) 
      {            
         utils_JSBloquant(sicomor_construireMessage($tab_message["tab_messageRGErreur"]));
         utils_JSCommande("parent.fraDetail.document.getElementById('txt_str_nom').focus();"); 
         $bln_OK = false;
      }
   }
   
   // ---------------------------------------------------------------
   // Fin de transaction
   // ---------------------------------------------------------------
   if ($bln_OK) 
   {
	   /* si des alertes ont �t� d�tect�es */
	   if ($bln_alerte)
	   {
         if ( $connexion->blnTransactionEnCours && !$connexion->RollbackTrans())
         {
            utils_JSBloquant("Erreur lors du rollback de la transaction.");
         }
         else
         {
            $str_messageAlerte='';
            foreach($tab_message['tab_messageRGAlerte'] as $key=>$valeur)
            {
               $str_messageAlerte .= $valeur;
            }

            // Concat�nation dans une variables de tous les param�tres de la page, ceci pour avoir
            // les valeurs des zones de saisie
            $str_parametres='';
         	foreach($_REQUEST as $str_NomVariable => $str_ValeurVariable)
         	{
         	   // Le param�tre est d�j� pr�sent dans le $_REQUEST mais avec la mauvaise valeur. On l'enl�ve donc de la
         	   // concat�nation pour �tre plus propre. Ce param�tre sera d�fini correctement dans la fonction relancePHP
         	   if ($str_NomVariable != "alertesConfirmees") $str_parametres .="&".$str_NomVariable."=".addslashes($str_ValeurVariable);
         	}
            utils_JSConfirmeScroll("<b>Liste des alertes � valider : </b><br>" . $str_messageAlerte,
                                   "relancePHP('"
                                 . "&term=" . $int_term
                                 . "&majDetail=N"
                                 . $str_parametres. "')",  // L'utilisateur confirme les alertes : on relance le PHP avec confirmation
                                   "self.close()");        // sinon, on sort
         }
      }


	   /* si aucune alerte n'a �t� d�tect�e */
	   if (! $bln_alerte)
	   {
         if (! $connexion->CommitTrans())
         {
            utils_JSBloquant("Erreur lors du commit de la transaction.");
         }
         else
         {
            utils_JSCommande("parent.fraListe.location.replace('parametres_banques_liste_0101592.php?".$_SESSION['str_paramWID']."&compte=" . urlencode($str_Compte) . "&term=" . $int_term . "&majDetail=O')");
         }
      }
   }
   else
   {
      if ($connexion->blnTransactionEnCours && ! $connexion->RollbackTrans())
      {
         utils_JSBloquant("Erreur lors du rollback de la transaction.");
      }
      else
      {
         utils_JSCommande("parent.fraListe.location.replace('parametres_banques_liste_0101592.php?".$_SESSION['str_paramWID']."&compte=" . urlencode($str_Compte) . "&term=" . $int_term . "&majDetail=N')");
      }
   }
?>
</body>
</html>
