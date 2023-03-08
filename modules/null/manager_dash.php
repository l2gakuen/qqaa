<?php

$zone2      = $template->find('[id=form]', 0);
$produits   = $template->find('[id=produits]', 0);
$produit    = $produits->find('tr', 0);

if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST
     * 
     */

    if (isset($_POST['action'])) {
        $insertdata = array(
            'nom'         => ($_POST['nom_article']),
            'description' => CLEAN($_POST['description_article']),
            'type'        => CLEAN($_POST['type_article']),
            'categorie'   => CLEAN($_POST['categorie']),
            'prix'        => CLEAN($_POST['prix']),
            'tags'        => CLEAN($_POST['tags']),
            'status'      => (isset($_POST['status']) ? 1 : 0)
        );

        /* Insert Produit */
        if ($_POST['action'] == "add") {
            $NEW = INSERT('produits', $insertdata, false);
        }

        /* Edit Produit */
        if ($_POST['action'] == "edit") {
            $UPDATE = UPDATE('produits', $insertdata, 'WHERE id="' . $_POST['article_id'] . '"', false);
            if ($UPDATE) {
                $checker->{'class'} = "green";
                $checker->innertext = "l'Article " . $insertdata['nom'] . " (#" . $_POST['article_id'] . ") à bien été modifié.";
            }
        }

        /* Delete Produit */
        if ($_POST['action'] == "delete") {
            $DELETE = DELETE('produits', 'WHERE id="' . $_POST['delete_id'] . '"');
            if ($DELETE) {
                $checker->{'class'} = "green";
                $checker->innertext = "l'Article " . $insertdata['nom'] . " (#" . $_POST['article_id'] . ") à été éffacé.";
            }
        }
    }
    
} else {

    /**
     * 
     *  GET
     * 
     */

    if (isset($_GET['a'])) {
        $form  = $zone2->find('form', 0);
        $dele  = $zone2->find('form', 1);

        $OPEN = SELECT('produits', '*', 'WHERE id="' . $_GET['a'] . '"');
        if ($OPEN) {
            while ($data = fetch_array($OPEN)) {
                $zone2->find('h2',                     0)->innertext = "Article #" . $data['id'];
                $form->find('input[name=article_id]',          0)->value    = $data['id'];
                $form->find('input[name=nom_article]',         0)->value    = $data['nom'];
                $form->find('input[name=description_article]', 0)->value    = $data['description'];
                $form->find('input[name=type_article]',        0)->value    = $data['type'];
                $form->find('input[name=categorie]',   0)->value    = $data['categorie'];
                $form->find('input[name=prix]',        0)->value    = $data['prix'];
                $form->find('input[name=tags]',        0)->value    = $data['tags'];
                $form->find('input[name=status]',      0)->checked  = $data['status'] == 1 ? true : null;
                $form->find('input[name=action]',      0)->value    = "edit";
                $form->find('input[type=submit]',      0)->value    = "&#xf0c7 Modifier Article";
                $dele->find('input[name=delete_id]',          0)->value    = $data['id'];
            }

            $form->find('[for=del]', 0)->{'style'} = null;
        } else {
            $zone2->find('h2', 0)->innertext = "Error";
        }
    }
}

/***********************************
 * 
 *      AFTER BOTH
 * 
 ***********************************/




$SELECT     = SELECT('produits', '*', 'WHERE type' . (isset($_GET['type']) ? '="' . $GET['type'] . '"' : '<>""') . (isset($GET['cat']) ? ' AND categorie="' . implode('" OR categorie="', $GET['cat']) . '"' : '') . ' ORDER BY type DESC, categorie ASC, nom ASC');
if ($SELECT) {
    $PRODSTACK = (string) NULL;
    $howMany      = 0;
    $types = [];
    $categ = [];
    while ($data = fetch_array($SELECT)) {
        $produit->find('td', 0)->find('a', 0)->innertext = $data['nom'];
        $produit->find('td', 0)->find('a', 0)->{'action'} = url($mod . '&a=' . $data['id'], true);
        $produit->find('td', 0)->find('a', 0)->{'href'}   = url($mod . '&a=' . $data['id'], true);
        $produit->find('td', 1)->innertext = $data['type'];
        $produit->find('td', 2)->innertext = $data['categorie'];
        $produit->find('td', 3)->innertext = $data['prix'] . ' €';
        $produit->find('td', 4)->innertext = $data['tags'];
        $produit->find('td', 5)->find('span', 0)->{'class'} = statusFromID($data['status']);
        $produit->find('td', 5)->find('span', 0)->innertext = $data['status'] == 1 ? "Activé" : "Désactivé";
        $PRODSTACK .= $produit;
        $howMany++;
        $types[] = $data['type'];
        $categ[] = $data['categorie'];
    }
    //TYPES
    $typelist = $zone2->find('[id=typelist]', 0);
    $typeopt  = $typelist->find('option', 0);
    $typeSTACK = (string) NULL;
    foreach (array_unique($types) as $type) {
        $typeopt->value = $type;
        $typeopt->innertext  = $type;
        $typeSTACK .= $typeopt;
    }
    $typeopt->outertext = $typeSTACK;

    //CATEGORIES
    $catlist = $zone2->find('[id=catlist]', 0);
    $catopt  = $catlist->find('option', 0);
    $catSTACk = (string) NULL;
    foreach (array_unique($categ) as $cat) {
        $catopt->value = $cat;
        $catopt->innertext  = $cat;
        $catSTACk .= $catopt;
    }
    $catopt->outertext = $catSTACk;

    $howManyTypes = count(array_unique($types));

    $dash->find('.card', 1)->find('.numbers',  0)->innertext = $howMany;
    $dash->find('.card', 1)->find('.cardName', 0)->innertext = "Produits";
    $dash->find('.card', 2)->find('.numbers',  0)->innertext = $howManyTypes;
    $dash->find('.card', 2)->find('.cardName', 0)->innertext = "Types : " . implode(', ', array_unique($types));


    $produit->outertext = $PRODSTACK;
}
// $FORMULES = SELECT('formules', '*', 'WHERE 1');
// if ($FORMULES) {
//     $FORMULESTACK = (string) NULL;
//     $produits = $template->find('[id=produits]', 0);
//     $produit   = $produits->find('tr', 0);
//     while ($data = fetch_array($FORMULES)) {
//         $produit->find('td', 0)->find('a', 0)->innertext = $data['nom'];
//         $produit->find('td', 0)->find('a', 0)->{'action'} = 'index.php?mod=formules&f=' . $data['id'];
//         $produit->find('td', 0)->find('a', 0)->{'href'}   = 'index.php?mod=formules&f=' . $data['id'];
//         $produit->find('td', 1)->innertext = CLEANCHARS($data['content'], false);
//         $produit->find('td', 2)->innertext = $data['prix'] . ' €';
//         $produit->find('td', -1)->find('span', 0)->{'class'} = statusFromID($data['status']);
//         $produit->find('td', -1)->find('span', 0)->innertext = $data['status'] == 1 ? "Activé" : "Désactivé";
//         $FORMULESTACK .= $produit;
//     }
//     $produits->outertext = $FORMULESTACK;
// }
