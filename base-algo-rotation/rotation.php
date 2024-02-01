<?php

/** Algorithme de rotation de groupes */

/**
 * Gestion de l'activité
 */
$nb_pax = 20; // Nombre de participants

/** Paramètres temps(minutes)*/
$total_time = 100; // Temps total de l'activité
$rotation_time = 5; // Temps par rotation (changement de stand)
$stand_time = 10; // Temps passé sur un stand

// Temps d'un tour
$turn_time = $stand_time + $rotation_time;

// Calcul du nombre de tours
$total_turns = $total_time / $turn_time;

// Nombre de stands
$total_stands = 6;

// Nombre d'équipes
$total_teams = 6;

/**
 * Gestion des stands
 */
// Générer le nom des stands
$stand_name = array();
for ($i = 1; $i <= $total_stands; $i++) {
    $stand_names[] = array(
        "id" => $i,
        "name" => "Stand " . $i
    );
}

/**
 * Gestion des équipes
 */
// Générer le nom des équipes
$team_name = array();
for ($i = 1; $i <= $total_teams; $i++) {
    $team_names[] = array(
        "id" => $i,
        "name" => "Équipe " . $i
    );
}

// Boucle qui parcourt chaque tour jusqu'au nombre total de tours
for ($turn_number = 1; $turn_number <= $total_turns; $turn_number++) {

    echo "<h2 style=\"font-weight: bold\"> Tour $turn_number : </h2>";

    // Boucle pour parcourir les équipes
    foreach ($team_names as $team) {

        // Calcul de l'index du stand pour le tour actuel
        $stand_index = ($turn_number + $team['id'] - 2) % $total_stands;

        // Affichage des informations du tour
        echo $team['name'] . " va au " . $stand_names[$stand_index]['name'] . "<br>";
    }
}
