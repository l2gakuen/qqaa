<?php

//Helpers
//Objet du mail
function OBJET($INT)
{
    switch ($INT) {
        case 0:
            $select = "Demande de Rendez-Vous";
            $color = 'red';
            break;
        case 1:
            $select = "Contact / Demande d'informations";
            $color = 'red';
            break;
        case 2:
            $select = "Autre";
            $color = 'red';
            break;
        default:
            $select = "Autre";
            $color = 'red';
            break;
    }
    return array($select, $color);
}

//Status
function STATUS($INT)
{
    switch ($INT) {
        case 0:
            $select = "Non lu";
            $solor  = "two";
            break;
        case 1:
            $select = "Lu";
            $color  = "one";
            break;
        default:
            break;
    }
    return array($select, $color);
}


//Conditions
if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    if ($_POST['action'] == 'deleteMsg') {
        $DELETE = UPDATE('messages', array('status' => 3), 'WHERE id="' . CLEAN($_POST['mid']) . '"', false);

        if ($DELETE) {
            $checker->innertext = "Le message à bien été supprimé.";
        }
    }
} else if (isset($_GET) && !empty($_GET)) { 

    /**
     * 
     *  GET only
     * 
     */


    if (isset($_GET['m']) && !empty($_GET['m'])) {

        //OPEN message on side
        $template->find('[id=toggle-modal]', 0)->{'checked'} = true;
        $SIDEFORM = $template->find('[id=sidebox]', 0);
        $SIDEFORM->find('.box', 0)->{'style'} = null; //Un-hide
        $GET_SINGLE_MSG = SELECT("messages", "*", "WHERE id = " . CLEAN($_GET['m']), false);

        if ($GET_SINGLE_MSG) {

            while ($DATA = fetch_array($GET_SINGLE_MSG)) {

                $SIDEFORM->find('h3',     0)->find('span', 0)->innertext = urldecode(DECRYPT($DATA['nom'])) . ', ';
                $SIDEFORM->find('h3',     0)->find('span', 1)->innertext = urldecode(DECRYPT($DATA['email']));
                $SIDEFORM->find('time',   0)->innertext                  = dateToFrench($DATA['date'], 'l j F Y H:i:s');
                $SIDEFORM->find('.objet', 0)->innertext                  = OBJET($DATA['objet'])[0];
                $SIDEFORM->find('.bordered p', 0)->innertext             = urldecode(DECRYPT($DATA['message']));
                $SIDEFORM->find('[name=mid]', 0)->value = $DATA['id'];

                $SIDEFORM->find('a', 0)->{'href'} = 'mailto:' . urldecode(DECRYPT($DATA['email'])) . '?subject=[' . OBJET($DATA['objet'])[0] . /*'&cc=test@gmail.com*/ ']%20' . urlencode(DECRYPT($DATA['nom'])) . '%20-%20Contacts%20Plaisir%20Rando&body=' . urldecode(DECRYPT($DATA['email'])) . ' : ' . dateToFrench($DATA['date'], 'l j F Y H:i:s')  . ' --- ' . urldecode(DECRYPT($DATA['message']));
            }

            //Si ouvert, status = Lu (1)
            $STATUS_LU = UPDATE("messages", array('status' => 1), "WHERE id = " . CLEAN($_GET['m']));

            $checker->innertext = "Ouverture du message";
            $checker->addClass('blue');
        }
    }
}

/**
 * 
 *  BOTH
 * 
 */

/*******************
 *  MESSAGES LIST
 *******************/
$GET_MESSAGES = SELECT('messages', '*', 'WHERE status < 3', false);
$UI_TBODY = $template->find('[id=tbody]', 0);
$UI_TR    = $UI_TBODY->find('tr', 0);

if ($GET_MESSAGES) {
    $STACK_TRS = (string) null;

    while ($DATA = fetch_array($GET_MESSAGES)) {

        $UI_TR->{'id'} = 'M' . $DATA['id']; //Something to refresh
        // $UI_TR->find('td', 0)->innertext = dateToFrench($DATA['date'], 'j F Y à H:i');


        $UI_TR->find('td', 1)->find('a', 0)->innertext  = urldecode(DECRYPT($DATA['nom']));
        $UI_TR->find('td', 1)->find('a', 0)->{'href'}   = url('manager_messages&m=' . $DATA['id'], true);
        $UI_TR->find('td', 1)->find('a', 0)->{'data-action'} = url('manager_messages&m=' . $DATA['id'], true);
        $UI_TR->find('td', 1)->find('a', 0)->{'data-push'} = '#checkbar, .modals, .hovered, #M' . $DATA['id']; //Refresh

        $UI_TR->find('td', 2)->find('span', 0)->innertext = OBJET($DATA['objet'])[0];
        $UI_TR->find('td', 2)->find('span', 0)->{'class'} = ($DATA['status'] == 0 ? 'fwb' : null);
        $UI_TR->find('td', 2)->find('span', 1)->innertext = urldecode(DECRYPT($DATA['message']));

        // $UI_TR->find('td', 3)->innertext = urldecode(DECRYPT($DATA['email']));
        // $UI_TR->find('td', 4)->innertext = DECRYPT($DATA['tel']);

        // //Status
        // $UI_TR->find('td', 5)->find('span', 0)->innertext = STATUS($DATA['status'])[0];
        // $UI_TR->find('td', 5)->find('span', 0)->{'class'} = 'status ' . STATUS($DATA['status'])[1];

        $UI_TR->find('td', -1)->find('span', 0)->innertext = dateToFrench($DATA['date'], 'j M');
        $UI_TR->find('td', -1)->find('span', 1)->innertext = dateToFrench($DATA['date'], 'à H:i');

        $STACK_TRS .= $UI_TR;
    }
    $UI_TBODY->innertext = $STACK_TRS;
}


















// // if ($_GET['mod'] == "manager_messages") {
//     $msgform = $template->find('[id=msgform]', 0);
//     $table   = $template->find('[id=msgtable]', 0);

//     if ((isset($_POST) && !empty($_POST))) {
//         /**
//          * POST
//          */

//         if (isset($_POST['action']) && $_POST['action'] == 'reserver') {

//             $splitMessage = SELECT("messages", "*", "WHERE id=" . CLEANCHARS($_POST['msg_id']), false);

//             if ($splitMessage) {
//                 while ($data = fetch_array($splitMessage)) {
//                     $dd = base64_encode(dateToFrench($data['start'], "d/m/Y")); //datedepart
//                     $da = base64_encode(dateToFrench($data['end'],   "d/m/Y")); //datearrivee
//                     $hd = base64_encode(dateToFrench($data['start'], "H:i")); //heured
//                     $ha = base64_encode(dateToFrench($data['end'],   "H:i")); //heurea
//                 }

//                 // echo url('locations&dd='.$dd.'&da='.$da.'&hd='.$hd.'&ha='.$ha, true);
//                 header('Location:' . url('planning&dd=' . $dd . '&da=' . $da . '&hd=' . $hd . '&ha=' . $ha . '&msgid=' . CLEANCHARS($_POST['msg_id']), true)); //PHP refresh

//                 // echo '<script>
//                 //         window.location.href = '.url('locations&dd='.$dd.'&da='.$da.'&hd='.$hd.'&ha='.$ha, true).'
//                 //       </script>';

//                 exit;
//             }
//         }
//         if (isset($_POST['action']) && $_POST['action'] == 'markasdone') {
//             $msgarray = array(
//                 "status" => 1
//             );
//             $UPDATEMSG = UPDATE("messages", $msgarray, "WHERE id=" . CLEANCHARS($_POST['msg_id']), false);
//         }
//         if (isset($_POST['action']) && $_POST['action'] == 'supprimer') {
//             $msgarray = array(
//                 "status" => 0
//             );
//             $DELETEMSG = UPDATE("messages", $msgarray, "WHERE id=" . CLEANCHARS($_POST['msg_id']), false);
//         }
//     } else {
//         /**
//          * GET
//          */
//         if (isset($_GET['msgid']) && !empty($_GET['msgid'])) {

//             $getSingleMessages = SELECT(
//                 'messages',
//                 'messages.*,
//                 voitures.modele',
//                 'LEFT JOIN voitures
//                 ON voitures.id = 1
//                 WHERE messages.id=' . CLEANCHARS($_GET['msgid']),
//                 false
//             );

//             if ($getSingleMessages) {
//                 while ($data = fetch_array($getSingleMessages)) {
//                     $msgform->find('.from', 0)->innertext = $data['nom'] . ',' . $data['prenom'];
//                     $msgform->find('.phone', 0)->innertext = $data['mobile'] . '/' . $data['tel'];
//                     $msgform->find('.message', 0)->innertext = "Recu le " . dateToFrench($data['date'], "d/m/Y à H:i") . "<br>" . $data['message'];
//                     $msgform->find('.email', 0)->innertext = $data['email'];
//                     $msgform->find('.start', 0)->innertext = dateToFrench($data['start'], "d/m/Y à H:i");
//                     $msgform->find('.end',   0)->innertext = dateToFrench($data['end'], "d/m/Y à H:i");
//                     foreach ($msgform->find('[name=msg_id]') as $msgId) {
//                         $msgId->value = $data['id'];
//                     }
//                 }

//                 $msgform->find('[id=hasmsg]', 0)->{'style'} = null;
//             }
//         }
//     }

//     $getMessages = SELECT('messages', '*', 'WHERE status>0', false); //id=' . CLEANCHARS($_GET['msgid'])

//     // echo $table;
//     // exit;


//     $tbody = $table->find('[id=msgtbody]', 0);
//     $tr    = $tbody->find('tr', 0);

//     $TABLESTACK = (string) null;

//     if ($getMessages) {
//         while ($data = fetch_array($getMessages)) {
//             $tr->find('td', 0)->innertext                   = dateToFrench($data['date'], "d/m/Y H:i");
//             $tr->find('td', 1)->find('a',     0)->{'href'}  = "index.php?mod=messages&msgid=" . $data['id'];
//             $tr->find('td', 1)->find('a',     0)->innertext = $data['nom'];
//             $tr->find('td', -1)->find('span', 0)->innertext = $data['status'] == 1 ? "Traité" : "Non Traité";
//             $tr->find('td', -1)->find('span', 0)->{'class'} = $data['status'] == 1 ? "status one" : "status two";
//             $TABLESTACK .= $tr;
//         }
//         $tbody->innertext = $TABLESTACK;
//     }
// // }
