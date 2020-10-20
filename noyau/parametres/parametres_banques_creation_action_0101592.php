<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Lancemement de la cr�ation de la banque � partir des 
 * param�tres saisis dans le formulaire
 *
 * ENTREES :
 *    - $_GET["compte"] : L'enregistrement sur lequel on est positionn�, ce qui 
 *								  permettra de mettre la ligne correspondant � cette banque 
 *                        en t�te de liste quand celle-ci sera r�affich�e
 *    - $_GET["term"] : num�ro de terminal
 *    - $_POST["txt_str_compte"] : le compte comptable de la banque � cr�er 
 *    - $_POST["txt_str_nom"] :  nom de la banque � cr�er
 *    - $_POST["txt_str_adresse"] :  adresse de la banque � cr�er
 *    - $_POST["txt_str_complementAdresse"] :  compl�ment de l'adresse de la banque � cr�er
 *    - $_POST["txt_str_codePostal"] :  code postal de la banque � cr�er
 *    - $_POST["txt_str_ville"] :  ville de la banque � cr�er
 *    - $_POST["txt_int_codeBanque"] :  code banque du RIB de la banque � cr�er
 *    - $_POST["txt_int_codeGuichet"] :  code guichet du RIB de la banque � cr�er
 *    - $_POST["txt_str_numeroCompte"] :  num�ro de compte du RIB de la banque � cr�er
 *    - $_POST["txt_int_cle"] :  cl� du RIB de la banque � cr�er
 *    - $_POST["txt_str_modePaiement"] :  mode de paiement de la banque � cr�er
 *    - $_POST["opt_str_typeEmetteur"] :  Type d'emetteur URSSAF ou RSI
 *    - $_POST["opt_str_banqueDeFrance"] :  Banque de France Oui ou Non
 *    - $_POST["opt_str_restitutionMontantSepa"] :  Restitution montant SEPA Global ou Unitaire
   ----------------------------------------------------------------------------
 * @author : Abdelmalek BOURTAL
 * @since  : 21/11/05
 * @package noyau/parametres
 * @see     : norme 01-01-532/DT/03/JS//A
 * @version : $Id: parametres_banques_creation_action_0101592.php 15755 2020-08-28 13:40:30Z AC75094922 $
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


	// ------------------------------------------------------------------------
	// Initialisation des variables
	// ------------------------------------------------------------------------
	// Tableau des messages d'erreurs
	$tab_message['tab_messageRGErreur'] = array();
	$tab_message['tab_messageRGAlerte'] = array();
	// flag d'ex�cution
	$bln_OK = true;
	$bln_alerte = false;
	// Titre de la fen�tre
	$str_TitreFenetre = "Cr�ation d'une banque";
	// banque s�lectionn�e dans la liste
	$str_Compte = "";
	// compte comptable de la banque � cr�er
	$str_CompteCreation = "";
   // nom de la banque
   $str_Nom = "";
   // adresse
  	$str_Adresse = "";
  	// adresse compl�mentaire
	$str_complementAdresse = "";
	// code postal            
	$str_codePostal = "";
	// ville
	$str_Ville = "";
	//Type d'emetteur
   $str_typeEmetteur = "U";
	// code banque
	$str_codeBanque = "";
	// code guichet
	$str_codeGuichet = "";
	// num�ro de compte
	$str_numCompte = "";
	// cl� du RIB
	$str_cleRib = "";
	// Code BIC
	$str_codeBIC = "";
	// Num�ro IBAN
	$str_numeroIBAN = "";
	// statut
	$str_statutBIC = "";
	// Validation par for�age
	$int_forcageValide = 0;
   // mode de paiement
   $str_modePaiement = "";
   
   // -----------------------------------------------------------------
   // R�cup�ration et mise en forme des param�tres
   // ----------------------------------------------------------------- 
	// Les champs saisis
	$bln_alertesConfirmees = ((utils_litParametre("alertesConfirmees") == 1) ? true : false);
	$str_CompteCreation = trim(utils_litParametre("txt_str_compteComptable"));   
	$str_nomBanque = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_nom"))));
	$str_Adresse = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_adresse"))));
	$str_complementAdresse = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_complementAdresse"))));
	$str_codePostal = trim(utils_litParametre("txt_str_codePostal"));
	$str_Ville = strtoupper(utils_transformeCaracteresSpeciaux(trim(utils_litParametre("txt_str_ville"))));
	$str_typeEmetteur = trim(utils_litParametre("opt_str_typeEmetteur"));   
	$str_codeBanque = trim(utils_litParametre("txt_int_codeBanque"));
	$str_codeGuichet = trim(utils_litParametre("txt_int_codeGuichet"));
	$str_numCompte = strtoupper(trim(utils_litParametre("txt_str_numeroCompte")));
	$str_cleRib = trim(utils_litParametre("txt_int_cle"));
	$str_codeBIC = trim(utils_litParametre("txt_str_codeBIC"));
	$str_banqueDeFrance = trim(utils_litParametre("opt_str_banqueDeFrance"));   
	$str_restitutionMontantSepa = trim(utils_litParametre("opt_str_restitutionMontantSepa"));
	// Construction du num�ro IBAN � partir des 9 champs de 4 caract�res
   utils_recupereParametre("txt_str_numeroIBAN"); 
	foreach($GLOBALS as $str_NomVariable => $str_ValeurVariable)
	{
	   if(substr($str_NomVariable, 0, strlen("txt_str_numeroIBAN")) == "txt_str_numeroIBAN")
	   {
	      $str_numeroIBAN .= strtoupper(utils_transformeCaracteresSpeciaux($str_ValeurVariable));
	   }
	}
	$str_statutBIC = $str_codeBIC != "" || $str_numeroIBAN != "" ? BIC_IBAN_STATUT_INSTANCE : "";
	$str_modePaiement = trim(utils_litParametre("txt_str_modePaiement"));
   $str_Compte = utils_litParametre("compte");
   $int_exercice = (integer) $_SESSION['vsExercice'];
   
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
   if ( !$bln_OK ) 
   {
   	utils_JSBloquant("Erreur : impossible d'ouvrir la transaction.");
   }
   

	// ----------------------------------------------------------------
	// Lock des tables 
	// ----------------------------------------------------------------
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
	   if ($str_CompteCreation == '')
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
	   
	   if (trim($str_typeEmetteur) !='U' && trim($str_typeEmetteur)!='R')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le type d'�metteur doit �tre URSSAF ou RSI.";
	   }

	   if (trim($str_banqueDeFrance) !='O' && trim($str_banqueDeFrance)!='N')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le type Banque de France doit �tre renseign�.";
	   }

	   if (trim($str_restitutionMontantSepa) !='G' && trim($str_restitutionMontantSepa)!='U')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "La restitution du montant SEPA doit �tre renseign�.";
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
	   // Les coordonn�es RIB ne sont pas renseign�es, 
	   // les mettre � z�ro sinon on a une erreur � l'INSERT dans la table
	   else 
	   {
	      $str_codeBanque = '0';
	      $str_codeGuichet = '0';
	      $str_cleRib = '0';
	   }
	   
	   // V�rifier si une banque est d�j� associ�e au compte comptable saisi     
      if (!parametres_banques_select_par_code($connexion, $rst_Selection, 0, $str_CompteCreation)) 
      {
         $bln_OK = false;
         $tab_message['tab_messageRGErreur'][] = "Le compte bancaire $str_CompteCreation existe d�j� pour une autre banque.";
      }
      else {
      	$rst_Selection->Close();
		}
	
	   /* V�rifier le compte comptable */
	   $tab_pcl = noyau_extraireDonneesUnitairesComptePCL($connexion, CODE_GESTION_BANQUES, $str_CompteCreation, $int_exercice);
	   if (count($tab_pcl) == 0)
	   {
	      $bln_OK = false;
	      $tab_message[ 'tab_messageRGErreur' ][] = "Le compte comptable $str_CompteCreation n'existe pas.";
	   }
	   else
	   {
	      $str_Statut = $tab_pcl[ "aj_statut" ];
	      $str_imputGenerale = $tab_pcl[ "aj_imputgene" ];
	      	
	      // V�rifier si le compte est ouvert
	      if ($str_Statut != 'O')
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le compte $str_CompteCreation n'est pas ouvert.";
	      }
	      	
	      // V�rifier si le compte n'est pas imputable en comptabilit� g�n�rale
	      if ($str_imputGenerale != 'I' )
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le compte $str_CompteCreation n'est pas imputable en comptabilit� g�n�rale.";
	      }
	      
			// Le compte et aucun de ses p�re Minimum au PCN ne doit avoir la propri�t� ACOSS. 
         if ($bln_OK)
         {
            $bln_OK = pieces_comptables_controler_compte_acoss(&$connexion, $int_exercice, CODE_GESTION_BANQUES, $str_CompteCreation,  &$tab_message);
            
         }
	   }
	}


   // V�rification de l'�galit� entre le code pays du BIC et le code pays de l'IBAN s'il sont renseign�s et
   // si l'alerte n'a pas �t� confirm�e par l'utilisateur
   if ($bln_OK && $bln_alertesConfirmees==false && trim($str_codeBIC)<>'' && trim($str_numeroIBAN)<>'')
   {
      // Si la valeur "pays" de l'IBAN est �gal � 'FR', alors la valeur "pays" du BIC doit �tre �gale � 'FR' ou 'NC' ou 'PF' ou 'WF', GP, GF, MQ, RE, YT, MC
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

        
	// Formattage et affichage d'un message d'avertissement si des anomalies ont
	// �t� d�tect�es pendant les contr�les pr�liminaires.
	if( !$bln_OK )
	{
	   $str_messageErreur = sicomor_construireMessage( $tab_message['tab_messageRGErreur'] );
	   utils_JSAvertissementScroll( $str_messageErreur );
	}

	// ---------------------------------------------------------------
	// Cr�ation de l'enregistrement dans la base
	// ---------------------------------------------------------------
	if ($bln_OK)
	{
	   // mise en forme des coordonn�es bancaires
	   // code banque
	   if( $str_codeBanque ) {
	      $str_codeBanque = utils_completeChaine($str_codeBanque, 5, "0");
	   }
	   // code guichet
	   if( $str_codeGuichet ) {
	      $str_codeGuichet = utils_completeChaine($str_codeGuichet, 5, "0");
	   }
	   // num�ro de compte
	   if ( $str_numCompte ) {
	      $str_numCompte = utils_completeChaine($str_numCompte, 11, "0");
	   }
	   // cl� RIB
	   if( $str_cleRib ) {
	      $str_cleRib = utils_completeChaine($str_cleRib, 2, "0");
	   }

	   // ---------------------------------------------------------------
	   // Cr�ation de l'enregistrement dans la base
	   // ---------------------------------------------------------------
	   $str_SQL = "INSERT INTO bb_banques " .
	              "(bb_aj_gestion, bb_aj_compte, bb_nom, " .
	              "bb_adresse, bb_compadres, bb_codpostal, bb_ville, " .
	              "bb_codbanque, bb_codguiche, bb_numcompte, bb_clerib, bb_modpaieme, bb_datemodif, " .
                  "bb_bic, bb_iban, bb_statut, bb_valid_forcage, bb_type_emetteur, bb_banque_france, ".
                  "bb_restitmntsepa, bb_modetransfert, bb_userid, bb_hostid, bb_partnerid, ".
                  "bb_nombanque, bb_numfax, bb_numemetteur) " .
	              "VALUES ( " .
	              $connexion->qstr(CODE_GESTION_BANQUES) . "," .
	              $connexion->qstr($str_CompteCreation) . "," .
	              $connexion->qstr($str_nomBanque) . "," .
	              $connexion->qstr($str_Adresse) . "," .
	              $connexion->qstr($str_complementAdresse) . "," .
	              $connexion->qstr($str_codePostal) . "," .
	              $connexion->qstr($str_Ville) . "," .
	              $str_codeBanque . "," .
	              $str_codeGuichet . "," .
	              $connexion->qstr($str_numCompte) . "," .
	              $str_cleRib . "," .
	              $connexion->qstr($str_modePaiement) . "," .
	              $connexion->qstr(date("d/m/y")) . "," .
	              $connexion->qstr($str_codeBIC) . "," .
	              $connexion->qstr($str_numeroIBAN) . "," .
				     $connexion->qstr($str_statutBIC) . "," .
				     $int_forcageValide . "," .
				     $connexion->qstr($str_typeEmetteur). "," .
				     $connexion->qstr($str_banqueDeFrance). "," .
				     $connexion->qstr($str_restitutionMontantSepa). ",".
	              $connexion->qstr($str_modeTransfert). ",".
	              $connexion->qstr($str_userID). ",".
	              $connexion->qstr($str_hostID). ",".
	              $connexion->qstr($str_partnerID). ",".
	              $connexion->qstr($str_nomBanqueVir). ",".
	              $connexion->qstr($str_numFax). ",".
	              $connexion->qstr($str_numEmetteur). ")";


	   if (  $connexion->gererRequeteSQLMiseAJour($str_SQL, "Erreur lors de la cr�ation de la banque.") != 1 )
	   {
	      utils_JSBloquant("Erreur lors de la cr�ation de la banque.");
	      utils_JSCommande("parent.fraDetail.document.getElementById('txt_str_compteComptable').focus();");
	      $bln_OK = false;
	   }
	}

	// ---------------------------------------------------------------
	// Signature Magn�tique
	// ---------------------------------------------------------------
	if ($bln_OK) 
	{
         if (!signatures_banques(&$connexion, SIGN_ACTION_CREATION, $_SESSION['vsExercice'], $str_CompteCreation, $_SESSION['vsLogin'], &$tab_message)) 
         {                      	
	         utils_JSBloquant(sicomor_construireMessage($tab_message["tab_messageRGErreur"]));
            utils_JSCommande("parent.fraDetail.document.getElementById('txt_str_compteComptable').focus();");
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
            utils_JSCommande("parent.fraListe.location.replace('parametres_banques_liste_0101592.php?".$_SESSION['str_paramWID']."&compte=" . urlencode($str_CompteCreation) . "&term=" . $int_term . "&majDetail=O')");
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
