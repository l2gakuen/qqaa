<?php

$customLayout = true;


if ((isset($_POST) && !empty($_POST))) {

    /***********************************
     * 
     *      POST
     * 
     ***********************************/
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

/*

- On commence par les voitures, pcq si on commence par les contrats, on verra pas les voitures "dispo"
- Compte le nombre de semaine/annee depuis une date, soit NOW ou $_GET['w'] as T
- Converti les dates depart-arrivee en numero de semaine, et verifie si T est dedans
- Converti date en numero de jours de la semaine T lundi=1, dimanche=7 as monday_of_week
- A partir des dates, determine le premier lundi de la semaine T et quel numero de jours, ex : (1), lundi, 22 janvier

- Determine le nb de jours entre les dates, arondi au supérieur, pcq qd SQL compare les dates, l'heure est comprise 
        ===== (2023-03-01 20:06 - 2023-03-03 14:50 == 1.78jours) ---> CAST(2.0jours) as INTEGER == 2jours.
- Determine donc un range de 1-7 pour utilisation des voitures
- Récupère les dates a cheval sur la semaine T=09 : voiures1 * 12jours = T09-T10

- TODO : Si semaine NB jours location depasse 7 jours, donc lundi-mardi (range 2) est faux,
         donc si range > 7 clamp autour de lundi-dimanche pour les semaines débordées.


Bordel de merde.
*/



$week_number = isset($_GET['w']) ? CLEAN(str_pad($_GET['w'], 2, '0', STR_PAD_LEFT)) : "strftime('%W', 'NOW')";
// $_GET_PLANNING = SELECT(
//     "voitures",
//     "voitures.*,
//     contrats.nom,
//     contrats.prenom,
//     contrats.tel_client,
//     contrats.date_depart,
//     contrats.date_arrivee,
//     CAST(round(julianday(date_arrivee) - julianday(date_depart), 0) AS INTEGER) as nb_jours,
//     CAST(strftime('%W', date_arrivee) AS INTEGER) - CAST(strftime('%W', date_depart) AS INTEGER) as nb_weeks,
//     strftime('%W', date_depart)  as week_depart,
//     strftime('%W', date_arrivee) as week_arrivee,
//     strftime('%s', contrats.date_depart) - strftime('%s', 'now') as diff_depart_seconds,
//     strftime('%s', contrats.date_arrivee) - strftime('%s', 'now') as diff_arrivee_seconds,
//     (CAST(strftime('%w', date_depart) AS INTEGER)  + 6) % 7 + 1 as num_day_depart,
//     (CAST(strftime('%w', date_arrivee) AS INTEGER) + 6) % 7 + 1 as num_day_arrivee,
//     DATE(date_depart, '-6 days', 'weekday 1') AS monday_of_week,
//     DATE(date_depart, '-1 days', 'weekday 6') AS sunday_of_week,
//     PRINTF('%02d', " . $week_number . ") as T,
//     strftime('%W', 'now') as W_NOW",
//     "LEFT JOIN contrats ON voitures.id = contrats.vehicule_id 
//     WHERE 1",
//     false
// );

$_GET_PLANNING = SELECT(
    "voitures",
    "voitures.*,
    contrats.contrat,
    contrats.nom,
    contrats.prenom,
    contrats.tel_client,
    contrats.date_depart,
    contrats.date_arrivee,
    CAST(round(julianday(date_arrivee) - julianday(date_depart), 0) AS INTEGER) as nb_days,
    (CAST(strftime('%W', date_arrivee) AS INTEGER) - CAST(strftime('%W', date_depart) AS INTEGER)) + 1 as nb_weeks,

       strftime('%W', date_depart) as week_depart,
       strftime('%W', date_arrivee) as week_arrivee,
       strftime('%w', date_depart) as day_depart,
       strftime('%w', date_arrivee) as day_arrivee, 
       strftime('%W', 'NOW') as week_now",
    "LEFT JOIN contrats 
    ON voitures.id = contrats.vehicule_id 
            AND (strftime('%W', contrats.date_depart) = '" . $week_number . "'
                OR strftime('%W', contrats.date_arrivee) = '" . $week_number . "'
                OR (strftime('%W', 'now') = '" . $week_number . "' AND contrats.date_depart IS NULL))
    WHERE voitures.status = 1",
    false
);

/* 
    Ok ok ok, donc la requete renvoi :
    voiture1 + contrat + dd + darrivee (N°1lundi-7dimanche)
    voiture1 + contrat + dd + darrivee (N°1lundi-7dimanche)
    voiture2 + contrat + dd + darrivee (N°1lundi-7dimanche)
    voiture3 + contrat + dd + darrivee (N°1lundi-7dimanche)

    on cherche un "group by" pour le html (1ligne = 1 voiture)

    voiture1 {
        contrat1, dd + darrivee
        contrat2, dd + darrivee
    } ...
    
    Donc en html =>  [voiture], [1,2 = contrat1], [5,6 = contrat2] (pas besoin de tracer 3-4) 

*/




if ($_GET_PLANNING) {
    $FULL_TABLE = sqlite_fetch_assoc_all($_GET_PLANNING);
    $_NEW_TABLE = (array) [];
    $UI_GRID = $template->find('[id=contrats]', 0);
    $UI_ROW  = $UI_GRID->find('.grid-row', 0);
    $UI_CELL = $UI_GRID->find('.cell', 0);
    foreach ($FULL_TABLE as $voiture) {
        $_NEW_TABLE[$voiture['immatriculation']][] = $voiture;
    }
    $STACK_VOITURE = (string) null;
    foreach ($_NEW_TABLE as $single_voiture) {
        $_VOITURE = (string) null;
        $count = (int) 1;
        foreach ($single_voiture as $DATA) {
            // $week = $_GET['w'];
            // $startWeek = $DATA['week_depart'];
            // $endWeek = $DATA['week_arrivee'];

            // // Check if the contract overlaps with the current week
            // if (($startWeek <= $week && $endWeek >= $week) || ($startWeek >= $week && $startWeek <= $week + 1)) {
            //     // If the contract starts before the current week, adjust the start week and add a class to indicate the start
            //     if ($startWeek < $week) {
            //         $startWeek = $week;
            //         $startClass = 'cal-start-' . ($week % 7 ?: 7) ;
            //     } else {
            //         $startClass = 'cal-start-' . ($DATA['day_depart'] ?: 7);
            //     }

            //     // If the contract ends after the current week, adjust the end week and add a class to indicate the end
            //     if ($endWeek > $week + 1) {
            //         $endWeek = $week + 1;
            //         $endClass = 'cal-end-' . (($week + 1) % 7 ?: 7);
            //     } else {
            //         $endClass = 'cal-end-' . ($DATA['day_arrivee'] ?: 7);
            //     }

            //     $UI_CELL->{'class'} = 'cell ' . $startClass . ' ' . $endClass;
            // } else {
            //     // If the contract does not overlap with the current week, set the class to indicate a non-booking week
            //     $UI_CELL->{'class'} = 'cell cal-start-'.$DATA['day_depart'].' cal-end-'.$DATA['day_arrivee'];
            // }


            // Assuming the query result is stored in $DATA

            $date_start = new DateTime($DATA['date_depart']);
            $date_end = new DateTime($DATA['date_arrivee']);

            // Calculate the number of weeks and days in the span
            $interval = $date_start->diff($date_end);
            $nb_days = $interval->format('%a') + 1;
            $nb_weeks = ceil($nb_days / 7);

            $week_start = $DATA['week_arrivee'] - 1;
            $week_end = $DATA['week_depart'] + 1;


            for ($i = 1; $i <= $nb_weeks; $i++) {
                $week1_start = new DateTime();
                $week1_end = new DateTime();

                $week1_start->setISODate(date('Y'), $week_start + $i - 1);
                $week1_start->modify('next monday');
                $week1_start->modify('+' . ($DATA['day_depart'] - 1) . ' day');
                if ($i == 1 && $date_start->format('N') != 1) {
                    $week1_start->modify('next monday');
                }

                if ($i == $nb_weeks && $DATA['day_arrivee'] != 7) {
                    $week1_end->setISODate(date('Y'), $week_end + $i - 1);
                    $week1_end->modify('next sunday');
                    $week1_end->modify('+' . ($DATA['day_arrivee'] - 7) . ' day');
                } else {
                    $week1_end->setISODate(date('Y'), $week_start + $i - 1);
                    $week1_end->modify('next sunday');
                }
                if ($i == $nb_weeks && $date_end->format('N') != 7) {
                    $week1_end->modify('previous sunday');
                }

                echo 'Week ' . $i . ': ' . $week1_start->format('Y-m-d') . ' to ' . $week1_end->format('Y-m-d') . '---' . $DATA['contrat'] . '<br>';
            }







            $UI_CELL->innertext = $DATA["nom"] . $DATA["prenom"] . $DATA["tel_client"] . '(' . $DATA["nb_days"] . 'jours)' . $DATA['contrat'];
            $_VOITURE .= $UI_CELL;
            $count++;
        }






        $UI_ROW->innertext = '<div class="cell cal-row-start-1 cal-row-end-' . $count . ' ">' . $DATA["marque"] . ' - ' . $DATA["immatriculation"] . '</div>' . $_VOITURE;
        $STACK_VOITURE .= $UI_ROW;
    }

    $UI_GRID->innertext = $STACK_VOITURE;
} else {
}
