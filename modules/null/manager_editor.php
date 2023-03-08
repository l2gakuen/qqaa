<?php
$customLayout = true;
class CodexToHtml
{

    private $templates = null;
    private $beautify = false;

    function __construct($templates, $beautify = false)
    {
        $this->templates = $templates;
        $this->beautify = $beautify;
    }

    public function render($blocks)
    {
        $result = [];
        foreach ($blocks as $block) {
            if (array_key_exists($block['type'], $this->templates)) {
                $template = $this->templates[$block['type']];
                $data = $block['data'];
                $result[] = call_user_func_array($template, $data);
            }
        }
        $html = implode($result);
        return $html;
    }
}

$codex = new CodexToHtml([
    'raw' => function ($html) {
        return $html;
    },
    'header' => function ($text, $level) {
        return "<h{$level}>{$text}</h{$level}>";
    },
    'paragraph' => function ($text) {
        return "<p>{$text}</p>";
    },
    'image' => function ($file, $caption, $widthBorder, $stretched, $withBackground) {
        return "<img src=\"{$file['url']}\" title=\"{$caption}\" alt=\"{$caption}\">";
    },
    'delimiter' => function () {
        return "<hr>";
    },
    'list' => function ($style, $items) {
        $lis = (string) null;
        foreach ($items as $item) {
            $lis .= "<li>{$item}</li>";
        }
        switch ($style) {
            case 'ordered':
                $type = "ol";
                break;
            case 'unordered':
                $type = "ul";
                break;
        }
        return  "<{$type}>" . $lis . "</{$type}>";
    },
    'table' => function ($withHeadings, $contents) {
        $rows = (string) null;
        $thead = '<thead><tr>';
        foreach ($contents as $i => $content) {

            $rows .= '<tr>';
            foreach ($content as $j => $value) {


                $rows .= '<td>' . $value . '</td>';
            }
            $rows .= '</tr>';
        }
        $thead .= '</thead>';
        return '<table>' . $thead . '<tbody>' . $rows . '</tbody></table>';
    },
]);

if ((isset($_POST) && !empty($_POST))) {

    /***********************************
     * 
     *      POST
     * 
     ***********************************/

    $data_array = array(
        'content'  => (str_replace(array("'"), array("&apos;"), CLEAN($_POST['output']))),
        'bg_color' => CLEAN($_POST['bg_color']),
        'title'    => CLEAN($_POST['a_title']),
        'subtitle' => CLEAN($_POST['a_subtitle']),
        'bg_color' => CLEAN($_POST['bg_color'])
    );

    if ($_POST['action'] == "create") {
        $data_array['role'] = CLEAN($_POST['a_title']);
        $INSERT_PAGE = INSERT('pages', $data_array, false);

        if ($UPDATE_PAGE) {
            $checker->innertext = "La Page a été créée";
            $checker->addClass('blue');
        } else {
            $checker->innertext = "Une erreur est survenue, la page existe peut-être déja";
            $checker->addClass('red');
        }
    }
    if ($_POST['action'] == "update" && !empty($_GET['role'])) {
        $data_array['role'] = CLEAN($_GET['role']);
        $UPDATE_PAGE = UPDATE('pages', $data_array, "role = '" . CLEAN($_GET['role']) . "'", false);
        if ($UPDATE_PAGE) {
            $checker->innertext = "La Page a été mise à jour avec succès";
            $checker->addClass('blue');
        } else {
            $checker->innertext = "Une erreur est survenue lors de la mise à jour de la page";
            $checker->addClass('red');
        }
    }
    if ($_POST['action'] == "upload_image") {

        $imgData            = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        $imageProperties    = getimageSize($_FILES['image']['tmp_name']);
        $ext                = pathinfo($_FILES['image']['name']);
        $customOutput       = date('Ymdhis_v_') .  ($ext['filename']) . '.' . $ext['extension'];
        $path               = 'upload/assets/';

        // echo $customOutput;
        $UPLOADFILE         = UPLOAD($_FILES['image'], '../' . $path, $customOutput);
        if ($UPLOADFILE) {
            echo '{
                "success" : 1,
                "file": {
                    "url" : "../' . $path . $customOutput . '"
                }
            }';
            exit;
        }
        // exit;
    }
} else {

    /*********************************** 
     * 
     *      GET
     * 
     ***********************************/


    //Get specific page by ROLE
    if (isset($_GET['role']) && !empty($_GET['role'])) {
        $GET_PAGE = SELECT('pages', '*', 'WHERE role="' . CLEAN($_GET['role']) . '"', false);
        if ($GET_PAGE) {
            $template->find('[name=action]', 0)->value = "update";
            while ($DATA = fetch_array($GET_PAGE)) {
                $blocks = json_decode(($DATA['content']), true)['blocks'];
                $holder = 'editorjs';
                $SCRIPTS .=
                    '<script>
                    const holder = document.querySelector(document.getElementById("' . $holder . '").dataset.target);
                    window.addEventListener("load", function () {
                        const editor = new EditorJS({
                            holder: "' . $holder . '",
                            tools: {
                                header : Header,
                                delimiter: Delimiter,
                                list: {
                                    class: List,
                                    inlineToolbar: true,
                                    config: {
                                        defaultStyle: "unordered",
                                    }
                                },
                                image: {
                                    class: ImageTool,
                                    config: {
                                        endpoints: {
                                            byFile: "' . url('manager_editor', true) . '"
                                            //,byUrl: "' . url('manager_editor', true) . '"
                                        },
                                        additionalRequestData: {
                                            "action":"upload_image"
                                        }
                                    }
                                },
                                table: {
                                    class: Table,
                                    inlineToolbar: true,
                                    config: {
                                      rows: 1,
                                      cols: 2,
                                    },
                                },
                            }, data:' . (!empty($DATA['content']) ? ($DATA['content']) : '{}') . ',
                            onChange: function() {
                                save();
                            }
                        });
                        function save(){
                            editor.save().then((outputData) => {
                                console.log("Article data: ", outputData)
                                holder.value = JSON.stringify(outputData); 
                            }).catch((error) => {
                                console.log("Saving failed: ", error)
                            });
                        }
                    });
                </script>';

                // $template->find('[id=side]', 0)->find('div', 0)->innertext = ($codex->render($blocks));
                $output = $template->find('[name=output]', 0);
                $output->innertext = (!empty($DATA['content']) ? ($DATA['content']) : '{}');

                //Extra DATA

                //Color picker
                $template->find('[type=color]', 0)->value = $DATA['bg_color'];

                //Titre
                $template->find('[name=a_title]',    0)->value = $DATA['title'];

                //Sous-titre
                $template->find('[name=a_subtitle]', 0)->value = $DATA['description'];
            }
        }
    }
}

/***********************************
 * 
 *      BOTH
 * 
 ***********************************/



/**
 * SETUP TABLE with ALL pages and Links
 */
$GET_ALL_PAGES = SELECT('pages', '*', 'WHERE 1', false);
if ($GET_ALL_PAGES) {

    //TEMPLATE
    $table  = $template->find('table', 0);
    $tbody  = $table->find('tbody', 0);
    $tr     = $tbody->find('tr', 0);

    //STRING STORAGE
    $_TR_STACK = (string) null;

    //PROCESS
    while ($DATA = fetch_array($GET_ALL_PAGES)) {
        //INSERT DATA in the first TR
        $tr->find('td', 0)->find('a', 0)->innertext = '--';
        $tr->find('td', 1)->find('a', 0)->innertext = $DATA['role'];
        $tr->find('td', 2)->find('a', 0)->innertext = $DATA['title'];
        $tr->find('td', 3)->find('a', 0)->innertext = $DATA['description'];
        // $tr->find('td', 3)->find('a', 0)->innertext = (!empty($DATA['content']) ? DECRYPT($DATA['content']) : '{}');

        //Fill all links at once
        foreach ($tr->find('a') as $link) {
            $link->{'href'} = url($module, true) . '&role=' . $DATA['role']; //current module + extra data
        }

        //STACK
        $_TR_STACK .= $tr;
    }
    //REINSERT
    $tbody->innertext = $_TR_STACK;
} else {
    //On le fait a l'envers : on met une erreur en "dur" dans la template originale comme ça, pas de gestion d'erreur requise si la BDD ne connecte pas, puisque rien ne se passe.
}
