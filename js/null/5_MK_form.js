//QUAND LE FORM SUBMIT
/***********************************************************
 *
 *   My MAAagic JQuery AJAX DATA PUSHER
 *   Replaces Data on screen after a silent GET/POST query
 *   
 ************************************************************/



/******************************************
 *
 *       ON FORMS
 *
 *******************************************/
$(document).on('submit', 'form[data-push]', function (e) {
    $method = ($(this).attr('method') ? $(this).attr('method') : "POST");
    $action = ($(this).attr('action') ? $(this).attr('action') : window.location.href);
    $targets = $(this).attr('data-push').split(',');
    //POST
    $.ajax({
        type: $method,
        url: $action,
        cache: false,
        data: $(this).serialize(),
        beforeSend: function () {
//            $("#loading-image").css('display', 'inline-block');
            $("#loading-image, .loading-image").css('visibility', 'visible');
        },
        success: function (r, textStatus, xhr) {
            if (xhr.status == "202") {
                setTimeout(function () {
                    // window.location.href = '?mod=dashboard';
                    window.location.href = window.location.href;
                }, 2000)
            } else {

                $.each($targets, function (i, v) {
                    if ($(v).is("input")) {
                        $(v).val($(r).find(v).val());
                    } else {
                        $(v).html($(r).find(v).html());
                    }
                });
                afterPush();
                
            }
        }
    });
    e.preventDefault(); //STOP PAGE CHANGE
}); 

/**************************************************
 *
 *       ON TABLES
 *       MAKES any tables a form while clicking TDs,
 *       and Replaces on-screen data
 *
 ****************************************************/

$(document).on('click', 'table[data-push] tbody td, a[data-push], input[type=button][data-push], div.table[data-push] .tbody .tr div', function (e) {
    $form = (function(){
        var $f;
        if(e.target.nodeName == 'TD'){
            $f = e.target.closest('table');
//        } else if(e.target.nodeName == 'A') {
//            $f = e.target;
        } else if(e.target.parentNode.nodeName == 'A') {
            $f = e.target.parentNode;
        } else if(e.target.nodeName == 'DIV' && e.target.parentNode.parentNode.className == 'tbody'){
            $f = e.target.closest('.table');
        } else { //A, INPUT, ETC 
            $f = e.target;
        }
        return $($f); 
    })();
//    $form = ($(e.target).closest('a').length ? $(this) : $(this).closest('table'));
//    console.log($form);
    $method = ($form.attr('method') ? $form.attr('method') : (e.target.parentNode.nodeName == 'A' || e.target.parentNode.nodeName == 'A' ? "GET":"POST"));
    $action = ($form.attr('action') ? $form.attr('action') : window.location.href);
    $targets = $form.attr('data-push').split(',');
    $data = $(this).parent().data('form'); //data-form=
    //    $testdata = {mod: "fiche", voir: $(this).parent().attr('id')};
    $parsedData = (function () {
        var result = {};
        $.each($data, function ($i, $v) {
            var $key = $i
            result[$key] = $data[$i];
        });
        return result; 
    })();
    
    //   
    //POST
    $.ajax({
        type: $method,
        url: $action, 
        cache: false,
        data: $parsedData,
        beforeSend: function () {
            $("#loading-image, .loading-image").css('visibility', 'visible');
        },
        success: function (r, textStatus, xhr) {
        
            if (xhr.status == "202") {
                setTimeout(function () {
                    // window.location.href = '?mod=dashboard';
                    window.location.href = window.location.href;
                }, 2000)
            } else {

                $.each($targets, function (i, v) {
                    if ($(v).is("input")) {
                        $(v).val($(r).find(v).val());
                    } else {
                        $(v).html($(r).find(v).html());
                    }
                });
                afterPush();

            }
           

            
        }
    });
    e.preventDefault(); //STOP PAGE CHANGE
});

function afterPush() {

//    $compensator = "calc( 100vh - "+$(".dataTables_info").outerHeight()+'px - '+$(".dataTables_paginate").outerHeight()+'px - '+$(".heading").outerHeight()+'px - '+$(".dataTables_filter").outerHeight()+'px)';
      
//    console.log($compensator);
//    $("#loading-image, .loading-image").hide(); 
    $("#loading-image, .loading-image").css('visibility', 'hidden'); 
//    ($($table).lengh > 0 ? "" : $table.destroy());
    
//    if ( typeof $table !== 'undefined' ) {
//        $table.destroy();
//    }
//    $table = $('table.search').DataTable({
//        responsive: true,
////        "scrollY" : $compensator,
//        "order": [[(typeof $(this).attr('col') !== typeof undefined && $(this).attr('col') !== false ? $(this).attr('col') : 0), (typeof $(this).attr('order') !== typeof undefined && $(this).attr('order') !== false ? $(this).attr('order') : "desc")]],
//        language: {
//            "sProcessing": "Traitement en cours...", 
//            "sSearch": '<i class="fa fa-search" aria-hidden="true"></i>&nbsp;',
//            "searchPlaceholder": "Quick Search",
//            "sLengthMenu": "Afficher _MENU_ &eacute;l&eacute;ments",
//            "sInfo": "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
//            "sInfoEmpty": "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
//            "sInfoFiltered": "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
//            "sInfoPostFix": "",
//            "sLoadingRecords": "Chargement en cours...",
//            "sZeroRecords": "Aucun &eacute;l&eacute;ment &agrave; afficher",
//            "sEmptyTable": "Aucune donn&eacute;e disponible dans le tableau",
//            "oPaginate": {
//                "sFirst": "Premier",
//                "sPrevious": '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
//                "sNext": '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
//                "sLast": "Dernier"
//            },
//            "oAria": {
//                "sSortAscending": ": activer pour trier la colonne par ordre croissant",
//                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
//            },
//            "bInfo": false,
//
//        }
//    });
//    $("#tabs.vertical").tabs({}).addClass('ui-tabs-vertical ui-helper-clearfix');
//    $("#tabs").tabs({
//        heightStyle: "auto"
//    });
    
//    $('.alert').each(function (i) {
//        $this = $(this);
//        $this.one('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function (e) {
//            setTimeout(function () {
//                $this.css('animation', 'fadeOut 1s ease').one('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function () {
//                    $this.remove();
//                });
//            }, 2500);
//        })
//    });
//    mktable('#table', '[filter]');
} 

/******************************************
 *
 *       PLAYGROUND 
 *
 *******************************************/

//$(document).on('click', '.fiches .dataTable tbody td', function(e) {
////    $form    = $(this).parents
////    $method  = ($(this).attr('method') ? $(this).attr('method') : "POST");
////    $action  = ($(this).attr('action') ? $(this).attr('action') : window.location.href);
////    $targets = $(this).attr('push').split(',');
////    
//    //POST
//    $.ajax({
//       type: "GET",
//       url:  window.location.href,
//       cache: false,
//       data: {mod: "fiche", voir: $(this).parent().attr('id')},
//       success: function(r){               
//           $("#sec").html($(r).find('#sec').html());
//       }
//     });
//    e.preventDefault(); //STOP PAGE CHANGE
//});
