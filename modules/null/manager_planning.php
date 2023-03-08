<?php

if ((isset($_POST) && !empty($_POST))) {

    /***********************************
     * 
     *      POST
     * 
     ***********************************/

    if ($_POST['action'] == "addUser") {
        $DB_DATA = array(
            'date' => date('Y-m-d H:i:s'),
            'eventID' => CLEAN($_POST['eid']),
            'userID'  => CLEAN($_POST['people']),
            'status'  => 1
        );
        $INSERT_QUEUE = INSERT('queue_events', $DB_DATA, false);
    }
    if ($_POST['action'] == "removeUser") {
        $DB_DATA = array(
            'date' => date('Y-m-d H:i:s'),
            'eventID' => CLEAN($_POST['eid']),
            'userID'  => CLEAN($_POST['uid']),
            'status'  => 0
        );
        $INSERT_QUEUE = INSERT('queue_events', $DB_DATA, false);
    }

    if ($_POST['action'] == "creer") {
        $DB_UPDATE = array(
            'date' => date("Y-m-d H:i:s"),
            'text'      => CLEAN($_POST['event_text']),
            'content'   => CLEAN($_POST['event_description']),
            'start'     => CLEAN(dateToFrench($_POST['event_start'], 'Y-m-d H:i:s')),
            'end'       => CLEAN(dateToFrench($_POST['event_end'], 'Y-m-d H:i:s')),
            'inscription' => CLEAN(dateToFrench($_POST['inscription'], 'Y-m-d H:i:s')),
            'publication' => CLEAN(dateToFrench($_POST['publication'], 'Y-m-d H:i:s')),
            'type'      => CLEAN($_POST['type']),
            'status'    => 1
        );

        //Si Fichier
        if ($_FILES  && $_FILES['image']['error'] == 0) {
            $path       = 'upload/agenda/';
            $ext        = pathinfo($_FILES['image']['name']);
            $file       = 'agenda_' . date('Ymdis') . '.' . $ext['extension'];
            $UPLOADFILE = UPLOAD($_FILES['image'], '../' . $path, $file);
            $DB_UPDATE['image'] = $path . $file;

            
        }

        $INSERT_EVENT = INSERT('agendas', $DB_UPDATE, false);

        if ($INSERT_EVENT && ($_FILES['image']['error'] == 0)) {

            $checker->innertext = "Evénement créé avec succès";
            $checker->{'class'} .= " green";
        } else {

            $checker->innertext = $phpFileUploadErrorsInFrench[$_FILES['image']['error']];
            $checker->addClass("yellow");
        }
    }
    if ($_POST['action'] == "editer") {

        $DB_UPDATE = array(
            'date' => date("Y-m-d H:i:s"),
            'text'      => CLEAN($_POST['event_text']),
            'content'   => CLEAN($_POST['event_description']),
            'start'     => CLEAN(dateToFrench($_POST['event_start'], 'Y-m-d H:i:s')),
            'end'       => CLEAN(dateToFrench($_POST['event_end'], 'Y-m-d H:i:s')),
            'inscription' => CLEAN(dateToFrench($_POST['inscription'], 'Y-m-d H:i:s')),
            'publication' => CLEAN(dateToFrench($_POST['publication'], 'Y-m-d H:i:s')),
            'type'      => CLEAN($_POST['type'])
        );

        //Si Fichier
        if ($_FILES  && ($_FILES['image']['error'] == 0)) {
            $path       = 'upload/agenda/';
            $ext        = pathinfo($_FILES['image']['name']);
            $file       = 'agenda_' . date('Ymdis') . '.' . $ext['extension'];

            $UPLOADFILE = UPLOAD($_FILES['image'], '../' . $path, $file);
            $DB_UPDATE['image'] = $path . $file;

            //
        }

        $EDIT_EVENT = UPDATE('agendas', $DB_UPDATE, 'WHERE id=' . CLEAN($_POST['eid']), false);


        if ($EDIT_EVENT && ($_FILES['image']['error'] == 0)) {
            $checker->innertext = "Evénement modifié avec succès";
            $checker->{'class'} .= ' green';
        } else {
            $checker->innertext = $phpFileUploadErrorsInFrench[$_FILES['image']['error']];
            $checker->addClass("yellow");
        }
    }
    if ($_POST['action'] == "supprimer") {
        $DELETE_EVENT = UPDATE('agendas', array('status' => 0), 'WHERE id=' . CLEAN($_POST['eid']), false);
        if ($DELETE_EVENT) {
            $checker->innertext = "Evénement supprimé avec succès";
            $checker->{'class'} .= ' yellow';
        } else {
            $checker->innertext = "Erreur lors de la suppression de l'événement";
            $checker->{'class'} .= ' red';
        }
    }
    if ($_POST['action'] == 'open') {
        $template->find('[id=toggle-modal]', 0)->{'checked'} = 'checked';
    }
} else {

    /***********************************
     * 
     *      GET
     * 
     ***********************************/
}

/***********************************
 * 
 *      BOTH
 * 
 ***********************************/

//we set GET HERE to stay open after POST (ajax updates the back page after any edition)
if (isset($_GET['o'])) {
    $template->find('[id=toggle-modal]', 0)->{'checked'} = 'checked';
}
if (isset($_GET['e']) && !empty($_GET['e'])) {

    //EXPORT SOME DATA OUTSIDE THE SCOPE 
    $_EXPORT  = [];

    //SQL
    $_GET_SINGLE_EVENT = SELECT('agendas', '*', 'WHERE id=' . CLEAN($_GET['e']), false);

    //QUERY
    if ($_GET_SINGLE_EVENT) {
        $UI_FORM = $template->find('[id=planningform]', 0);
        $template->find('[id=show]', 0)->removeClass('d-none');
        while ($DATA = fetch_array($_GET_SINGLE_EVENT)) {
            $UI_FORM->find('h2', 0)->innertext                        = $DATA['text'];
            $UI_FORM->find('[name=event_text]',         0)->value     = $DATA['text'];
            $UI_FORM->find('[name=event_description]',  0)->innertext = $DATA['content'];
            $UI_FORM->find('[name=event_start]',        0)->value     = dateToFrench($DATA['start'], 'Y-m-d');
            $UI_FORM->find('[name=event_end]',          0)->value     = dateToFrench($DATA['end'], 'Y-m-d');

            $UI_FORM->find('[name=publication]',        0)->value     = dateToFrench($DATA['publication'], 'Y-m-d');
            $UI_FORM->find('[name=inscription]',        0)->value     = dateToFrench($DATA['inscription'], 'Y-m-d');

            $UI_FORM->find('[name=action]',             0)->value     = 'editer';
            // $UI_FORM->find('[name=eid]',                0)->value     = $DATA['id'];

            $UI_FORM->find('.img-fluid', 0)->{'src'} = '../' . IMAGE('crop', 128, 128, $DATA['image']);

            foreach ($template->find('[name=eid]') as $eid) {
                $eid->value = $DATA['id'];
            }
            $UI_FORM->find('select', 0)->find('option', $DATA['type'])->{'selected'} = true;

            //EXPORT data outside this loop for further use
            $_EXPORT['event_name'] = $DATA['text'];
            $_EXPORT['event_date'] = $DATA['start'];
        }

        //Participants validés
        $_GET_PARTICIPANTS = SELECT(
            "(SELECT 
            queue_events.*, json_array_length(queue_events.groupe, '$') as extrappl,
            adherents.id as userID,
            adherents.nom,
            adherents.prenom,
            adherents.tel,
            adherents.note,
            (SELECT queue_cotisations.status FROM queue_cotisations WHERE userID = adherents.id ORDER BY date DESC LIMIT 1) as queue_status
            FROM 'queue_events' 
            LEFT JOIN adherents
            ON adherents.id = queue_events.userID
            WHERE eventID=" . CLEAN($_GET['e']) . '
            ORDER BY queue_events.date DESC)',
            '*',
            'as t GROUP BY userID',
            false
        );

        if ($_GET_PARTICIPANTS) {
            $PCOUNT = 0;
            $UI_LISTE = $template->find('[id=adherents]', 0);
            $UI_THEAD = $template->find('thead', 0);
            $UI_TBODY = $UI_LISTE->find('tbody', 0);
            $UI_TR    = $UI_TBODY->find('tr', 0);

            $STACK_TR = (string) null;
            while ($DATA = fetch_array($_GET_PARTICIPANTS)) {
                if ($DATA['status'] == 1) {
                    $UI_TR->find('td', 0)->find('span', 0)->innertext   = DECRYPT($DATA['nom']) . ', ' . DECRYPT($DATA['prenom']) . ($DATA['extrappl'] > 0 ? ' +' . $DATA['extrappl'] . '<i class="fal fa-users"></i>' : '');
                    $UI_TR->find('td', 0)->find('span', 0)->{'data-uid'} = $DATA['userID'];
                    $UI_TR->find('td', 0)->find('span', 0)->{'data-eid'} = $DATA['eventID'];
                    $UI_TR->find('td', 0)->find('span', 0)->{'data-post'} = '#adherents';
                    $UI_TR->find('td', 0)->find('span', 0)->{'data-action'} = url('manager_planning&e=' . $DATA['eventID'], true);
                    $UI_TR->find('td', 1)->innertext = DECRYPT($DATA['tel']);
                    $UI_TR->find('td', 2)->innertext = DECRYPT($DATA['note']);
                    $UI_TR->find('td', 3)->find('span', 0)->innertext = $DATA['queue_status'] == '1' ? 'Complète' : 'Incomplète';
                    $UI_TR->find('td', 3)->find('span', 0)->{'class'} = $DATA['queue_status'] == '1' ? 'status one' : 'status two';
                    $STACK_TR .= $UI_TR;
                    $PCOUNT += 1 + $DATA['extrappl'];
                }
            }
            $UI_THEAD->find('tr', 0)->find('th', 1)->innertext = $PCOUNT;
            $UI_TBODY->innertext = $STACK_TR;
        }

        //Participants non validés
        $_GET_OTHERS = SELECT("adherents", 'id, nom, prenom', 'WHERE status>0', false);
        if ($_GET_OTHERS) {
            $UI_OTHERS  = $template->find('[id=others]', 0);
            $UI_OPTION  = $UI_OTHERS->find('option', 0);

            $STACK_OPTIONS = (string) null;
            while ($DATA = fetch_array($_GET_OTHERS)) {
                $UI_OPTION->innertext = DECRYPT($DATA['nom']) . ', ' . DECRYPT($DATA['prenom']);
                $UI_OPTION->value = $DATA['id'];

                $STACK_OPTIONS .= $UI_OPTION;
            }

            $UI_OTHERS->innertext = $STACK_OPTIONS;
        }

        $template->find('[id=toggle-modal]', 0)->{'checked'} = 'checked';
        $checker->innertext = "Ouverture de l'événement - " . $_EXPORT['event_name'] . " (" . dateToFrench($_EXPORT['event_date'], 'd M') . ")"; //Use of Global data $_EXPORT HERE 
        $checker->{'class'} .= ' blue';
    } else {

        $checker->innertext = "Erreur lors de la modification de l'événement";
        $checker->{'class'} .= ' red';
    }
}


$_GET_ALL_EVENTS = SELECT('agendas', '*', 'WHERE status=1 ORDER BY start DESC', false);

if ($_GET_ALL_EVENTS) {
    $FILTERED = [];
    $UI_RESUME = $template->find('[id=resume]', 0);
    $UI_YEAR   = $UI_RESUME->find('.year', 0);

    $TABLE = $template->find('.calen', 0);
    $TR    = $TABLE->find('tr', 0);
    // echo $TABLE;

    $STACK_TR = (string) null;

    while ($DATA = fetch_array($_GET_ALL_EVENTS)) {
        $YEAR  = dateToFrench($DATA['start'], 'Y');
        $MONTH = dateToFrench($DATA['start'], 'm');
        $FILTERED[$YEAR][$MONTH][] = $DATA;

        $TR->find('td', 0)->find('a', 0)->innertext = $DATA['id'];
        $TR->find('td', 1)->find('a', 0)->innertext = dateToFrench($DATA['start'], 'd/m/Y');
        $TR->find('td', 2)->find('a', 0)->innertext = $DATA['text'];
        $TR->find('td', 3)->find('a', 0)->innertext = $DATA['content'];
        $TR->find('td', 4)->find('a', 0)->innertext = DATEDIFF($DATA['end'], $DATA['start']) + 1    . ' j';
        $TR->find('td', 5)->find('a', 0)->innertext = dateToFrench($DATA['publication'], 'd/m/Y');
        $TR->find('td', 6)->find('a', 0)->innertext = dateToFrench($DATA['inscription'], 'd/m/Y');

        foreach ($TR->find('a') as $a) {
            $a->{'href'}        = url('manager_planning', true) . '&e=' . $DATA['id'] . '&m=' . dateToFrench($DATA['start'], 'n');
            $a->{'data-action'} = url('manager_planning', true) . '&e=' . $DATA['id'];
            $a->{'data-push'}  = '.modals';
            $a->{'data-post'}  = '#checkbar, .calendar';
            $a->{'data-item'}  = $DATA['id'];
        }

        $STACK_TR .= $TR;
    }

    $TABLE->innertext = $STACK_TR;

    $STACK_YEARS = (string) null;
    $COUNTY = 0;
    foreach ($FILTERED as $YEAR => $MONTHS) {
        $UI_MONTH   = str_get_html('<details class="clear month">
                                        <summary>
                                            --
                                        </summary>
                                        <p class="text-right"><a>Aller à <span></span></a></p>
                                        <div>
                                        </div>
                                    </details>');
        $UI_YEAR->find('summary', 0)->innertext = $YEAR;
        $UI_YEAR->{'open'} = $COUNTY == 0 ? true : null;
        $STACK_MONTHS = (string) null;

        foreach ($MONTHS as $MNAME => $EVENTS) {
            $COUNTM = 0;
            $UI_EVENTS = str_get_html('<ul class="events">
                                            <li class="event">
                                                <a href="#">
                                                    <span>Lorem ipsum dolor</span> 
                                                    <span> sit amet consectetur. </span>
                                                </a>
                                            </li>
                                        </ul>');
            $UI_EVENT = $UI_EVENTS->find('.event', 0);
            $STACK_EVENTS = (string) null;
            foreach ($EVENTS as $EVENT) {
                $UI_EVENT->find('a', 0)->find('span', 0)->innertext      = mk_text($EVENT['text'], 15);
                $UI_EVENT->find('a', 0)->find('span', 1)->innertext      = dateToFrench($EVENT['start'], 'd/m');
                $UI_EVENT->find('a', 0)->{'href'}       = url('manager_planning', true) . '&e=' . $EVENT['id'] . '&m=' . dateToFrench($EVENT['start'], 'n');
                $UI_EVENT->find('a', 0)->{'data-action'}     = url('manager_planning', true) . '&e=' . $EVENT['id'] . '&m='  . dateToFrench($EVENT['start'], 'n');
                $UI_EVENT->find('a', 0)->{'data-push'}     = '.modals';
                $STACK_EVENTS                          .= $UI_EVENT;
            }
            $UI_EVENTS->find('.events', 0)->innertext   = $STACK_EVENTS;

            // $UI_MONTH->find('details', 0)->{'open'}     = 
            $UI_MONTH->find('summary', 0)->innertext            = strtoupper(dateToFrench('2022/' . $MNAME . '/01', 'F'));
            $UI_MONTH->find('div',  0)->innertext               = $UI_EVENTS;
            $UI_MONTH->find('a', 0)->{'data-url'}               = 'manager_planning&m=' . dateToFrench('2022/' . $MNAME . '/01', 'n');
            $UI_MONTH->find('a', 0)->find('span', 0)->innertext = dateToFrench('2022/' . $MNAME . '/01', 'M');
            $UI_MONTH->{'open'} = $COUNTM == 0 ? true : null;

            $STACK_MONTHS .= $UI_MONTH;
            $COUNTM++;
        }
        $UI_YEAR->find('div', 0)->innertext = $STACK_MONTHS;
        $STACK_YEARS .= $UI_YEAR;
        $COUNTY++;
    }
    $UI_RESUME->innertext = $STACK_YEARS;
}

$getAgenda = SELECT(
    'agendas',
    "*,
    DAYOFWEEK(start)-1 as 'nday',
    DATE_FORMAT(start, '%d') as 'day',
    DATE_FORMAT(start, '%k') as 'dstart', 
    DATE_FORMAT(end, '%k') as 'dend'",
    "WHERE status=1 AND DATE_FORMAT(start, '%m') = " . (isset($_GET['m']) ? "'" . str_pad($_GET['m'], 2, '0', STR_PAD_LEFT) . "'" : "DATE_FORMAT(NOW(), '%m')") . "   ORDER BY day ASC",
    false
);
$UI_NAV = $template->find('[id=planning_nav]', 0);

// $UI_NAV->find('li', 0)->find('a',0)->{'href'} = url('manager_planning', true).'&m=
$UI_NAV->find('li', 1)->innertext = (isset($_GET['m']) ? dateToFrench('2022-' . $_GET['m'] . '-01', 'F') : dateToFrench(date('Y-m-d H:i:s'), 'F'));

if ($getAgenda) {
    $_UI_CALENDAR = $template->find('.calendar', 0);
    $_UI_DAYS     = $_UI_CALENDAR->find('.days', 0);
    while ($DATA = fetch_array($getAgenda)) {

        $UI_EVENT = str_get_html(
            '<a href="#" class="event">
                <span class="time text-gr-1">-</span>
                <span class="title">1</span>
                <span class="people text-gr-1">-</span>
            </a>'
            //start-1 end-2 type-3
        );
        // $UI_EVENT->
        $UI_EVENT->find('a',      0)->{'href'}       = url('manager_planning', true) . '&e=' . $DATA['id'];
        $UI_EVENT->find('a',      0)->{'data-action'} = url('manager_planning', true) . '&e=' . $DATA['id'];
        $UI_EVENT->find('a',      0)->{'data-push'}  = '.modals';
        $UI_EVENT->find('a',      0)->{'data-post'}  = '#checkbar, .calendar';
        $UI_EVENT->find('a',      0)->{'data-after'} = dateToFrench($DATA['start'], 'd');
        $UI_EVENT->find('a',      0)->{'data-item'}  = $DATA['id'];
        $UI_EVENT->find('.title', 0)->innertext = $DATA['text'];
        $UI_EVENT->find('.time',  0)->innertext = dateToFrench($DATA['start'], 'd F Y');

        $UI_EVENT->find('.event', 0)->{'class'} .= ' start-' . round($DATA['day'] / 6) . ' end-' . round($DATA['day'] / 6) . ' type-' . $DATA['type'];

        $_COUNT = SELECT('(SELECT * FROM "queue_events" WHERE eventID=' . CLEAN($DATA['id']) . ' ORDER BY date DESC)', "COUNT(*) as count, people FROM (SELECT *, json_array_length(groupe, '$') as people", ' WHERE 1 GROUP BY userID)  WHERE status=1', false);

        if ($_COUNT) {
            while ($COUNT = fetch_array($_COUNT)) {
                $UI_EVENT->find('.people', 0)->innertext = ($COUNT['count'] + $COUNT['people']) . ' participants';
            }
        }

        // SELECT COUNT(*) as count FROM (SELECT * FROM (SELECT * FROM "queue_events" WHERE eventID=6 ORDER BY date DESC) WHERE 1 GROUP BY userID) WHERE status=1
        // $UI_EVENT->find('.people', 0)->innertext = "q";

        $_UI_DAYS->find('.day', $DATA['nday'])->find('.events', 0)->innertext .= $UI_EVENT;
    }
} else {
    // echo "QQ";
}
