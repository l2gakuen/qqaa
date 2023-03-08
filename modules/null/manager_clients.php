<?php

if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    $CLIENT_ARRAY = array(
        'name' => CLEAN($_POST['name']),
        'email' => CLEAN($_POST['email']),
        'adresse' => CLEAN($_POST['adresse']),
        'tel' => CLEAN($_POST['tel']),
        'type' => CLEAN($_POST['type'])
    );
    if (isset($_POST['action']) && $_POST['action'] == 'editer') {

        $EDIT_CLIENT = UPDATE('clients', $CLIENT_ARRAY, 'WHERE id_code="' . CLEAN($_POST['id']) . '"', false);
        if ($EDIT_CLIENT) {
            $checker->innertext  = 'La modification à bien été effectuée.';
            $checker->addClass('blue');
        } else {
            $checker->innertext  = 'Une erreur est survenue (QUERY)';
            $checker->addClass('red');
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == 'creer') {

        $CREATE_CLIENT = INSERT("clients", $CLIENT_ARRAY, false);
        if ($CREATE_CLIENT) {
            $checker->innertext  = 'Le client à bien été créé.';
            $checker->addClass('blue');
        } else {
            $checker->innertext  = 'Une erreur est survenue (INSERTQUERY)';
            $checker->addClass('red');
        }
    }
} else {

    /**
     * 
     *  GET only
     * 
     */

    if (isset($_GET['c']) && !empty($_GET['c'])) {



        $_GET_SINGLE_CLIENT = SELECT('clients', '*', 'WHERE id_code="' . CLEAN($_GET['c']) . '"', false);
        if ($_GET_SINGLE_CLIENT) {
            //Open Message
            $checker->innertext  = 'Ouverture client N°#' . $_GET['c'];
            $checker->addClass('blue');

            //Open UI
            $template->find('[id=toggle-modal]', 0)->{'checked'} = 'checked';
            $modal = $template->find('.modal', 0);

            //Export data
            $_export = (array) [];

            //Fillup UI
            while ($DATA = fetch_array($_GET_SINGLE_CLIENT)) {
                $modal->find('[name=name]', 0)->value    = $DATA['name'];
                $modal->find('[name=email]', 0)->value   = $DATA['email'];
                $modal->find('[name=tel]', 0)->value     = $DATA['tel'];
                $modal->find('[name=adresse]', 0)->value = $DATA['adresse'];
                $modal->find('[name=zone]', 0)->value    = $DATA['zone'];
                $modal->find('[name=id]', 0)->value    = $DATA['id_code'];

                //If is edit : change action input
                $modal->find('[name=action]', 0)->value = "editer";
                $modal->find('[type=submit]', 0)->value = "Éditer";

                //Export data
                $_export['id'] = $DATA['id'];
            }

            //Historique
            $_GET_TICKETS_QUEUE = SELECT(
                'tickets_queue',
                'tickets_queue.*,
                tickets.type,
                tickets.status,
                tickets.usage,
                clients.name',
                'LEFT JOIN tickets ON tickets_queue.ticket_code = tickets.ticket_code 
                LEFT JOIN clients ON tickets_queue.belongsTo = clients.id
                WHERE tickets_queue.belongsTo = "' . CLEAN($_export['id']) . '" ORDER BY date DESC',
                false
            );

            $table = $template->find('.htickets', 0)->find('table', 0);
            $tbody = $table->find('tbody', 0);
            $tr    = $tbody->find('tr', 0);

            $STACK = (string) null;
            if ($_GET_TICKETS_QUEUE) {
                while ($DATA = fetch_array($_GET_TICKETS_QUEUE)) {

                    $tr->find('td', 0)->innertext = $DATA['ticket_code'];
                    $tr->find('td', 1)->innertext = $DATA['date'];
                    $tr->find('td', 2)->innertext = ($DATA['addedBy'] == "0" ? 'Admin' : 'Client');
                    $tr->find('td', 3)->innertext = $DATA['action'];
                    $tr->find('td', 4)->innertext = $DATA['cat'];
                    $tr->find('td', 5)->innertext = $DATA['belongsTo'];
                    $tr->find('td', 6)->innertext = $DATA['type'];
                    $tr->find('td', 7)->innertext = $DATA['status'];
                    $tr->find('td', 8)->innertext = $DATA['usage'];
                    $tr->find('td', 9)->innertext = $DATA['name'];

                    //action forms
                    foreach ($tr->find('td', -1)->find('form') as $form) {
                        $form->find('[name=tid]', 0)->value = $DATA['ticket_code'];
                    }


                    $STACK .= $tr;
                }
                $tbody->innertext = $STACK;
            } else {
                //error
            }
        } else {
            $checker->innertext  = 'Une erreur est survenue (DBCONNECTION)';
            $checker->addClass('red');
        }
    }
}


/**
 * 
 *  BOTH
 * 
 */


$table = $template->find('table', 0);
$tbody = $table->find('tbody', 0);
$tr    = $tbody->find('tr', 0);

//Stack
$STRING_STACK = (string) null;


//SQL
$_SELECT_ALL_CLIENTS = SELECT('clients', '*', 'WHERE 1', false); //some shit with union

if ($_SELECT_ALL_CLIENTS) {
    while ($DATA = fetch_array($_SELECT_ALL_CLIENTS)) {

        $tr->find('td', 1)->find('a', 0)->innertext = $DATA['name'];
        $tr->find('td', 2)->find('a', 0)->innertext = $DATA['email'];
        $tr->find('td', 3)->find('a', 0)->innertext = $DATA['zone'];
        $tr->find('td', 4)->find('a', 0)->innertext = $DATA['status'];
        $tr->find('td', 5)->find('a', 0)->innertext = ($DATA['type'] == 0 ? "PRO" : "--");

        foreach ($template->find('a') as $link) {
            $link->{'href'} = url('manager_clients&c=' . $DATA['id_code'], true);
        }

        //Stack TR
        $STRING_STACK .= $tr;
    }
    //Insert Stack
    $tbody->innertext = $STRING_STACK;
}
