<?php

require('include/fpdm/fpdm.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/*

    Pour eviter de perdre des données, bien qu'improbable,
    DECRYPT/ENCRYPT on été remplacés par des fonctions vides.
    convertir XDECRYPT et XENCRYPT en ENCRYPT et DECRYPT pour activer le cryptage des données personelles.

*/

function XDECRYPT($input)
{
    return $input;
}
function XENCRYPT($input)
{
    return $input;
}

/*
 EXPORT ARRAY
 on met un array en dehors du scope 
 pour récuperer les données partout dans le code php mais le remonter plus haut dans le html,
 sans faire milles requetes bidons

 Because I AM SPEEEEEEEEEEEEEED.
*/

$_EXPORT_ARRAY = (array) null;

//POST, GET, BOTH, pcq on modif, et on affiche, 
//pas besoin d'une grande conne de page avec un bouton retour

if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    //Editer contrat
    if ($_POST['action'] == "edit") {

        $arrData = array(


            'vehicule_id'  => CLEAN($_POST['vehicule']),
            'kilometrage'  => CLEAN($_POST['kilometrage']),
            'c_tarif'      => CLEAN($_POST['ctarifs']),

            //Dates de location
            'date_depart'  => CLEAN(str_replace('T', ' ', $_POST['date_livraison'])), //input datetime-local impose un séparateur T au lieu d'un espace 
            'date_arrivee' => CLEAN(str_replace('T', ' ', $_POST['date_reception'])),

            //Loueur Responsable
            'nom' => CLEAN($_POST['nom']),
            'prenom' => CLEAN($_POST['prenom']),
            'date_naissance' => CLEAN($_POST['date_naissance']),
            'adresse' => CLEAN($_POST['adresse']),
            'num_permis' => CLEAN($_POST['num_permis']),
            'date_permis' => CLEAN($_POST['date_permis']),
            'lieu_permis' => CLEAN($_POST['lieu_permis']),
            'tel_client' => CLEAN($_POST['tel_client']),
            'c_nom' => CLEAN($_POST['c_nom']),

            //Conducteur secondaire
            'c_nom' => CLEAN($_POST['c_nom']),
            'c_prenom' => CLEAN($_POST['c_prenom']),
            'c_date_naissance' => CLEAN(str_replace('T', ' ', $_POST['c_date_naissance'])),
            'c_lieu_naissance' => CLEAN($_POST['c_lieu_naissance']),
            'c_adresse'     => CLEAN($_POST['c_adresse']),
            'c_num_permis'  => CLEAN($_POST['c_num_permis']),
            'c_date_permis' => CLEAN($_POST['c_date_permis']),
            'c_lieu_permis' => CLEAN($_POST['c_lieu_permis']),

            //Transferts
            'transfert_sortie' => CLEAN($_POST['transfert_sortie']),
            'transfert_entree' => CLEAN($_POST['transfert_entree']),

            //Carburant
            'carburant_livraison' => CLEAN($_POST['carburant_livraison']),
            'carburant_reception' => CLEAN($_POST['carburant_reception']),

            //Observations
            'observations_livraison' => CLEAN($_POST['description']),
            'observations_restitution' => CLEAN($_POST['description2']),

            //Paiement
            'c_cb'  => CLEAN($_POST['c_cb']),
            'c_esp' => CLEAN($_POST['c_esp']),
            'c_chq' => CLEAN($_POST['c_chq']),

            'r_cb'  => CLEAN($_POST['r_cb']),
            'c_esp' => CLEAN($_POST['r_esp']),
            'r_chq' => CLEAN($_POST['r_chq'])

        );


        $_UPDATE_ACCR = UPDATE("contrats", $arrData, "WHERE contrat = " . CLEAN(DECRYPT($_GET['f'])), false);

        if ($_UPDATE_ACCR) {
            $checker->innertext = 'Le contrat a bien été modifiée';
            $checker->{'class'} .= " green";

            //Set Historique
            // $_INSERT_JOURNAL = INSERT("journal", array(
            //     "date"   => date('Y-m-d H:i:s', strtotime('+4 hours')),
            //     "login"  => $_SESSION['userID'],
            //     "action" => json_encode(array('edition', $arrData)),
            //     "factureID" => CLEAN($_GET['f'])

            // ), false);
        } else {
            $checker->innertext = "Un problème est survenu lors de la modification du contrat";
            $checker->{'class'} .= " red";
        }
    }


    if ($_POST['action'] == 'new') {
        $toggle1 = $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';
    }

    if ($_POST['action'] == 'create') {
        $arrData = array(

            'vehicule_id'  => CLEAN($_POST['vehicule']),
            'contrat'      => CLEAN(date('Ymdhi')),
            'c_tarif'      => CLEAN($_POST['ctarifs']),

            //Dates de location
            'date_depart'  => CLEAN(str_replace('T', ' ', $_POST['date_livraison'])),
            'date_arrivee' => CLEAN(str_replace('T', ' ', $_POST['date_reception'])),

            //Loueur Responsable
            'nom' => CLEAN($_POST['nom']),
            'prenom' => CLEAN($_POST['prenom']),
            'date_naissance' => CLEAN($_POST['date_naissance']),
            'adresse' => CLEAN($_POST['adresse']),
            'num_permis' => CLEAN($_POST['num_permis']),
            'date_permis' => CLEAN($_POST['date_permis']),
            'lieu_permis' => CLEAN($_POST['lieu_permis']),
            'tel_client' => CLEAN($_POST['tel_client']),
            'c_nom' => CLEAN($_POST['c_nom']),

            //Conducteur secondaire
            'c_nom' => CLEAN($_POST['c_nom']),
            'c_prenom' => CLEAN($_POST['c_prenom']),
            'c_date_naissance' => CLEAN(str_replace('T', ' ', $_POST['c_date_naissance'])),
            'c_lieu_naissance' => CLEAN($_POST['c_lieu_naissance']),
            'c_adresse'     => CLEAN($_POST['c_adresse']),
            'c_num_permis'  => CLEAN($_POST['c_num_permis']),
            'c_date_permis' => CLEAN($_POST['c_date_permis']),
            'c_lieu_permis' => CLEAN($_POST['c_lieu_permis']),

            //Transferts
            'transfert_sortie' => CLEAN($_POST['transfert_sortie']),
            'transfert_entree' => CLEAN($_POST['transfert_entree']),

            //Carburant
            'carburant_livraison' => CLEAN($_POST['carburant_livraison']),
            'carburant_reception' => CLEAN($_POST['carburant_reception']),

            //Observations
            'observations_livraison' => CLEAN($_POST['description']),
            'observations_restitution' => CLEAN($_POST['description2']),

            //Paiement
            'c_cb' => CLEAN($_POST['c_cb']),
            'c_esp' => CLEAN($_POST['c_esp']),
            'c_chq' => CLEAN($_POST['c_chq']),

            'r_cb' => CLEAN($_POST['r_cb']),
            'c_esp' => CLEAN($_POST['c_esp']),
            'r_chq' => CLEAN($_POST['r_chq'])

        );

        $_insert = INSERT('contrats', $arrData, true);
    }
} else {

    /**
     * 
     *  GET only
     * 
     */

    $_EXPORT_ID = (string) null;
    if (isset($_GET['f']) && !empty($_GET['f'])) {

        //Open modal window

        $template->find('[value=create]', 0)->value = 'edit';
        $template->find('h3', 0)->innertext = 'Editer une entrée';

        $_GET_SINGLE_CONTRAT = SELECT(
            "contrats",
            "contrats.*,
            voitures.marque,
            voitures.type,
            voitures.immatriculation,
            voitures.tarif_1a7 as tarif,
            (SELECT transferts.prix FROM transferts WHERE transferts.range=contrats.transfert_sortie AND transferts.type=1) as sortie, 
            (SELECT transferts.prix FROM transferts WHERE transferts.range=contrats.transfert_entree AND transferts.type=2) as entree",
            "LEFT JOIN voitures ON voitures.id = contrats.vehicule_id WHERE contrat='" . CLEAN(DECRYPT($_GET['f'])) . "' ORDER BY date DESC",
            false
        );

        if ($_GET_SINGLE_CONTRAT) {

            //Open window if contrat exists
            $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';

            //TRICK
            $TRICK = (array) null;
            while ($DATA = fetch_array($_GET_SINGLE_CONTRAT)) {

                $difference_livraison_reception = compare_dates($DATA['date_depart'], $DATA['date_arrivee']); //Nb jours
                $total_ht                       = $difference_livraison_reception * (!empty($DATA['c_tarif']) ? $DATA['c_tarif'] : $DATA['tarif']);           //Total HT
                $transfert                      = $DATA['sortie'] + $DATA['entree']; //Prix Transferts Entree + Prix Sortie
                $ht_plus_transfert              = $total_ht + $transfert;
                $taux                           = 8.5;
                $tva                            = $ht_plus_transfert * $taux / 100;
                $total_ttc                      = $ht_plus_transfert + $tva;

                $template->find('[name=ht]', 0)->value        = $ht_plus_transfert . '€';
                $template->find('[name=transfert]', 0)->value = $transfert . '€';
                $template->find('[name=ttc]', 0)->value = $total_ttc . '€';


                $template->find('[id=btarifs]', 0)->innertext     = $DATA['tarif'] . '€';
                $template->find('[name=ctarifs]', 0)->value       = $DATA['c_tarif'];
                $template->find('[name=kilometrage]', 0)->value = $DATA['kilometrage'];


                //TOP
                $template->find('[name=ncontrat]', 0)->value = $DATA['contrat'];
                $template->find('[name=date_livraison]', 0)->value = dateToFrench($DATA['date_depart'], 'Y-m-d\TH:i');
                $template->find('[name=date_reception]', 0)->value = dateToFrench($DATA['date_arrivee'], 'Y-m-d\TH:i');

                //Caracteristiques
                $template->find('[name=marque]', 0)->value = $DATA['marque'];
                $template->find('[name=type]', 0)->value = $DATA['type'];
                $template->find('[name=immatriculation]', 0)->value = $DATA['immatriculation'];

                //Conducteur 1
                $template->find('[name=nom]', 0)->value            = $DATA['nom'];
                $template->find('[name=prenom]', 0)->value         = $DATA['prenom'];
                $template->find('[name=date_naissance]', 0)->value = dateToFrench($DATA['date_naissance'], "Y-m-d");
                $template->find('[name=adresse]', 0)->value        = $DATA['adresse'];
                $template->find('[name=num_permis]', 0)->value     = $DATA['num_permis'];
                $template->find('[name=date_permis]', 0)->value    = dateToFrench($DATA['date_permis'], "Y-m-d");
                $template->find('[name=lieu_permis]', 0)->value    = $DATA['lieu_permis'];
                $template->find('[name=tel_client]', 0)->value     = $DATA['tel_client'];


                //Conducteur 2
                $template->find('[name=c_nom]', 0)->value            = $DATA['c_nom'];
                $template->find('[name=c_prenom]', 0)->value         = $DATA['c_prenom'];
                $template->find('[name=c_date_naissance]', 0)->value = dateToFrench($DATA['c_date_naissance'], "Y-m-d");
                $template->find('[name=c_adresse]', 0)->value        = $DATA['c_adresse'];
                $template->find('[name=c_num_permis]', 0)->value     = $DATA['c_num_permis'];
                $template->find('[name=c_date_permis]', 0)->value    = dateToFrench($DATA['c_date_permis'], "Y-m-d");
                $template->find('[name=c_lieu_permis]', 0)->value    = $DATA['c_lieu_permis'];
                $template->find('[name=c_tel_client]', 0)->value     = $DATA['tel_client'];

                //Transferts
                //Sortie
                $template->find('input[name="transfert_sortie"][value="' . $DATA['transfert_sortie'] . '"]', 0)->{'checked'} = 'checked';
                //Rentrée
                $template->find('input[name="transfert_entree"][value="' . $DATA['transfert_entree'] . '"]', 0)->{'checked'} = 'checked';

                //Carburant
                $template->find('input[name="carburant_livraison"][value="' . $DATA['carburant_livraison'] . '"]', 0)->{'checked'} = 'checked';
                $template->find('input[name="carburant_reception"][value="' . $DATA['carburant_reception'] . '"]', 0)->{'checked'} = 'checked';

                //Observations
                $template->find('[name=description]',   0)->value = CLEAN($DATA['observations_livraison']);
                $template->find('[name=description2]', 0)->value =  CLEAN($DATA['observations_restitution']);

                //Paiement
                $template->find('[name=c_cb]', 0)->value =  CLEAN($DATA['c_cb']);
                $template->find('[name=c_esp]', 0)->value =  CLEAN($DATA['c_esp']);
                $template->find('[name=c_chq]', 0)->value =  CLEAN($DATA['c_chq']);

                $template->find('[name=r_cb]', 0)->value =  CLEAN($DATA['r_cb']);
                $template->find('[name=r_esp]', 0)->value =  CLEAN($DATA['r_esp']);
                $template->find('[name=r_chq]', 0)->value =  CLEAN($DATA['r_chq']);
            }
        }
    }


    if (isset($_GET['p']) && !empty($_GET['p'])) {

        $_GET_PRINT_CONTRAT = SELECT(
            "contrats",
            "contrats.*,
            voitures.marque,
            voitures.type,
            voitures.immatriculation,
            voitures.tarif_1a7 as tarif,
            (SELECT transferts.prix FROM transferts WHERE transferts.range=contrats.transfert_sortie AND transferts.type=1) as sortie, 
            (SELECT transferts.prix FROM transferts WHERE transferts.range=contrats.transfert_entree AND transferts.type=2) as entree",
            "LEFT JOIN voitures ON voitures.id = contrats.vehicule_id WHERE contrat='" . CLEAN($_GET['p']) . "' ORDER BY date DESC",
            false
        );

        $pdf  = new FPDM("output.pdf"); //Source

        if ($_GET_PRINT_CONTRAT) {

            $TEMP = (string) null;
            while ($DATA = fetch_array($_GET_PRINT_CONTRAT)) {

                $difference_livraison_reception = compare_dates($DATA['date_depart'], $DATA['date_arrivee']); //Nb jours
                $total_ht                       = $difference_livraison_reception * (!empty($DATA['c_tarif']) ? $DATA['c_tarif'] : $DATA['tarif']);           //Total HT
                $transfert                      = $DATA['sortie'] + $DATA['entree']; //Prix Transferts Entree + Prix Sortie
                $ht_plus_transfert              = $total_ht + $transfert;
                $taux                           = 8.5;
                $tva                            = $ht_plus_transfert * $taux / 100;
                $total_ttc                      = $ht_plus_transfert + $tva;

                $FIELDS = array(

                    //Caractéristiques du véhicule
                    'marque' => ($DATA['marque']),
                    'type'   => ($DATA['type']),
                    'immat'  => ($DATA['immatriculation']),

                    //Loueur Responsable
                    'nom' => ($DATA['nom']),
                    'prenom' => ($DATA['prenom']),
                    'date_naissance' => dateToFrench($DATA['date_naissance'], 'd/m/Y'),
                    'adresse' => ($DATA['adresse']),
                    'num_permis' => ($DATA['num_permis']),
                    'date_permis' => ($DATA['date_permis']),
                    'lieu_permis' => ($DATA['lieu_permis']),
                    'tel_client' => ($DATA['tel_client']),
                    'c_nom' => ($DATA['c_nom']),

                    //Conducteur secondaire
                    'c_nom' => ($DATA['c_nom']),
                    'c_prenom' => ($DATA['c_prenom']),
                    'c_date_naissance' => ($DATA['c_date_naissance']),
                    'c_lieu_naissance' => ($DATA['c_lieu_naissance']),
                    'c_adresse'     => ($DATA['c_adresse']),
                    'c_num_permis'  => ($DATA['c_num_permis']),
                    'c_date_permis' => ($DATA['c_date_permis']),
                    'c_lieu_permis' => ($DATA['c_lieu_permis']),

                    //Dates de location
                    'date_livraison' => dateToFrench($DATA['date_depart'], 'd/m/Y H:i'),
                    'date_reception' => dateToFrench($DATA['date_arrivee'], 'd/m/Y H:i'),
                    'total_jours'    => $difference_livraison_reception,

                    //Informations de location
                    'tarif_jour'       => ($DATA['tarif']) . "€",
                    'date_livraison2'  => ($DATA['date_depart']),
                    'total_jours2'     => $difference_livraison_reception,

                    //Opérations
                    'total_ht'         => $ht_plus_transfert . '€',
                    'total_transfert'  => $transfert . '€',
                    'tva'              => $tva . '€',
                    'total_ttc'        => $total_ttc . '€',

                    //Noïce ! =D
                    'sortie' . (!empty($DATA['transfert_sortie']) && $DATA['transfert_sortie'] != NULL ? $DATA['transfert_sortie'] : '1')  => $DATA['sortie'] . '€', //--> PDF = field "sortieX", ATTENTION, si c'est vide "sortie" sans X n'existes pas
                    'entree' . (!empty($DATA['transfert_entree']) && $DATA['transfert_entree'] != NULL ? $DATA['transfert_entree'] : '1')  => $DATA['entree'] . '€',

                    //Carburant
                    'liv' . $DATA['carburant_livraison'] => 'X',
                    'rec' . $DATA['carburant_reception'] => 'X',

                    //Observations
                    'description'   => $DATA['observations_livraison'],
                    'description2' => $DATA['observations_restitution'],

                    //Paiement
                    'c_cb' => $DATA['c_cb'],
                    'c_esp' => $DATA['c_esp'],
                    'c_chq' => $DATA['c_chq'],

                    'r_cb' => $DATA['r_cb'],
                    'r_esp' => $DATA['r_esp'],
                    'r_chq' => $DATA['r_chq'],


                );

                $TEMP = "Contrat N° " . $DATA['contrat'];
            }

            // echo "<pre>";
            // print_r($FIELDS);
            // echo "</pre>";

            //DATA ARRAY
            $pdf->Load($FIELDS, true);
            //MERGE
            $pdf->Merge();
            // //FLATTEN
            // $pdf->Flatten();
            // //OUTPUT
            $pdf->Output('I', $TEMP . '.pdf'); //I pour Inline (dans le browser), D pour Download, F pour save sur le serveur



        } else {

            $checker->innertext = "Un problème est survenu lors de la récupération du profil";
            $checker->addClass('red');
        }
    }
}


/**
 * 
 *  BOTH
 * 
 */

//Get contrats + voitures,
//Si aucun contrat aujourdhui "NOW" (En location) regarde status de la voiture seule (Dispo / Reparation / Desactivé)

$_GET_CONTRATS = SELECT(
    'contrats',
    "contrats.*,
    voitures.marque,
    voitures.type,
    voitures.immatriculation,
    voitures.status as vstatus,
    datetime('now') as now,
    strftime('%s', contrats.date_depart) - strftime('%s', 'now') as diff_depart_seconds,
    strftime('%s', contrats.date_arrivee) - strftime('%s', 'now') as diff_arrivee_seconds,
    printf('%02d:%02d', abs((strftime('%s', contrats.date_depart) - strftime('%s', 'now')) / 3600), abs(((strftime('%s', contrats.date_depart) - strftime('%s', 'now')) % 3600) / 60)) as diff_depart,
    printf('%02d:%02d', abs((strftime('%s', contrats.date_arrivee) - strftime('%s', 'now')) / 3600), abs(((strftime('%s', contrats.date_arrivee) - strftime('%s', 'now')) % 3600) / 60)) as diff_arrivee",
    'LEFT JOIN voitures ON voitures.id = contrats.vehicule_id
    WHERE 1',
    false
);

if ($_GET_CONTRATS) {
    $d = (int) 0;

    $table = $template->find('table[id=contrats]', 0);
    $tbody = $table->find('tbody', 0);
    $tr    = $tbody->find('tr', 0);

    $STACK = (string) null; 

    while ($DATA = fetch_array($_GET_CONTRATS)) {

        $tr->find('td', 0)->find('a', 0)->innertext = $DATA['contrat'];
        $tr->find('td', 1)->find('a', 0)->innertext = $DATA['marque'];
        $tr->find('td', 2)->find('a', 0)->innertext = $DATA['nom'] . ', ' . $DATA['prenom'];
        $tr->find('td', 3)->find('a', 0)->innertext = $DATA['immatriculation'];
        $tr->find('td', 4)->find('a', 0)->innertext = dateToFrench($DATA['date_depart'], 'd/m/Y') . ' <i class="fas fa-arrows-alt-h"></i> ' . dateToFrench($DATA['date_arrivee'], 'd/m/Y') . ' <span class="status"><i class="fal fa-calendar"></i> ' . compare_dates($DATA['date_depart'], $DATA['date_arrivee']) . ' jours</span>';
        $tr->find('td', 5)->find('a', 0)->innertext = $DATA['tel_client'];
        // $tr->find('td', 6)->find('a', 0)->find('span', 0)->innertext = ($DATA['now'] >= $DATA['d_depart'] && $DATA['now'] <= $DATA['d_arrivee'] ? "En Location" : display_status($DATA['vstatus']));
        $tr->find('td', 6)->find('a', 0)->find('span', 0)->innertext =
            (function ($DATA) {

                if ($DATA['diff_depart_seconds'] <= 3600 && $DATA['diff_depart_seconds'] >= 0) {
                    $string = "Preparation " . $DATA['diff_depart'];
                } elseif ($DATA['diff_depart_seconds'] >= 0 && $DATA['diff_arrivee_seconds'] >= 0) {
                    $string = "En Location";
                } elseif ($DATA['diff_depart_seconds'] < 0 && $DATA['diff_arrivee_seconds'] < 0 && $DATA['status'] == 1) {
                    $string = "Terminé";
                } elseif ($DATA['diff_depart_seconds'] < 0 && $DATA['diff_arrivee_seconds'] < 0 && $DATA['status'] == 0) {
                    $string = "En Retard ". $DATA['diff_arrivee'];
                } elseif ($DATA['diff_depart_seconds'] < 0 && $DATA['diff_arrivee_seconds'] > 0) {
                    $string = "En Location";
                }

                return $string;
            })($DATA);

        foreach ($tr->find('a') as $link) {
            //Si le <a> a la class "print"
            if (strpos($link->{'class'}, 'print') !== false) {
                $link->{'href'} = '?p=' . $DATA['contrat'];
                $link->{'id'}   = 'p' . $DATA['id'];
            } else {
                //sinon lien normal
                $link->{'href'} = url('manager_home&f=' . ENCRYPT($DATA['contrat']));
            }
        }

        $STACK .= $tr;
    }
    $tbody->innertext = $STACK;
} else {
    $checker->innertext = "Un problème est survenu lors de la récupération des contrats.";
    $checker->{'class'} .= " red";
}

$_LISTE_VEHICULE = SELECT('voitures', '*', 'WHERE 1', false);
$UI_SELECT = $template->find('[name=vehicule]', 0);
$UI_OPTION = $UI_SELECT->find('option', 0);

$TON_MOMON = (string) null;

if ($_LISTE_VEHICULE) {
    while ($DATA = fetch_array($_LISTE_VEHICULE)) {
        $UI_OPTION->value     = $DATA['id'];
        $UI_OPTION->innertext = $DATA['marque'] . ' -- ' . $DATA['immatriculation'];
        $TON_MOMON .= $UI_OPTION;
    }
    $UI_SELECT->innertext = $TON_MOMON;
}



/******
 * 
 *  USING EXPORTED DATA
 * 
 */
//Unhide cardbox
// $cards->removeClass('d-none');

// //String stack
// $STACK_CARD = (string) null;

// //Loop through the array
// foreach ($_EXPORT_ARRAY as $key => $value) {

//     //Set the title
//     $card->find('span', 0)->innertext = $key;

//     //Set the value
//     $card->find('span', 1)->innertext = $value;

//     if ($key !== 'lycees') {
//         //Add the card to the stack
//         $STACK_CARD .= $card;
//     }
// }
//reinsert the stack
// $cards->innertext = $STACK_CARD;
//DONE.
if (isset($_SESSION['type']) &&  $_SESSION['type'] == '1') {
}
// echo "<pre>";
// print_r($_EXPORT_ARRAY['lycees']);
// echo "</pre>";


// phpinfo();