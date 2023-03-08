<?php
$navigation = array(
    'home' => array(
        'title' => "Contrats",
        'desc'  => "Edition des contrats",
        'url'   => "manager_home",
        'icon'  => 'fal fa-file',
        'inner' => 'Contrats'
    ),
    'clients' => array(
        'title' => "Voitures",
        'desc'  => "Voitures",
        'url'   => "manager_voitures",
        'icon'  => 'fal fa-car',
        'inner' => 'Voitures'
    ),
    'planning' => array(
        'title' => "planning",
        'desc'  => "planning",
        'url'   => "manager_planning",
        'icon'  => 'fal fa-calendar',
        'inner' => 'planning'
    ),
    // 'messages' => array(
    //     'title' => "Messages",
    //     'desc'  => "Formulaire de Contact",
    //     'url'   => "manager_messages",
    //     'icon'  => 'fal fa-envelope',
    //     'inner' => 'Messagerie'
    // ),
    // 'planning' => array(
    //     'title' => "Planning évènements",
    //     'desc'  => "Randonnées et évènements",
    //     'url'   => "manager_planning",
    //     'icon'  => 'fal fa-calendar',
    //     'inner' => 'Évènements'
    // ),
    // 'users' => array(
    //     'title' => "Utilisateurs",
    //     'desc'  => "Gestion des Utilisateurs",
    //     'url'   => "manager_users",
    //     'icon'  => 'fal fa-users',
    //     'inner' => 'Adhérents'
    // ),
    // 'chantiers' => array(
    //     'title' => "Chantiers",
    //     'desc'  => "--",
    //     'url'   => "manager_chantiers",
    //     'icon'  => 'fal fa-user-hard-hat',
    //     'inner' => 'Chantiers'
    // ),
    // 'theme' => array(
    //     'title' => "Galerie",
    //     'desc'  => "Galerie Photo",
    //     'url'   => "manager_galerie",
    //     'icon'  => 'fal fa-photo-video',
    //     'inner' => 'Galerie Photo'
    // ),
    // 'apropos' => array(
    //     'title' => "À Propos",
    //     'desc'  => "Page",
    //     'url'   => "manager_apropos",
    //     'icon'  => 'fal fa-info',
    //     "inner" => "À Propos"
    // ),
    // 'mention' => array(
    //     'title' => "Mentions & Conditions",
    //     'desc'  => "Mentions et Conditions du Site",
    //     'url'   => "manager_mention",
    //     'icon'  => 'fal fa-gavel',
    //     'inner' => 'Mentions Légales'
    // ),
    // 'mention2' => array(
    //     'title' => "Conditions Ventes",
    //     'desc'  => "Conditions Générales de vente",
    //     'url'   => "manager_mention2",
    //     'icon'  => 'fal fa-mask',
    //     'inner' => 'Protection des données'
    // ),
    // 'navigation' => array(
    //     'title' => "Navigation",
    //     'desc'  => "Menu général du Site",
    //     'url'   => "manager_menu",
    //     'icon'  => 'fal fa-ship',
    //     'inner' => 'Navigation'
    // ),
    // 'general' => array(
    //     'title' => "Paramètres Généraux",
    //     'desc'  => "Informations générales du Site",
    //     'url'   => "manager_general",
    //     'icon'  => 'fal fa-cogs',
    //     'inner' => 'Général'
    // ),
    // 'magic' => array(
    //     'title' => "Crop Image",
    //     'desc'  => "Traitement des images",
    //     'url'   => "manager_image",
    //     'icon'  => 'fal fa-wand-magic',
    //     'inner' => "Traitement des images"
    // ),
    // 'editor' => array(
    //     'title' => "Editeur",
    //     'desc'  => "Experimental",
    //     'url'   => "manager_editor",
    //     'icon'  => 'fal fa-wand-magic',
    //     'inner' => 'Editeur'
    // )
);


/***********************************
 * 
 *      GENERAL NAVIGATION
 * 
 ***********************************/

if (1 < 2) {
    $NAVSTACK = "";
    $navul  = $html->find('.navigation ul', 0);
    $navli  = $navul->find('li', 1);
    $title  = (string) null;
    foreach ($navigation as $nav) {
        $navli->find('.title',   0)->innertext = $nav['title'];
        $navli->find('a',        0)->{'href'}  = url($nav['url'], false);
        $navli->find('a',        0)->{'title'} = $nav['desc'];
        $navli->find('.icon i ', 0)->{'class'} = $nav['icon'];
        $navli->{'class'}        = ($module == $nav['url'] ? 'hovered' : null);

        $_EXPORT = array($module, $nav['url'], ($module == $nav['url'] ? $nav['inner'] : "null"));

        //SETUP Page Title
        if ($module == $nav['url']) {
            $title = $nav['inner'];
        }
        $navli->find('.icon', 0)->{'data-after'} = (function ($_EXPORT) {
            $_COUNT = null;
            // switch ($_EXPORT[1]) {
            //     case 'manager_messages':
            //         $_COUNTMSGS = SELECT('messages', 'COUNT(*) as count', 'WHERE status=0', false);
            //         if ($_COUNTMSGS) {
            //             while ($DATA = fetch_array($_COUNTMSGS)) {
            //                 $_COUNT = $DATA['count'] == 0 ? null : $DATA['count'];
            //             }
            //         }
            //         break;

            //     case 'manager_planning':
            //         $_COUNTPLANNING = SELECT('agendas', 'COUNT(*) as count', 'WHERE MONTH(start) = MONTH(NOW()) AND status=1', false);
            //         if ($_COUNTPLANNING) {
            //             while ($DATA = fetch_array($_COUNTPLANNING)) {
            //                 $_COUNT = $DATA['count'];
            //             }
            //         }
            //         break;

            //     case 'manager_users':
            //         $_COUNTUSERS = SELECT('adherents', '(SELECT queue_cotisations.status FROM queue_cotisations WHERE userID = adherents.id ORDER BY date DESC LIMIT 1) as queue_status', 'WHERE status>0 AND queue_status=0', false);
            //         if ($_COUNTUSERS) {
            //             while ($DATA = fetch_array($_COUNTUSERS)) {
            //                 $_COUNT++;
            //             }
            //         }
            //         break;
            //     default:
            //         $_COUNT = null;
            //         break;
            // }
            return $_COUNT;
        })($_EXPORT);
        $NAVSTACK .= $navli;
    }
    $navli->outertext = $NAVSTACK;
    $html->find('.modtitle', 0)->innertext = $title;
}
