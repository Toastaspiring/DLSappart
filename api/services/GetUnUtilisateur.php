<?php
namespace api\services;
use modele\DAO;
use modele\Utilisateur;
use DOMDocument;

/*
Projet TraceGPS - services web
fichier : api/services/GetTousLesUtilisateurs.php
Dernière mise à jour : 21/11/2023 par Ethan DIVET
Rôle : ce service permet à un utilisateur authentifié d'obtenir la liste de tous les utilisateurs (de niveau 1)

Le service web doit recevoir 3 paramètres :
    pseudo : le pseudo de l'utilisateur
    mdp : le mot de passe de l'utilisateur hashé en sha1
    lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
    
Les paramètres doivent être passés par la méthode GET :
    http://<hébergeur>/tracegps/api/GetTousLesUtilisateurs?pseudo=callisto&mdp=13e3668bbee30b004380052b086457b014504b3e&lang=xml
*/

// connexion du serveur web à la base MySQL
$dao = new DAO();
	
// Récupération des données transmises
$token = ( empty($this->request['token'])) ? "" : $this->request['token'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
$unUtilisateur = null;

if ($lang != "xml") $lang = "json";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET")
{	$msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
}
else {
    // Les paramètres doivent être présents
    if ( $token == "" )
    {	$msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    }
    else
    {	if ( !$dao->getConnexionToken($token) ) {
    		$msg = "Erreur : authentification incorrecte.";
    		$code_reponse = 401;
        }
    	else 
    	{	// récupération de la liste des utilisateurs à l'aide de la méthode getTousLesUtilisateurs de la classe DAO
    	    $unUtilisateur = $dao->getUtilisateur($token);

    	    if ($unUtilisateur == null) {
    			$msg = "Aucun utilisateur.";
    			$code_reponse = 200;
    	    }
    	    else {
    			$msg = "utilisateur recupere.";
    			$code_reponse = 200;
    	    }
    	}
    }
}
// ferme la connexion à MySQL :
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";      // indique le format XML pour la réponse
    $donnees = creerFluxXML($msg, $unUtilisateur);
}
else {
    $content_type = "application/json; charset=utf-8";      // indique le format Json pour la réponse
    $donnees = creerFluxJSON($msg, $unUtilisateur);
}

// envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// fin du programme (pour ne pas enchainer sur les 2 fonctions qui suivent)
exit;

// ================================================================================================
 
// création du flux XML en sortie
function creerFluxXML($msg, $lesUtilisateurs)
{	
    // TO DO LATER 


	// Mise en forme finale
	// $doc->formatOutput = true;
	
	// renvoie le contenu XML
	//return $doc->saveXML();
}

// ================================================================================================

// création du flux JSON en sortie
function creerFluxJSON($msg, $unUtilisateur)
{
    /* Exemple de code JSON
        {
            "data": {
                "reponse": "2 utilisateur(s).",
                "donnees": {
                    "lesUtilisateurs": [
                        {
                            "id": "2",
                            "pseudo": "callisto",
                            "adrMail": "delasalle.sio.eleves@gmail.com",
                            "numTel": "22.33.44.55.66",
                            "niveau": "1",
                            "dateCreation": "2018-08-12 19:45:23",
                            "nbTraces": "2",
                            "dateDerniereTrace": "2018-01-19 13:08:48"
                        },
                        {
                            "id": "3",
                            "pseudo": "europa",
                            "adrMail": "delasalle.sio.eleves@gmail.com",
                            "numTel": "22.33.44.55.66",
                            "niveau": "1",
                            "dateCreation": "2018-08-12 19:45:23",
                            "nbTraces": "0"
                        }
                    ]
                }
            }
        }
     */
    
    // construction d'un tableau contenant les utilisateurs
    $unObjetUtilisateur = array();

    if ($unUtilisateur) {
    $unObjetUtilisateur["id"] = $unUtilisateur->getId();
    $unObjetUtilisateur["nom"] = $unUtilisateur->getNom();
    $unObjetUtilisateur["prenom"] = $unUtilisateur->GetPrenom();
    $unObjetUtilisateur["mdpSha1"] = $unUtilisateur->getMdpSha1();
    $unObjetUtilisateur["adrMail"] = $unUtilisateur->getAdrMail();
    $unObjetUtilisateur["dateCreation"] = $unUtilisateur->getDateCreation();
    $unObjetUtilisateur["nbAnnonces"] = $unUtilisateur->getNbAnnonce();
    }
    // construction de l'élément "lesUtilisateurs"
    $elt_utilisateur = ["Utilisateur" => $unObjetUtilisateur];
    
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg, "donnees" => $elt_utilisateur];

    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    return json_encode($elt_racine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ================================================================================================
?>