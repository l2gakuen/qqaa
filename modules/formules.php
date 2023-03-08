<?php

$msgform = $template->find('[id=msgform]', 0);
$table   = $template->find('[id=msgtable]', 0);

if ((isset($_POST) && !empty($_POST))) {
    /***********************************
     * 
     *      POST
     * 
     ***********************************/

    $inserformule = array(
        'nom'         => CLEANCHARS($_POST['nom_formule']),
        'content'     => CLEANCHARS($_POST['content_formule']),
        'prix'        => CLEANCHARS($_POST['prix_formule']),
        'status'      => (isset($_POST['status']) ? 1 : 0)
    );
    /* Insert Produit */
    if ($_POST['action'] == "add_formule") {
        $NEWFORM = INSERT('formules', $inserformule);
    }
    /* Edit Produit */
    if ($_POST['action'] == "edit_formule") {
        $UPDATE = UPDATE('formules', $inserformule, 'WHERE id="' . $_POST['formule_id'] . '"');
        if ($UPDATE) {
            $checker->{'class'} = "green";
            $checker->innertext = "la Formule " . $inserformule['nom'] . " (#" . $_POST['formule_id'] . ") à bien été modifié.";
        }
    }
} else {
    /***********************************
     * 
     *      GET
     * 
     ***********************************/

    if (isset($_GET['f'])) {
        $formf = $template->find('[id=form_formule]', 0);
        $OPEN = SELECT('formules', '*', 'WHERE id="' . CLEAN($_GET['f']) . '"');
        if ($OPEN) {
            $formf->find('h2', 0)->innertext            = "Modifier la formule";
            $formf->find('[type=submit]', 0)->value     = "Modifier";
            while ($data = fetch_array($OPEN)) {
                // $zone2->find('h2',                     0)->innertext = "Article #" . $data['id'];
                $formf->find('input[name=formule_id]',          0)->value     = $data['id'];
                $formf->find('input[name=nom_formule]',         0)->value     = $data['nom'];
                $formf->find('[name=content_formule]',          0)->innertext = CLEANCHARS($data['content'], false);
                // $form->find('input[name=type_article]',        0)->value    = $data['type'];
                // $form->find('input[name=categorie]',   0)->value    = $data['categorie'];
                $formf->find('input[name=prix_formule]',        0)->value    = $data['prix'];
                // $form->find('input[name=tags]',        0)->value    = $data['tags'];
                // $form->find('input[name=status]',      0)->checked  = $data['status'] == 1 ? true : null;
                $formf->find('input[name=action]',      0)->value    = "edit_formule";
                // $form->find('input[type=submit]',      0)->value    = "&#xf0c7 Modifier Article";
                // $dele->find('input[name=delete_id]',          0)->value    = $data['id'];
            }
        } else {
            $checker->innertext = "Error";
        }
    }
}

/***********************************
 * 
 *      BOTH
 * 
 ***********************************/

$FORMULES = SELECT('formules', '*', 'WHERE 1');
if ($FORMULES) {
    $FORMULESTACK = (string) NULL;
    $produits = $template->find('[id=produits]', 0);
    $produit   = $produits->find('tr', 0);
    while ($data = fetch_array($FORMULES)) {
        $produit->find('td', 0)->find('a', 0)->innertext = $data['nom'];
        $produit->find('td', 0)->find('a', 0)->{'action'} = 'index.php?mod=formules&f=' . $data['id'];
        $produit->find('td', 0)->find('a', 0)->{'href'}   = 'index.php?mod=formules&f=' . $data['id'];
        $produit->find('td', 1)->innertext = CLEANCHARS($data['content'], false);
        $produit->find('td', 2)->innertext = $data['prix'] . ' €';
        $produit->find('td', -1)->find('span', 0)->{'class'} = statusFromID($data['status']);
        $produit->find('td', -1)->find('span', 0)->innertext = $data['status'] == 1 ? "Activé" : "Désactivé";
        $FORMULESTACK .= $produit;
    }
    $produits->outertext = $FORMULESTACK;
}
