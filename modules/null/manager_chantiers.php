<?php

$_EXPORT_DATA = []; //Data to use inside and outside the scope
if ((isset($_POST) && !empty($_POST))) {

    /***********************************
     * 
     *      POST
     * 
     ***********************************/

    // echo "<pre>";
    // print_r($_FILES);
    // print_r($_POST);
    // echo "</pre>";

    if ($_POST['action'] == "removeImage") {

        $SQL = "UPDATE articles SET `images` = (SELECT json_remove(images, '$." . CLEAN($_POST['iid']) . "') WHERE id=" . CLEAN($_POST['role']) . ")  WHERE id=" . CLEAN($_POST['role']);

        $result = sqlite_query($connection, $SQL);
        if ($result) {
            $checker->innertext = "L'image a bien été supprimée";
            $checker->addClass('yellow');
        } else {
            $checker->innertext = "Une erreur est survenue lors de la suppression de l'image";
            $checker->addClass('red');
        }
    }
    
    if ($_POST['action'] == "upload") {

        if (count($_FILES) > 0) {
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                $imgData            = addslashes(file_get_contents($_FILES['file']['tmp_name']));
                $imageProperties    = getimageSize($_FILES['file']['tmp_name']);
                $ext                = pathinfo($_FILES['file']['name']);
                $customTime         = date('Ymdhis_v_');
                $customOutput       = $customTime .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];
                $UPLOADFILE         = UPLOAD($_FILES['file'], '../upload/aci', $customOutput);

                if ($UPLOADFILE) {

                    /* 
                        SQL JSON functions permet de manipuler les données dans un sous-tableau
                       [ ID | TEXT | DATE | IMAGES ]
                          1 |   1  |  --  |  [1,2,3,[4,5,6]]

                        LET'S GO modern coding !
                    */

                    $SQL = "update articles set images = JSON_SET(images, '$." . $customTime . "', '" . 'upload/aci' . $customOutput . "') where id = " . CLEAN($_POST['droptarget']);
                    //execute query

                    $result = sqlite_query($connection, $SQL);
                    if ($result) {
                        $checker->innertext = 'Modifié avec succès';
                    } else {
                        $checker->innertext = 'Erreur';
                    }

                    if ($INSERT_IMAGE) {
                        $checker->innertext = 'Inserted';
                    } else {
                        $checker->innertext = 'Error Database Insert';
                        //Debug : Remove uploaded data without proper database insertion
                        unlink('../upload/' . $customOutput);
                    }
                } else {
                    $checker->innertext = "error upload";
                }
            }
        }
    }

    if ($_POST['action'] == "addArticle") {
        $dataArr = array(
            'date'      => date('Y-m-d H:i:s'),
            'text'      => CLEAN($_POST['article_name']),
            'content'   => CLEANCHARS($_POST['article_content']),
            'eventDate' => CLEAN($_POST['article_date']), // . ' 00:00:00'),
            'eventEnd'  => CLEAN($_POST['article_end']), // . ' 00:00:00'),
            'author'    => 1,
            'status'    => 1
        );
        
        if (count($_FILES) > 0) {
            // Count total files
            $countfiles = count($_FILES['files']['name']);
            // Looping all files
            $SUCESS = [];
            for ($i = 0; $i < $countfiles; $i++) {
                $filename = $_FILES['files']['name'][$i];
                $imageProperties    = getimageSize($_FILES['files']['tmp_name'][$i]);
                $ext                = pathinfo($_FILES['files']['name'][$i]);
                $customStamp        = date('Ymdhis_') . explode('.', microtime(true))[1] . '_';
                $customOutput       = '../upload/aci/articles/' . $customStamp .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];

                //UPLOAD 1 by 1 and set in database
                $UPLOAD             = move_uploaded_file($_FILES['files']['tmp_name'][$i], $customOutput);


                // $customOutput       = date('Ymdhis_v_') .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];
                // $path               = 'upload/galerie/' . (CLEAN($_POST['droptarget'])) . '/';
                // $UPLOADFILE         = UPLOAD($_FILES['file'], '../' . $path, $customOutput);
                

                if ($UPLOAD) {
                    $SUCCESS[$customStamp] = $customOutput;

                    // echo $sql;
                    // $UPDATE_SAFE = UPDATE('articles', array('images' => 'JSON_SET(images, "$.' . $customStamp . '", "' . str_replace('../', '', $customOutput) . '")'), 'WHERE id=' . CLEAN($_POST['aid']), true);
                    $dataArr['images'] = '{"'.$customStamp.'":"'.str_replace('../', '', $customOutput).'"}';
                }
            }
        }
        $INSERT_EVENT = INSERT(
            'articles',
            $dataArr,
            false
        );
        if ($INSERT_EVENT) {
            $checker->innertext = "L'article a été ajouté avec succès";
            $checker->{'class'} .= ' green';
        }
    }
    if ($_POST['action'] == "editArticle") {

        $UPDATE_GROUPE = UPDATE(
            'articles',
            array(
                'text'      => CLEAN($_POST['article_name']),
                'content'   => CLEANCHARS($_POST['article_content']),
                'eventDate' => CLEAN(gmdate('Y-m-d H:i:s', strtotime($_POST['article_date']))),
                'eventEnd'  => CLEAN(gmdate('Y-m-d H:i:s', strtotime($_POST['article_end']))),
                'author'    => 1,
                'status'    => 1
            ),
            'WHERE id=' . CLEAN($_POST['aid']),
            false
        );

        if (count($_FILES) > 0) {
            // Count total files
            $countfiles = count($_FILES['files']['name']);
            // Looping all files
            $SUCESS = [];
            for ($i = 0; $i < $countfiles; $i++) {
                $filename = $_FILES['files']['name'][$i];
                $imageProperties    = getimageSize($_FILES['files']['tmp_name'][$i]);
                $ext                = pathinfo($_FILES['files']['name'][$i]);
                $customStamp        = date('Ymdhis_') . explode('.', microtime(true))[1] . '_';
                $customOutput       = '../upload/aci/articles/' . $customStamp .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];

                //UPLOAD 1 by 1 and set in database
                $UPLOAD             = move_uploaded_file($_FILES['files']['tmp_name'][$i], $customOutput);


                // $customOutput       = date('Ymdhis_v_') .  NORMALIZE_ACCENTS($ext['filename']) . '.' . $ext['extension'];
                // $path               = 'upload/galerie/' . (CLEAN($_POST['droptarget'])) . '/';
                // $UPLOADFILE         = UPLOAD($_FILES['file'], '../' . $path, $customOutput);
                

                if ($UPLOAD) {
                    $SUCCESS[$customStamp] = $customOutput;
                    $sql    = 'update articles set images = JSON_SET(images, "$.' . $customStamp . '", "' . str_replace('../', '', $customOutput) . '") where id=' . CLEAN($_POST['aid']) . ';';
                    $update = sqlite_query($connection, $sql);

                    // echo $sql;
                    // $UPDATE_SAFE = UPDATE('articles', array('images' => 'JSON_SET(images, "$.' . $customStamp . '", "' . str_replace('../', '', $customOutput) . '")'), 'WHERE id=' . CLEAN($_POST['aid']), true);
                }
            }
        }

        if ($UPDATE_GROUPE) {
            $checker->innertext = "Modification éffectuée";
            $checker->{'class'} .= ' green';
        }
    }
    if ($_POST['action'] == "open") {
        $EDITOR             = $template->find('[id=editor]', 0);
        $GET_SINGLE_ARTICLE = SELECT('articles', '*', 'WHERE id=' . CLEAN($_POST['aid']), false);

        if ($GET_SINGLE_ARTICLE) {

            //Swap Title
            $EDITOR->parent()->find('summary span', 0)->innertext = "Édition de l'article";

            while ($DATA = fetch_array($GET_SINGLE_ARTICLE)) {
                //Fillup contents
                $EDITOR->find('[name=article_name]',              0)->value = $DATA['text'];
                $EDITOR->find('[name=article_content]',           0)->value = $DATA['content'];
                $EDITOR->find('[data-edit=#article_content]', 0)->innertext = CLEANCHARS($DATA['content'], false);

                $EDITOR->find('[name=article_date]',           0)->value = dateToFrench($DATA['eventDate'], 'Y-m-d');
                $EDITOR->find('[name=article_end]',           0)->value = dateToFrench($DATA['eventEnd'], 'Y-m-d');

                //Transform Add to Edit
                $EDITOR->find('[name=action]', 0)->value = "editArticle";

                $JSON = json_decode($DATA['images'], true);

                //Fillup images
                $UI_GRID = $EDITOR->find('.grid', 0);
                $UI_ITEM = $UI_GRID->find('label', 0);
                $GRID_STACK = (string) null;
                foreach ($JSON as $key => $IMG_URL) {
                    $UI_ITEM->find('figure', 0)->{'style'} = 'background-image:url(../' . IMAGE("crop", 200, 200, $IMG_URL) . ')';
                    $UI_ITEM->find('figure', 0)->{'data-post'} = '#checkbar';
                    $UI_ITEM->find('figure', 0)->{'data-uri'}  = $key;
                    $UI_ITEM->find('figure', 0)->{'data-role'}  = $DATA['id'];

                    $GRID_STACK .= $UI_ITEM;
                }
                $UI_GRID->innertext = $GRID_STACK;

                //Add ID to edit coz there is none when "ADD"
                $EDITOR->innertext .= '<input type="hidden" name="aid" value="' . $DATA['id'] . '">';
            }
            $checker->innertext = "Ouverture de l'article pour modifications";
            $checker->{'class'} .= ' blue';
        }
    }
    if ($_POST['action'] == "deleteArticle") {

        $DELETE_ARTICLE = UPDATE('articles', array('status' => 0), 'WHERE id=' . CLEAN($_POST['aid']), false);
        if ($DELETE_ARTICLE) {
            $checker->innertext = "L'article a été supprimé avec succès";
            $checker->{'class'} .= ' yellow';
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
 *    GET ALL ARTICLES
 ************************/

$_GET_ARTICLES = SELECT(
    'articles',
    'articles.*,
    (SELECT COUNT(*) FROM articles WHERE status>0) as count',
    'WHERE status=1 ORDER BY articles.date DESC',
    false
);
if ($_GET_ARTICLES) {
    $UI_PREVIEW = $template->find('[id=preview] div', 0);
    $UI_DETAILS = $UI_PREVIEW->find('details', 0);
    $UI_SUMMARY = $UI_DETAILS->find('summary', 0);
    $UI_TOGGLE = $UI_DETAILS->find('.toggle', 0);

    $STACK_DETAILS = (string) null;
    while ($DATA = fetch_array($_GET_ARTICLES)) {

        $UI_SUMMARY->find('span', 0)->innertext      = $DATA['text'];
        $UI_DETAILS->find('h2',   0)->innertext      = $DATA['text'];
        $UI_DETAILS->find('.contents', 0)->innertext = /* HTML to Plain text for preview */ mk_text(str_get_html(CLEANCHARS($DATA['content'], false))->plaintext, 200);

        //Set Edit Button
        $UI_DETAILS->find('.button.edit', 0)->{'for'}                   = "#E" . $DATA['id'];
        $UI_DETAILS->find('.forms .edit input[type=submit]', 0)->{'id'} = "#E" . $DATA['id'];

        //Set Delete Button
        $UI_DETAILS->find('.button.delete', 0)->{'for'}                   = "#D" . $DATA['id'];
        $UI_DETAILS->find('.forms .delete input[type=submit]', 0)->{'id'} = "#DD" . $DATA['id'];
        $UI_TOGGLE->find('[type=checkbox]', 0)->{'id'} = "#D" . $DATA['id'];
        foreach ($UI_TOGGLE->find('.check') as $checkback) {
            $checkback->{'for'} = "#D" . $DATA['id'];
        }
        $UI_TOGGLE->find('.del', 0)->{'for'} = "#DD" . $DATA['id'];

        foreach ($UI_DETAILS->find('[name=aid]') as $ID) {
            $ID->value = $DATA['id'];
        }

        $STACK_DETAILS .= $UI_DETAILS;
    }
    $UI_PREVIEW->innertext = $STACK_DETAILS;
}

// /********************************
//  *  GET ALL GROUPS for <select>
//  ********************************/
// $UI_UPLOAD   = MODULE('ui_upload');
// $UI_SELECT   = $UI_UPLOAD->find('[name=droptarget]', 0);
// $UI_OPTION   = $UI_SELECT->find('option', 1);
// $UI_STACK    = (string) null;
// $_GET_GROUPS = SELECT(
//     'articles',
//     'articles.*',
//     'WHERE status=1 ORDER BY id DESC',
//     false
// );

// while ($DATA = fetch_array($_GET_GROUPS)) {
//     $UI_OPTION->{'value'} = $DATA['id'];
//     $UI_OPTION->innertext = dateToFrench($DATA['eventDate'],      'd F Y') . ' __ ' . $DATA['text'];
//     $UI_STACK .= $UI_OPTION;
// }

// $UI_OPTION->outertext = $UI_STACK;

// $template->find('[id=upload]',     0)->innertext .= $UI_UPLOAD;
// $template->find('[id=edit_image]', 0)->innertext  = $UI_STACK;
// $card->find('a', 0)->innertext = "s";

