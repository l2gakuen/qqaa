<?php

if ((isset($_POST) && !empty($_POST))) {

    /***********************************
     * 
     *      POST
     * 
     ***********************************/


    if ($_POST['action'] == "upload") {

        // print_r($_FILES);
        // print_r($_POST);

        if (count($_FILES) > 0) {
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                $imgData            = addslashes(file_get_contents($_FILES['file']['tmp_name']));
                $imageProperties    = getimageSize($_FILES['file']['tmp_name']);
                $ext                = pathinfo($_FILES['file']['name']);
                $customOutput       = date('Ymdhis_v_') .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];
                $path               = 'upload/galerie/' . (CLEAN($_POST['droptarget'])) . '/';

                // echo $customOutput;
                $UPLOADFILE         = UPLOAD($_FILES['file'], '../' . $path, $customOutput);

                // base64toImage($_POST['file'], '../upload/' . $customOutput .'jpeg');

                if ($UPLOADFILE) {
                    $INSERT_IMAGE = INSERT(
                        'galerie',
                        array(
                            'group_id'  => CLEAN($_POST['droptarget']),
                            'date'      => date('Y-m-d H:i:s'),
                            'text'      => 'Demo',
                            'details'   => 'Details',
                            'eventDate' => '2022-04-14 17:27:00',
                            'author'    => 1,
                            'status'    => 1,
                            'file_url'  => $path . $customOutput,
                            'stamp_id'  => date('YmdHis') . explode('.', microtime(true))[1]
                            // , 'file_blob' => CLEAN($_POST['file_blob'])
                        ),
                        false
                    );
                    if ($INSERT_IMAGE) {
                        $checker->innertext = "L'image a bien été ajoutée";
                        $checker->{'class'} .= " green";
                    } else {

                        $checker->innertext = "Une erreur est survenue lors de l'ajout de l'image";
                        $checker->{'class'} .= " yellow";
                        //Debug : Remove uploaded data without proper database insertion
                        unlink('../upload/' . $customOutput);
                    }
                } else {
                    $checker->innertext = "Erreur lors de l'upload";
                    $checker->{'class'} .= " red";
                }
            }
        }
    }
    if ($_POST['action'] == "addEvent") {
        $INSERT_EVENT = INSERT(
            'galerie_group',
            array(
                'date'      => date('Y-m-d H:i:s'),
                'text'      => CLEAN($_POST['event_name']),
                'content'   => CLEAN($_POST['event_description']),
                'eventDate' => CLEAN($_POST['event_date']), // . ' 00:00:00'),
                'author'    => 1,
                'status'    => 1
            ),
            false
        );
        if ($INSERT_EVENT) {
            $checker->innertext = "Inserted!";
        }
    }
    if ($_POST['action'] == "editEvent") {
        $UPDATE_GROUPE = UPDATE(
            'galerie_group',
            array(
                'text'      => CLEAN($_POST['group_name']),
                'content'   => CLEAN($_POST['group_description']),
                'eventDate' => CLEAN(gmdate('Y-m-d H:i:s', strtotime($_POST['group_date']))),
                'author'    => 1,
                'status'    => 1
            ),
            'WHERE id=' . CLEAN($_POST['gid']),
            false
        );
        if ($UPDATE_GROUPE) {
            $checker->innertext = "Modification éffectuée";
            $checker->{'class'} .= " green";
        }
    }
    if ($_POST['action'] == "removeEvent") {
        $UPDATE_GROUPE = UPDATE(
            'galerie_group',
            array(
                'status'    => 0
            ),
            'WHERE id=' . CLEAN($_POST['gid']),
            false
        );
        if ($UPDATE_GROUPE) {
            $checker->innertext = "Suppression éffectuée";
            $checker->{'class'} .= " yellow";
        }
    }
    if ($_POST['action'] == "removeImage") {
        $UPDATE_GROUPE = UPDATE(
            'galerie',
            array('status' => 0),
            'WHERE id=' . CLEAN($_POST['iid']),
            false
        );
        if ($UPDATE_GROUPE) {
            $checker->innertext = "L'Image a bien été supprimée";
            $checker->{'class'} .= " yellow";
        }
    }
    if ($_POST['action'] == "open") {
        $EDITOR = $template->find('[id=editor]', 0);
        $EDITOR->find('div', 0)->class = null;


        $GET_SINGLE_IMAGE = SELECT(
            'galerie',
            'galerie.*, 
        galerie_group.text as event,
        galerie_group.content,
        galerie_group.date, 
        galerie_group.eventDate',
            'LEFT JOIN galerie_group ON galerie.group_id = galerie_group.id WHERE galerie.status=1 AND galerie.id=' . CLEAN($_POST['id']),
            false
        );

        if ($GET_SINGLE_IMAGE) {
            $EDITOR->{'class'} = null;
            while ($DATA = fetch_array($GET_SINGLE_IMAGE)) {
                $EDITOR->find('img', 0)->src = '../' . IMAGE('crop', 760, 570, $DATA['file_url']);
                $EDITOR->find('[name=iid]', 0)->value = $DATA['id'];

                $checker->innertext = "Modification de l'image " . '<span class="text-gr-1 d-block fwb">(' . $DATA['event'] . ')</span>';
                $checker->{'class'} .= " blue";
            }
        }
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

/************************
 *    GET ALL IMAGES
 ************************/

$_GET_GALLERY = SELECT(
    'galerie',
    'galerie.*, 
    galerie_group.text as event,
    galerie_group.content,
    galerie_group.date,
    galerie_group.eventDate',
    'LEFT JOIN galerie_group ON galerie.group_id = galerie_group.id WHERE galerie.status=1 AND galerie_group.status=1 ORDER BY galerie.id DESC',
    false
);
if ($_GET_GALLERY) {
    $GRID        = $template->find('.grid', 0);
    $GRID_ITEM   = $GRID->find('label', 0);
    $GRID_FORMS  = $template->find('[id=forms]', 0);
    $GRID_FORM   = $GRID_FORMS->find('form', 0);
    $GRID_GROUP  = (array) [];
    $GRID_STACK  = (string) null;
    $FORM_STACK  = (string) null;

    $ALL_URLS = (array) [];
    while ($DATA = fetch_array($_GET_GALLERY)) {

        // //Tasse Par Item
        // $GRID_ITEM->{'for'}             = 'G_' . $DATA['id'];
        // $GRID_ITEM->find('img', 0)->src = '../' . IMAGE('crop', 200, 200, $DATA['file_url']);
        // $GRID_STACK .= $GRID_ITEM;
        $ALL_URLS[] = $DATA['file_url'];

        //Tasse Par Groupe
        $GRID_GROUP[$DATA['group_id']][] = $DATA;

        //Tasse Par Form
        $GRID_FORM->find('[name=id]',       0)->value   = $DATA['id'];
        $GRID_FORM->find('[type=submit]',   0)->id      = 'G_' . $DATA['id'];
        $FORM_STACK .= $GRID_FORM;
    }

    // echo "<pre>";
    // print_r($ALL_URLS);
    // echo "</pre>";

    $compareFolder = '/var/www/plaisir-rando2p.re/upload/galerie/';
    $compareFiles  = glob($compareFolder . '*/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    //for each files in $compareFiles, check if exist in $ALL_URLS
    foreach ($compareFiles as $file) {
        $file = str_replace($compareFolder, '', $file);
        if (!in_array($file, $ALL_URLS)) {
            //delete
            // unlink($compareFolder . $file);
        }
    }

    //Pour chaque Image
    $GRID_FORMS->innertext = $FORM_STACK;

    //Pour Chaque Groupe
    $GROUP_STACK        = (string) null;
    $BY_GROUP           = $template->find('[id=byGroup]', 0);
    $DETAILS            = $BY_GROUP->find('details', 0);
    $SUMMARY            = $DETAILS->find('summary span', 0);
    $BY_GROUP_GRID      = $DETAILS->find('.grid', 0);
    $BY_GROUP_GRID_ITEM = $BY_GROUP_GRID->find('label', 0);

    $TOGGLE             = $BY_GROUP->find('.toggle', 0);
    $HIDDEN_FORM        = $BY_GROUP->find('.remove', 0);

    $COUNT = 0;
    foreach ($GRID_GROUP as $ID => $GROUP) {
        $GROUP_ITEM_STACK   = (string) null;

        foreach ($GROUP as $INDEX => $ITEM) {
            $BY_GROUP_GRID->{'id'} = 'Q_' . $ID . '_grid';
            $BY_GROUP_GRID_ITEM->find('figure', 0)->{'data-uri'}       = $ITEM['id'];
            $BY_GROUP_GRID_ITEM->find('figure', 0)->{'data-post'}      = '#checkbar, #Q_' . $ID . '_grid';
            $BY_GROUP_GRID_ITEM->{'for'}             = 'G_' . $ITEM['id'];
            $BY_GROUP_GRID_ITEM->find('img', 0)->src = '../' . IMAGE('crop', 200, 200, $ITEM['file_url']);
            $SUMMARY->innertext                      = dateToFrench($ITEM['eventDate'], 'd F Y') . ' - ' . $ITEM['event'];
            $GROUP_ITEM_STACK                       .= $BY_GROUP_GRID_ITEM;
            $DETAILS->{'id'}                                    = 'Q_' . $ID;
            $DETAILS->find('form', 0)->{'data-push'}            = '#checkbar, #droptarget, #Q_' . $ID;
            $DETAILS->find('[id=ilist]',        0)->value       = $ITEM['event'];
            $DETAILS->find('[id=description]',  0)->innertext   = $ITEM['content'];
            $DETAILS->find('[name=gid]',        0)->value       = $ID;
            $DETAILS->find('[name=group_date]', 0)->value       = dateToFrench($ITEM['eventDate'], 'Y-m-d'); // 'Y-m-d\TH:i:s'); //For input localdate-time
            $DETAILS->find('time',              0)->innertext   = dateToFrench($ITEM['date'],      'd F Y');

            //DELETE BUTTON
            $HIDDEN_FORM->find('[name=gid]', 0)->value = $ID;
            $HIDDEN_FORM->find('[type=submit]', 0)->id = 'G_' . $ITEM['id'];

            $TOGGLE->find('[type=checkbox]', 0)->{'id'}  = 'CG_' . $ITEM['id'];
            $TOGGLE->find('label.check',    0)->{'for'} = 'CG_' . $ITEM['id'];
            $TOGGLE->find('label.check',    1)->{'for'} = 'CG_' . $ITEM['id'];

            $TOGGLE->find('label.del',      0)->{'for'} = 'G_' . $ITEM['id'];


            // $DETAILS->{'open'} = $COUNT < 1 ? true : null;
        }
        $BY_GROUP_GRID->innertext  = $GROUP_ITEM_STACK;
        $GROUP_STACK              .= $DETAILS;
        $COUNT++;
    }

    $BY_GROUP->innertext = $GROUP_STACK;
    $GRID->innertext     = $GRID_STACK;
}

/********************************
 *  GET ALL GROUPS for <select>
 ********************************/
$UI_UPLOAD   = MODULE('ui_upload');
$UI_SELECT   = $UI_UPLOAD->find('[name=droptarget]', 0);
$UI_OPTION   = $UI_SELECT->find('option', 1);
$UI_STACK    = (string) null;
$_GET_GROUPS = SELECT(
    'galerie_group',
    'galerie_group.*',
    'WHERE status=1 ORDER BY id DESC',
    false
);

while ($DATA = fetch_array($_GET_GROUPS)) {
    $UI_OPTION->{'value'} = $DATA['id'];
    $UI_OPTION->innertext = dateToFrench($DATA['eventDate'],      'd F Y') . ' __ ' . $DATA['text'];
    $UI_STACK .= $UI_OPTION;
}

$UI_OPTION->outertext = $UI_STACK;

$template->find('[id=upload]',     0)->innertext .= $UI_UPLOAD;
$template->find('[id=edit_image]', 0)->innertext  = $UI_STACK;
