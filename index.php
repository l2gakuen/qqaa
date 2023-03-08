<?php
header('Content-Type: text/html; charset=utf-8');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

//Script starts NOW, start counting...
$start = microtime(true);

$isManager = false;
require('include/sqlite.php');

// require('vendor/autoload.php');

require('include/database.php');
require('include/parse.php');
require('include/fonction.php');
require('include/dafunc.php');
require('include/function_cars.php');
require('include/class.magic-min.php');

function is_base64($s)
{
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
}

$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.'
);

$phpFileUploadErrorsInFrench = array(
    0 => 'Aucune erreur, le fichier a été envoyé avec succès',
    1 => 'Le fichier téléchargé dépasse la taille maximale autorisée par le serveur',
    2 => 'Le fichier téléchargé dépasse la taille maximale autorisée par le formulaire',
    3 => 'Le fichier a été partiellement envoyé',
    4 => 'Aucun fichier n\'a été envoyé',
    6 => 'Il manque un dossier temporaire',
    7 => 'Echec de l\'écriture du fichier sur le disque',
    8 => 'Une extension PHP a arrêté l\'envoi de fichier.'
);

//unset($_SESSION['panier']);
// unset($_SESSION['panier']['2246']);

// echo rootURL();
// exit;

//SETUP TEMPLATE
$html     = str_get_html(file_get_contents('index_manager.html'));
$head     = $html->find('head', 0);
$body     = $html->find('body', 0);
$main     = $body->find('.main', 0);
$cards    = $html->find('.cards', 0);
$card     = $cards->find('.card', 0);
$checker  = $html->find('[id=checker]', 0);

$customLayout = false;
$SCRIPTS = (string) null;

//SETUP PANIER
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];

//LOGIN 
if (!isset($_SESSION["access"]) == "true") {
    $login = file_get_html('login.html');
    $loading = file_get_html('loading.html');


    foreach ($login->find('script') as $script) {
        $script->{'src'} .= "?v=" . date('his');
    }
    if (isset($_POST["logon"])) {
        $_CHECK_USER = SELECT('utilisateurs', 'id, password, type', 'WHERE email="' . CLEAN($_POST['email']) . '"', false);

        if ($_CHECK_USER) {
            $Results = fetch_array($_CHECK_USER);
            if (password_verify($_POST['password'], $Results['password'])) {
                //Login Good
                //Create Sessions
                $_SESSION["access"] = 'true'; //string
                $_SESSION['userID'] = $Results['id'];
                $_SESSION['type']   = $Results['type'];

                echo minify_html($loading);

                //JS ON
                header("HTTP/1.1 202 Accepted"); //202 = ACCEPTED, js listens to 202 then refresh
                //JS OFF
                header('refresh:2;url=' . curPageURL()); //PHP refresh
                //Legacy Code
                // echo '<script>setTimeout(function(){ window.location = "' . url('participer') . '"; }, 200);</script>';

                //Safe Exit
                exit;
            } else {
                //Login Bad
                $checker->innertext = 'Mot de passe incorrect';
                $checker->innertext = 'Identifiants incorrects';
                $LOGS_ARRAY = array(
                    'date' => date('Y-m-d H:i:s'),
                    'info' => 'Attack',
                    'cmd'  => ENCRYPT(json_encode(array(
                        'ip'   => $_SERVER['REMOTE_ADDR'],
                        'url'  => $_SERVER['REQUEST_URI'],
                        'user' => $_SERVER['HTTP_USER_AGENT'],
                        'post' => $_POST,
                        'get' => $_GET
                    ))),
                );
                $INSERT_LOG = INSERT('logs', $LOGS_ARRAY, false);
            }
        } else {
            //Login Bad
            $checker->innertext = 'Identifiants incorrects';
            $LOGS_ARRAY = array(
                'date' => date('Y-m-d H:i:s'),
                'info' => 'Attack',
                'cmd'  => ENCRYPT(json_encode(array(
                    'ip'   => $_SERVER['REMOTE_ADDR'],
                    'url'  => $_SERVER['REQUEST_URI'],
                    'user' => $_SERVER['HTTP_USER_AGENT'],
                    'post' => $_POST,
                    'get' => $_GET
                ))),
            );
            $INSERT_LOG = INSERT('logs', $LOGS_ARRAY, false);
        }
    }

    echo minify_html($login);
    exit;
}

//LOGOFF
if (isset($_GET["logoff"])) {
    session_destroy();
    header('refresh:0;url=' . rootURL()); //PHP refresh
    exit;
}
//Align FORMS dynamically
foreach ($html->find('form') as $form) {
    $form->{'action'} = curPageURL();
}


/* MODULES CHANGES */
$module     = (isset($_GET['mod']) ? $_GET['mod'] : "manager_home");
$incmod     = "modules/" . $module . ".php";
// $mod        = (isset($_GET['mod']) ? $_GET['mod'] : "manager_home"); //dafuk

if (file_exists($incmod) && file_exists('pages/' . $module . '.html')) {

    $template   = MODULE($module);
    $main->{'class'} .= ' ' . $module;
    //PHP FILE
    include($incmod);
    // $page->innertext = $template;
}



//Conditions
if (isset($_POST) && !empty($_POST)) {

    /**
     * 
     *  POST
     * 
     */
} else {

    /**
     * 
     *  GET
     * 
     */

    if (isset($_GET) && !empty($_GET)) {
    }
}

/**
 * 
 *  BOTH
 *  
 */


require('snippets/navigation.php');



$page = $html->find('[id=page]', 0);
$page->innertext .= $template;

/***************
 *   MINIFY
 ***************/

$minified = new Minifier(
    array(
        'gzip'    => false,
        'closure' => false,
        'echo'    => false
    )
);
$exclude_styles = array(
    'css/svg.css'
);
$exclude = array(
    'js/exclude.js'
);

// $head->find('[rel=stylesheet]', -1)->{'href'} .= '?v=' . date('myis');
$body->find('script[src]',      -1)->{'src'}   = $minified->merge('js/packed.min.js',  'js',  'js',  $exclude); // . '?v=' . date('myis');

//REPARSE
$html = str_get_html($html);

// $mixQuery   = mixQuery();
// $SELECTCONTENTS = SELECT('contents', '*', 'WHERE 1');
// $SELECTTYPE     = SELECT('produits', 'DISTINCT type', 'WHERE 1');
// $SELECTCATG     = SELECT('produits', 'DISTINCT categorie', 'WHERE categorie<>"" ORDER BY categorie ASC');


$CONTENT = [];

function statusFromID($id)
{
    switch ($id) {
        case 0:
            $status = "zero";
            break;
        case 1:
            $status = "one";
            break;
        case 2:
            $status = "two";
            break;
        default:
            $status = "none";
    }
    return "status " . $status;
}

// if ($SELECTCONTENTS) {
//     /**
//      * PASTES EVERY PAGES CONTENT
//      */
//     while ($data = fetch_array($SELECTCONTENTS)) {
//         $CONTENT[$data['role']] = $data;
//     }
//     foreach ($CONTENT as $ind => $val) {
//         $cms = json_decode($CONTENT[$ind]['content'], true);
//         foreach ($cms as $ctname => $ctvalue) {
//             $target = $html->find('[name=' . $ctname . ']', 0);
//             // $targets = $html->find('[name=' . $ctname . ']');
//             if ($target) {
//                 // foreach ($targets as $targer) {
//                 if ($target->tag == "textarea") {
//                     $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS($ctvalue, false)); //(is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue)); //($ctvalue);
//                 } elseif ($target->tag == "input") {
//                     $target->value = CLEANCHARS($ctvalue, false);
//                 } elseif ($target->tag == "div") {
//                     // $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS($ctvalue, false)); //(is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue)); //($ctvalue);
//                     $target->innertext = str_replace('<br>', "\r\n", CLEANCHARS((is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue), false)); //; //($ctvalue);
//                 }
//                 if ($target->{'class'} == "editor") {
//                     $html->find('[data-edit=#' . $target->id . ']', 0)->innertext = (is_base64($ctvalue) ? base64_decode($ctvalue) : $ctvalue);
//                 }
//                 // }
//             }
//         }
//     }
//     $images = json_decode($CONTENT['images']['content'],  true);
//     foreach ($images as $name => $value) {
//         $t = $html->find('.' . $name, 0);
//         if ($t) {
//             $html->find('.' . $name, 0)->{'src'} = "../images.php?w=150&h=150&src=" . $value;
//         }
//     }
// }


foreach ($html->find('.iframe') as $iframe) {
    $iframe->{'src'} = $iframe->{'data-src'} ? url($iframe->{'data-src'}) : rootURL(); //. "?mod=" . $mod;
}
foreach ($html->find('form') as $form) {
    if (empty($form->{'action'})) {
        $form->{'action'} = $form->{'data-action'} ? url($form->{'data-action'}) : curPageURL();
    }
    $form->{'data-action'} = null;
}
/* Pareil, marre de taper des urls dynamiques, on va le faire depuis HTML */
foreach ($html->find('[data-url]') as $dataurl) {
    $attr              = $dataurl->{'data-url'};
    $dataurl->{'href'} = (function ($attr) {
        switch ($attr) {
            case "root":
                return rootURL();
                break;
            case "current":
                return curPageURL();
                break;
            default:
                return url($attr, true);
                break;
        }
    })($attr);
    $dataurl->{'data-url'} = null;
}

foreach ($head->find('[rel=stylesheet]') as $css) {
    $css->{'href'} .= '?v=' . date('his');
}

foreach ($html->find('script') as $js) {
    // $js->{'src'} .= '?v=' . date('his');
}

//Si le layout n'est pas custom, uniformise MAIN+SIDE, sinon, prends ce qu'il ya dans la template HTML
//Le custom est déclaré en debut d'index.php, et doit etre changé mid- module.php
if (!$customLayout) {
    $html->find('[id=main]', 0)->{'class'} = 'row-start-1 col-start-1 row-end-4 col-end-12 col-md-end-13 col-lg-end-11 col-xl-end-10';
    $html->find('[id=side]', 0)->{'class'} = 'row-start-1 row-end-4 col-start-1 col-end-13 col-md-start-5 col-lg-start-8 col-xl-start-10';
}

$html->find('[id=main]', 0)->addClass('box d-flex flex-column');



//Script ends now, stop counting AND DISPLAY TIME
$time_elapsed_secs = round(microtime(true) - $start, 2);
// $footer->find('[id=time]', 0)->innertext = 'Cette page à été générée en ' . $time_elapsed_secs . 's ! <span class="fwb color-2">AMAZING !</span>';

$html->find('script', 0)->innertext .= 'console.log("Cette page à été générée en ' . $time_elapsed_secs . 's !")';
$html->find('body', 0)->innertext   .= $SCRIPTS;
//DISPLAY
echo minify_html($html);
