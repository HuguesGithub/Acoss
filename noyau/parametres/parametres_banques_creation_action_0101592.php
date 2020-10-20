<?php
/* ----------------------------------------------------------------------------
   $RCSfile$
   ----------------------------------------------------------------------------
   DESCRIPTION :                                                         */ /**
 * Lancemement de la création de la banque à partir des 
 * paramètres saisis dans le formulaire
 *
 * ENTREES :
 *    - $_GET["compte"] : L'enregistrement sur lequel on est positionné, ce qui 
 *								  permettra de mettre la ligne correspondant à cette banque 
 *                        en tête de liste quand celle-ci sera réaffichée
 *    - $_GET["term"] : numéro de terminal
 *    - $_POST["txt_str_compte"] : le compte comptable de la banque à créer 
 *    - $_POST["txt_str_nom"] :  nom de la banque à créer
 *    - $_POST["txt_str_adresse"] :  adresse de la banque à créer
 *    - $_POST["txt_str_complementAdresse"] :  complément de l'adresse de la banque à créer
 *    - $_POST["txt_str_codePostal"] :  code postal de la banque à créer
 *    - $_POST["txt_str_ville"] :  ville de la banque à créer
 *    - $_POST["txt_int_codeBanque"] :  code banque du RIB de la banque à créer
 *    - $_POST["txt_int_codeGuichet"] :  code guichet du RIB de la banque à créer
 *    - $_POST["txt_str_numeroCompte"] :  numéro de compte du RIB de la banque à créer
 *    - $_POST["txt_int_cle"] :  clé du RIB de la banque à créer
 *    - $_POST["txt_str_modePaiement"] :  mode de paiement de la banque à créer
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
   
	/** Bibliothèque de gestion d'erreur */
	require_once("gestion_erreur_fct.php");
	/** Bibliothèque de connexion à la base de données*/
	require_once(FICHIER_CLASSE_BDD);
	/** Bibliothèque de fonctions communes aux projets de sicomor */
	require_once("sicomor_utils_fct.php");
	/** Bibliothèque de fonctions des signatures magnétiques */
	require_once("signatures_fct.php");
	/** Bibliothèque des fonctions communes au module */
   require_once("parametres.inc.php");   
   /** Bibliothèque des fonctions communes au module noyau */
   require_once("noyau.inc.php");   
   /** Bibliothèque du module controle des pièces comptables */
   require_once("pieces_comptables_controler.inc.php"); 

   // -----------------------------------------------------------------------
   // Récupération du N° de terminal
   // -----------------------------------------------------------------------
   $int_term = (integer) utils_litParametre("term");
   sicomor_verifValiditeNumTerminal($int_term);
   

   // -----------------------------------------------------------------------
   // Vérification des droits
   // -----------------------------------------------------------------------
   utils_VerifieDroit("MAJ_CPT_BANQUES");


	// ------------------------------------------------------------------------
	// Initialisation des variables
	// ------------------------------------------------------------------------
	// Tableau des messages d'erreurs
	$tab_message['tab_messageRGErreur'] = array();
	$tab_message['tab_messageRGAlerte'] = array();
	// flag d'exécution
	$bln_OK = true;
	$bln_alerte = false;
	// Titre de la fenêtre
	$str_TitreFenetre = "Création d'une banque";
	// banque sélectionnée dans la liste
	$str_Compte = "";
	// compte comptable de la banque à créer
	$str_CompteCreation = "";
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
	//Type d'emetteur
   $str_typeEmetteur = "U";
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
	// statut
	$str_statutBIC = "";
	// Validation par forçage
	$int_forcageValide = 0;
   // mode de paiement
   $str_modePaiement = "";
   
   // -----------------------------------------------------------------
   // Récupération et mise en forme des paramètres
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
	// Construction du numéro IBAN à partir des 9 champs de 4 caractères
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
   // Connexion à la base
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
   // Début de transaction
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
			utils_JSBloquant("La table des banques est utilisée par un autre utilisateur."
                         	. "Veuillez réessayer dans quelques instants.");	
		}
	}   
	if ( $bln_OK )
	{
		$bln_OK = $connexion->LockTable("aj_pcl0", false);
		if( !$bln_OK ) 
		{
			utils_JSBloquant("La table du plan comptable local est utilisée par un autre utilisateur."
                         	. "Veuillez réessayer dans quelques instants.");	
		}
	}      

	//lock des tables métiers
	include("noyau_lock_tables_metiers.inc.php");
	
	// ---------------------------------------------------------------
	// Contrôles fonctionnels et traitements métiers
	// ---------------------------------------------------------------
	if ($bln_OK)
	{
	   // Vérification : champ obligatoire - Le compte comptable
	   if ($str_CompteCreation == '')
	   {
	      $bln_OK = false;
	      $tab_message[ 'tab_messageRGErreur' ][] = 'Le compte comptable est obligatoire.';
	   }
	
	   // VERIFICATION DE LA SAISIE D'UN BIC-IBAN
	   if ($str_codeBIC    != "" ||
	       $str_numeroIBAN != "")
	   {
	      // Vérification : code BIC obligatoire
	      if( $str_codeBIC == '' )
	      {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le code BIC est obligatoire.";
	      }
	      // Vérifier la syntaxe du code BIC
	      else if (!noyau_controleBIC($str_codeBIC, &$tab_message))
	      {
	         $bln_OK = false;
	      }
	       
	      // Vérification : numéro IBAN obligatoire
	      if( $str_numeroIBAN == '' )
	      {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le numéro IBAN est obligatoire.";
	      }
	      // Vérifier la syntaxe du numero IBAN
	      else if (!noyau_controleIBAN($str_numeroIBAN, &$tab_message))
	      {
	         $bln_OK = false;
	      }
	   }
	   
	   if (trim($str_typeEmetteur) !='U' && trim($str_typeEmetteur)!='R')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le type d'émetteur doit être URSSAF ou RSI.";
	   }

	   if (trim($str_banqueDeFrance) !='O' && trim($str_banqueDeFrance)!='N')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "Le type Banque de France doit être renseigné.";
	   }

	   if (trim($str_restitutionMontantSepa) !='G' && trim($str_restitutionMontantSepa)!='U')
	   {
	         $bln_OK = false;
	         $tab_message['tab_messageRGErreur'][] = "La restitution du montant SEPA doit être renseigné.";
	   }
	   	
	   // VERIFICATION DE LA SAISIE D'UN RIB
	   if ($str_codeBanque   != "" ||
	       $str_codeGuichet  != "" ||
	       $str_numCompte    != "" ||
	       $str_cleRib       != "")
	   {
	      // Vérifier que le code banque est un numérique
	      if ($str_codeBanque == "")
	      {
	         $str_codeBanque = '0';
	      }
	      else if (!utils_estNumerique($str_codeBanque))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le code banque doit être un numérique.";
	      }
	       
	      // Vérifier que le code guichet est un numérique
	      if ($str_codeGuichet == "")
	      {
	         $str_codeGuichet = '0';
	      }
	      else if (!utils_estNumerique($str_codeGuichet))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le code guichet doit être un numérique.";
	      }
	      	
	      // Vérifier que la clé du RIB est un numérique
	      if ($str_cleRib == "")
	      {
	         $str_cleRib = '0';
	      }
	      else if (!utils_estNumerique($str_cleRib))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "La clé doit être un numérique.";
	      }
	      	
	      // Vérifier que le RIB est bien formé
	      if (!noyau_controle_rib($str_codeBanque, $str_codeGuichet, $str_numCompte, $str_cleRib))
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le RIB saisi est incorrect.";
	      }
	   }
	   // Les coordonnées RIB ne sont pas renseignées, 
	   // les mettre à zéro sinon on a une erreur à l'INSERT dans la table
	   else 
	   {
	      $str_codeBanque = '0';
	      $str_codeGuichet = '0';
	      $str_cleRib = '0';
	   }
	   
	   // Vérifier si une banque est déjà associée au compte comptable saisi     
      if (!parametres_banques_select_par_code($connexion, $rst_Selection, 0, $str_CompteCreation)) 
      {
         $bln_OK = false;
         $tab_message['tab_messageRGErreur'][] = "Le compte bancaire $str_CompteCreation existe déjà pour une autre banque.";
      }
      else {
      	$rst_Selection->Close();
		}
	
	   /* Vérifier le compte comptable */
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
	      	
	      // Vérifier si le compte est ouvert
	      if ($str_Statut != 'O')
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le compte $str_CompteCreation n'est pas ouvert.";
	      }
	      	
	      // Vérifier si le compte n'est pas imputable en comptabilité générale
	      if ($str_imputGenerale != 'I' )
	      {
	         $bln_OK = false;
	         $tab_message[ 'tab_messageRGErreur' ][] = "Le compte $str_CompteCreation n'est pas imputable en comptabilité générale.";
	      }
	      
			// Le compte et aucun de ses père Minimum au PCN ne doit avoir la propriété ACOSS. 
         if ($bln_OK)
         {
            $bln_OK = pieces_comptables_controler_compte_acoss(&$connexion, $int_exercice, CODE_GESTION_BANQUES, $str_CompteCreation,  &$tab_message);
            
         }
	   }
	}


   // Vérification de l'égalité entre le code pays du BIC et le code pays de l'IBAN s'il sont renseignés et
   // si l'alerte n'a pas été confirmée par l'utilisateur
   if ($bln_OK && $bln_alertesConfirmees==false && trim($str_codeBIC)<>'' && trim($str_numeroIBAN)<>'')
   {
      // Si la valeur "pays" de l'IBAN est égal à 'FR', alors la valeur "pays" du BIC doit être égale à 'FR' ou 'NC' ou 'PF' ou 'WF', GP, GF, MQ, RE, YT, MC
      // Si la valeur "pays" de l'IBAN est différente de 'FR', alors la valeur "pays" du BIC doit être égale à la valeur "pays" de l'IBAN
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
            $tab_message['tab_messageRGErreur'][] = "Le code pays du BIC est différent du code pays de l'IBAN.";
         }
      }
   }
   
   //vérification sur les information sur les mode de transfert automatique
   //si c'est une banque de France, on ne doit pas être en automatique
   if($str_banqueDeFrance == "O" && $str_modeTransfert == "A"){
      $bln_OK  = false;
      $tab_message['tab_messageRGErreur'][] = "L'activation de l'option transfert automatique est interdite"
            ." si le compte de banque a le type 'Banque de France' à 'Oui'.";
   }
    
   // contrôle des champs ID, et ne doivent pas comporter des caractère spéciaux et ne doivent pas être vide
   else if($str_modeTransfert == "A"){
      if($str_userID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'User ID' ne doit pas être vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_userID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'User ID' ne doit pas contenir des caractères spéciaux.";
      }
   
      if($str_hostID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Host ID' ne doit pas être vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_hostID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Host ID' ne doit pas contenir des caractères spéciaux.";
      }
   
      if($str_partnerID == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Partner ID' ne doit pas être vide.";
      }
      else if(!preg_match("#^[a-z0-9A-Z]+$#i", $str_partnerID)){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Partner ID' ne doit pas contenir des caractères spéciaux.";
      }
      
      if($str_nomBanqueVir == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Nom de la banque' ne doit pas être vide.";
      }
      if($str_numFax == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Numéro de fax' ne doit pas être vide.";
      }
      if($str_numEmetteur == ''){
         $bln_OK  = false;
         $tab_message['tab_messageRGErreur'][] = "Le champ 'Numéro émetteur' ne doit pas être vide.";
      }
   }

        
	// Formattage et affichage d'un message d'avertissement si des anomalies ont
	// été détectées pendant les contrôles préliminaires.
	if( !$bln_OK )
	{
	   $str_messageErreur = sicomor_construireMessage( $tab_message['tab_messageRGErreur'] );
	   utils_JSAvertissementScroll( $str_messageErreur );
	}

	// ---------------------------------------------------------------
	// Création de l'enregistrement dans la base
	// ---------------------------------------------------------------
	if ($bln_OK)
	{
	   // mise en forme des coordonnées bancaires
	   // code banque
	   if( $str_codeBanque ) {
	      $str_codeBanque = utils_completeChaine($str_codeBanque, 5, "0");
	   }
	   // code guichet
	   if( $str_codeGuichet ) {
	      $str_codeGuichet = utils_completeChaine($str_codeGuichet, 5, "0");
	   }
	   // numéro de compte
	   if ( $str_numCompte ) {
	      $str_numCompte = utils_completeChaine($str_numCompte, 11, "0");
	   }
	   // clé RIB
	   if( $str_cleRib ) {
	      $str_cleRib = utils_completeChaine($str_cleRib, 2, "0");
	   }

	   // ---------------------------------------------------------------
	   // Création de l'enregistrement dans la base
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


	   if (  $connexion->gererRequeteSQLMiseAJour($str_SQL, "Erreur lors de la création de la banque.") != 1 )
	   {
	      utils_JSBloquant("Erreur lors de la création de la banque.");
	      utils_JSCommande("parent.fraDetail.document.getElementById('txt_str_compteComptable').focus();");
	      $bln_OK = false;
	   }
	}

	// ---------------------------------------------------------------
	// Signature Magnétique
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
	   /* si des alertes ont été détectées */
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

            // Concaténation dans une variables de tous les paramètres de la page, ceci pour avoir
            // les valeurs des zones de saisie
            $str_parametres='';
         	foreach($_REQUEST as $str_NomVariable => $str_ValeurVariable)
         	{
         	   // Le paramètre est déjà présent dans le $_REQUEST mais avec la mauvaise valeur. On l'enlève donc de la
         	   // concaténation pour être plus propre. Ce paramètre sera défini correctement dans la fonction relancePHP
         	   if ($str_NomVariable != "alertesConfirmees") $str_parametres .="&".$str_NomVariable."=".addslashes($str_ValeurVariable);
         	}
            utils_JSConfirmeScroll("<b>Liste des alertes à valider : </b><br>" . $str_messageAlerte,
                                   "relancePHP('"
                                 . "&term=" . $int_term
                                 . "&majDetail=N"
                                 . $str_parametres. "')",  // L'utilisateur confirme les alertes : on relance le PHP avec confirmation
                                   "self.close()");        // sinon, on sort
         }
      }


	   /* si aucune alerte n'a été détectée */
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
