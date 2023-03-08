<?php


if (isset($_POST) && !empty($_POST)) {

    /***********************************
     * 
     *     POST
     * 
     ***********************************/

    if (isset($_POST['action'])) {

        /* Edit Menus */
        $menudata = array(
            'titre'       => CLEANCHARS($_POST['titre_menu']),
            'description' => CLEANCHARS($_POST['description_menu']),
            'destination' => CLEANCHARS($_POST['destination_menu']),
            'meta_titre' => CLEANCHARS($_POST['meta_titre']),
            'meta_descr' => CLEANCHARS($_POST['meta_descr']),
            'ordre'       => $_POST['ordre'],
            'status'      => (isset($_POST['status']) ? 1 : 0)
        );
        if ($_POST['action'] == "addMenu") {
            $NEW = INSERT('navigation', $menudata, false);
            if ($NEW) {
                $checker->innertext = "Le Menu à bien été ajouté.";
            }
        }
        if ($_POST['action'] == "editMenu") {
            $UPDATE = UPDATE('navigation', $menudata, 'WHERE id="' . CLEAN($_POST['menuID']) . '"', false);
            if ($UPDATE) {
                $checker->{'class'} = "green";
                $checker->innertext = "le Menu à bien été modifié.";
            }
        }
    }
} else {

    /***********************************
     * 
     *      GET
     * 
     ***********************************/

    if (isset($_GET) && !empty($_GET)) {
        if (isset($_GET['b'])) {


            $OPENMENU = SELECT('navigation', '*, (SELECT json_extract(content, "$.siteweb") FROM contents WHERE role="general") as siteweb', 'WHERE id="' . CLEAN($_GET['b']) . '"', false);
            $form = $template->find('[id=menu-form]', 0);
            $UI_GOOGLE = $template->find('.google', 0);
            $UI_UL = $UI_GOOGLE->find('ul', 0);

            if ($OPENMENU) {

                while ($data = fetch_array($OPENMENU)) {

                    $form->find('[name=menuID]',           0)->value    = $data['id'];
                    $form->find('[name=titre_menu]',       0)->value    = $data['titre'];
                    $form->find('[name=description_menu]', 0)->value    = $data['description'];
                    $form->find('[name=destination_menu]', 0)->value    = $data['destination'];
                    $form->find('[name=meta_titre]', 0)->value     = $data['meta_titre'];
                    $form->find('[name=meta_descr]', 0)->innertext = $data['meta_descr'];
                    $form->find('[name=ordre]',       0)->value    = $data['ordre'];
                    $form->find('[name=status]',      0)->checked  = $data['status'] == 1 ? true : null;
                    $form->find('[name=action]',      0)->value    = "editMenu";
                    $form->find('[type=submit]',      0)->value    = "&#xf0c7 Modifier Article";

                    $UI_GOOGLE->find('h3', 0)->innertext = $data['meta_titre'] . ' - ' . $data['siteweb'];
                    $UI_GOOGLE->find('h4', 0)->innertext = $data['meta_descr'];

                    $UI_UL->find('li', 0)->innertext = $data['destination'] == 'root' ? rootURL() : url($data['destination']);
                }

                $select = $form->find('[id=destinationlist]', 0);
                $option = $select->find('option', 1);
                $MODULEOPT = (string) null;
                $MODULEFOLDER = glob('../modules/*.{php}', GLOB_BRACE);

                foreach ($MODULEFOLDER as $modulefile) {
                    $pathinfo = pathinfo($modulefile);
                    $option->value     = $pathinfo['filename'];
                    $option->innertext = $pathinfo['filename'];
                    $MODULEOPT .= $option;
                }
                $option->outertext = $MODULEOPT;

                $checker->innertext = "Ouverture du menu";
                $checker->addClass('blue');
            }
        }
    }
}

/***********************************
 * 
 *      AFTER BOTH
 * 
 ***********************************/

$SELECTMENU     = SELECT('navigation', '*', 'WHERE 1 ORDER BY ordre ASC');
if ($SELECTMENU) {
    $MENUSTACK = (string) NULL;
    $menutable = $template->find('[id=menutable]', 0)->find('tr', 0);
    while ($data = fetch_array($SELECTMENU)) {
        $menutable->find('td', 0)->find('a', 0)->innertext  = $data['titre'];
        $menutable->find('td', 0)->find('a', 0)->{'data-action'} = url($module . '&b=' . $data['id'], true);
        $menutable->find('td', 0)->find('a', 0)->{'href'} = url($module . '&b=' . $data['id'], true);
        $menutable->find('td', 1)->innertext = $data['description'];
        $menutable->find('td', 2)->innertext = $data['destination'];
        $menutable->find('td', 3)->innertext = $data['ordre'];
        $menutable->find('td', -1)->find('span', 0)->innertext = $data['status'] == 1 ? "Activé" : "Désactivé";
        $menutable->find('td', -1)->find('span', 0)->{'class'} = statusFromID($data['status']);
        $MENUSTACK .= $menutable;
    }
    $menutable->outertext = $MENUSTACK;
}

$form = $template->find('[id=menu-form]', 0);
$select = $form->find('[id=destinationlist]', 0);
$option = $select->find('option', 1);
$MODULEOPT = (string) null;
$MODULEFOLDER = glob('../modules/*.{php}', GLOB_BRACE);

foreach ($MODULEFOLDER as $modulefile) {
    $pathinfo = pathinfo($modulefile);
    $option->value     = $pathinfo['filename'];
    $option->innertext = $pathinfo['filename'];
    $MODULEOPT .= $option;
}
$option->outertext = $MODULEOPT;
