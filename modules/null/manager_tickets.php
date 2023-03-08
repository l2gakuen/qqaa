<?php

if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST only
     * 
     */

    if (isset($_POST['action']) && $_POST['action'] == 'editer') {
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



//Stack
$STRING_STACK = (string) null;


//SQL
$_SELECT_ALL_TICKETS = SELECT(
    'tickets_queue',
    'tickets_queue.*,
    tickets.type as ttype,
    tickets.status,
    tickets.usage,
    clients.name,
    clients.email,
    clients.zone,
    clients.status,
    clients.type as ctype
    ',
    'LEFT JOIN tickets ON tickets_queue.ticket_code = tickets.ticket_code 
     LEFT JOIN clients ON tickets_queue.belongsTo = clients.id
    WHERE tickets_queue.date > DATETIME("now", "-30 day") ORDER BY date DESC',
    false
);

//On perd de la ressource a jouer sans GROUP BY et en déduire des trucs pcq le serveur craint

if ($_SELECT_ALL_TICKETS) {
    $table = $template->find('table', 0);
    $tbody = $table->find('tbody', 0);
    $tr    = $tbody->find('tr', 0);
    $_EXPORT_DATA     = (array)[];

    while ($DATA = fetch_array($_SELECT_ALL_TICKETS)) {

        //On prends le gros paté (tickets queue), on GROUPBY en php : on tasse dans une array avec le numero de ticket comme key
        foreach ($DATA as $i => $v) {
            if (!is_numeric($i)) {
                //on export et reordonne pour faire une simple loop
                $_EXPORT_DATA[$DATA['ticket_code']][$DATA['id']][$i] = $v;
            }
        }

        //On repli un premier tableau
        $tr->find('td', 0)->find('a', 0)->innertext = '-';
        $tr->find('td', 1)->find('a', 0)->innertext = $DATA['name'];
        $tr->find('td', 2)->find('a', 0)->innertext = $DATA['email'];
        $tr->find('td', 3)->find('a', 0)->innertext = $DATA['zone'];
        $tr->find('td', 4)->find('a', 0)->innertext = $DATA['status'];
        $tr->find('td', 5)->find('a', 0)->innertext = ($DATA['ctype'] == 0 ? "PRO" : "--");

        foreach ($template->find('a') as $link) {
            $link->{'href'} = url('manager_clients&c=' . $DATA['id_code'], true);
        }

        //Stack TR
        $STRING_STACK .= $tr;
    }
    //Insert Stack
    $tbody->innertext = $STRING_STACK;

    //Ensuite on en deduits donc le reste, une seule query, pas besoin acheter un xeon
    $c       = (int) 0;
    $_SORTED = (array) [];

    $table = $template->find('table', 1);
    $thead = $table->find('thead', 0);
    $tbody = $table->find('tbody', 0);
    $tr    = $tbody->find('tr', 0);

    foreach ($_EXPORT_DATA as $liste_tickets) {
        $used = (int) 0;
        foreach ($liste_tickets as $usage) {

            //On compte combien de fois ['action'] 2 = DEMANDE
            if ($usage['action'] == "2") {
                $used++;
            }
        }
        $tr->find('td', 0)->find('a', 0)->innertext = '-';
        $tr->find('td', 1)->find('a', 0)->innertext = ($usage['name']);
        $tr->find('td', 2)->find('a', 0)->innertext =  $usage['email'];

        //Action
        $tr->find('td', 3)->find('a', 0)->innertext = ($usage['zone']);
        $tr->find('td', 4)->find('a', 0)->innertext = ($usage['cat']);
        $tr->find('td', 5)->find('a', 0)->innertext = $usage['type'] == null ? $usage['ttype'] : $usage['type'];
        $STACK_TR .= $tr;
    }
    $tbody->innertext = $STACK_TR;




    // $STACK_TR = (string) null;

    // echo "<pre>";
    // print_r($_SORTED);
    // echo "</pre>";

    // foreach ($_SORTED as $ticket) {

        
    // }
}
