<?php

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


if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    //Editer contrat
    if ($_POST['action'] == "edit") {

        $arrData = array(

            'marque'  => CLEAN($_POST['marque']),
            'type'  => CLEAN($_POST['type']),
            'immatriculation'  => CLEAN($_POST['immatriculation']),
            'places'  => CLEAN($_POST['places']),
            'portes'  => CLEAN($_POST['portes']),
            'climatisation'  => CLEAN($_POST['climatisation']),
            'energie'  => CLEAN($_POST['energie']),

        );


        $_UPDATE_ACCR = UPDATE("voitures", $arrData, "WHERE id = " . CLEAN(DECRYPT($_GET['f'])), false);

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
        //Fill the select energie
        $UI_SELECT = $template->find('[name=energie]', 0);
        $UI_OPTION = $UI_SELECT->find('option', 0);
        $STACK_SELECT = (string) null;
        foreach (display_energy(0, false, true) as $e => $nergie) {
            $UI_OPTION->value = $e;
            $UI_OPTION->innertext = $nergie;
            $STACK_SELECT .= $UI_OPTION;
        }
        $UI_SELECT->innertext = $STACK_SELECT;
    }

    if ($_POST['action'] == 'create') {
        $arrData = array(

            'marque'  => CLEAN($_POST['marque']),
            'type'  => CLEAN($_POST['type']),
            'immatriculation'  => CLEAN($_POST['immatriculation']),
            'places'  => CLEAN($_POST['places']),
            'portes'  => CLEAN($_POST['portes']),
            'climatisation'  => CLEAN($_POST['climatisation']),
            'energie'  => CLEAN($_POST['energie']),

        );

        $_insert = INSERT('voitures', $arrData, false);
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

        $_GET_SINGLE_CAR = SELECT(
            "voitures",
            "voitures.*",
            "WHERE id='" . CLEAN(DECRYPT($_GET['f'])) . "'",
            false
        );

        if ($_GET_SINGLE_CAR) {

            //Open window if contrat exists
            $template->find('[id=toggle-modal1]', 0)->{'checked'} = 'checked';

            //TRICK
            $TRICK = (array) null;
            while ($DATA = fetch_array($_GET_SINGLE_CAR)) {

                //
                $template->find('[name=marque]', 0)->value          = $DATA['marque'];
                $template->find('[name=type]',   0)->value          = $DATA['type'];
                $template->find('[name=immatriculation]', 0)->value = $DATA['immatriculation'];
                $template->find('[name=places]', 0)->value          = $DATA['places'];
                $template->find('[name=portes]', 0)->value          = $DATA['portes'];
                $template->find('[id=climatisation_' . $DATA['climatisation'] . ']', 0)->{'checked'} = 'checked';

                //Fill the select energie
                $UI_SELECT = $template->find('[name=energie]', 0);
                $UI_OPTION = $UI_SELECT->find('option', 0);
                $STACK_SELECT = (string) null;
                foreach (display_energy(0, false, true) as $e => $nergie) {
                    $UI_OPTION->value = $e;
                    $UI_OPTION->innertext = $nergie;
                    $UI_OPTION->{'selected'} = $DATA['energie'] == $e ? 'selected' : null;
                    $STACK_SELECT .= $UI_OPTION;
                }
                $UI_SELECT->innertext = $STACK_SELECT;
            }
        }
    }
}


/**
 * 
 *  BOTH
 * 
 */

$_GET_VOITURES = SELECT(
    'voitures',
    'voitures.id,
     voitures.marque,
     voitures.type,
     voitures.immatriculation,
     voitures.tarif_1a7 as tarif,
     voitures.places,
     voitures.portes,
     voitures.energie,
     voitures.climatisation,
     voitures.categorie,
     voitures.status as vstatus,
     contrats.date_depart,
     contrats.date_arrivee,
     date("NOW") as now',
    'LEFT JOIN contrats ON date(contrats.date_depart) >= date("NOW") AND date(contrats.date_arrivee) >= date("NOW") AND contrats.vehicule_id = voitures.id
     WHERE voitures.status=1',
    false
);

if ($_GET_VOITURES) {
    $d = (int) 1;

    $table = $template->find('table[id=contrats]', 0);
    $tbody = $table->find('tbody', 0);
    $tr    = $tbody->find('tr', 0);

    $STACK = (string) null;

    while ($DATA = fetch_array($_GET_VOITURES)) {

        $tr->find('td', 0)->find('a', 0)->innertext = $d;
        $tr->find('td', 1)->find('a', 0)->innertext = $DATA['marque'];
        $tr->find('td', 2)->find('a', 0)->innertext = $DATA['type'];
        $tr->find('td', 3)->find('a', 0)->find('span', 0)->innertext = $DATA['immatriculation'];
        $tr->find('td', 4)->find('a', 0)->find('span', 0)->innertext = $DATA['categorie'];
        $tr->find('td', 5)->find('a', 0)->innertext = (!empty($DATA['places']) ? $DATA['places'] . ' places' : '');
        $tr->find('td', 6)->find('a', 0)->innertext = (!empty($DATA['portes']) ? $DATA['portes'] . ' portes' : '');
        $tr->find('td', 7)->find('a', 0)->find('span', 0)->innertext = display_energy($DATA['energie']);
        $tr->find('td', 7)->find('a', 0)->find('span', 0)->{'class'} = 'status ' . display_energy($DATA['energie'], true);
        $tr->find('td', 8)->find('a', 0)->find('span', 0)->innertext = ($DATA['climatisation'] == 0 ? "non" : "oui");
        $tr->find('td', 8)->find('a', 0)->find('span', 0)->{'class'} = 'status' . ($DATA['climatisation'] == 0 ? "" : " four");
        $tr->find('td', -1)->find('a', 0)->find('span', 0)->innertext = display_status(empty($DATA['date_depart']) ? $DATA['vstatus'] : 2);
        $tr->find('td', -1)->find('a', 0)->find('span', 0)->addClass(display_status(empty($DATA['date_depart']) ? $DATA['vstatus'] : 0, true));
        foreach ($tr->find('a') as $link) {
            //Si le <a> a la class "print"
            if (strpos($link->{'class'}, 'print') !== false) {
                $link->{'href'} = '?p=' . $DATA['immatriculation'];
                $link->{'id'}   = 'p' . $DATA['id'];
            } else {
                //sinon lien normal
                $link->{'href'} = url('manager_voitures&f=' . ENCRYPT($DATA['id']));
            }
        }
        $d++;
        $STACK .= $tr;
    }
    $tbody->innertext = $STACK;
} else {
    $checker->innertext = "Un problème est survenu lors de la récupération des contrats.";
    $checker->{'class'} .= " red";
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
