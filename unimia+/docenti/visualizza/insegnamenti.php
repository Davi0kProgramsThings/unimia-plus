<?php
    require('../../scripts/init.php');

    require_role('professor');

    $email = $_SESSION['user']['email'];

    $course = $_GET['corso'];

    if (!isset($course)) {
        redirect('/docenti/visualizza/corsi.php');
    }

    [ $rows, $_ ] = execute_query(
        'SELECT * FROM get_full_teachings($1) WHERE course=$2 ORDER BY year, semester',
            [ $email, $course ]);

    function get_correlations($course, $identifier) {
        $query = 'SELECT * FROM get_teaching_correlations($1, $2)';

        [ $rows, $_ ] = execute_query($query, [ $course, $identifier ]);

        return $rows;
    }

    function get_prerequisites($course, $identifier) {
        $query = 'SELECT * FROM get_teaching_prerequisites($1, $2)';

        [ $rows, $_ ] = execute_query($query, [ $course, $identifier ]);

        return $rows;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Docenti | Visualizza | Insegnamenti</title>
</head>

<body data-theme="light">
    <?php require('../../components/docenti/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-book fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza i tuoi insegnamenti</h1>
                    </span>
                </div>

                <div class="column has-text-right">
                    <a class="button pl-5" onclick="history.back()">
                        <span class="icon-text">
                            <span class="icon">
                                <i class="fa-solid fa-circle-left fa-sm mr-3"></i>
                            </span>

                            Indietro
                        </span>
                    </a>
                </div>
            </div>

            <?php require('../../components/filter.php') ?>
            
            <table class="table is-fullwidth is-hoverable is-size-6-5">
                <thead>
                    <tr>
                        <th>
                            Corso di laurea
                        </th>

                        <th>
                            Identificatore
                        </th>

                        <th>
                            Nome
                        </th>

                        <th>
                            Descrizione
                        </th>

                        <th>
                            Anno
                        </th>

                        <th>
                            Semestre
                        </th>

                        <th>
                            Crediti
                        </th>

                        <th>
                            Professore
                        </th>

                        <th>
                            Altri professori
                        </th>

                        <th>
                            Propedeuticità
                        </th>

                        <th>
                            Esami
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td>
                                <?= $row['course_class'] ?> | <?= $row['course'] ?> | <?= $row['course_title'] ?>
                            </td>

                            <td>
                                <?= $row['identifier'] ?>
                            </td>

                            <td>
                                <?= $row['name'] ?>
                            </td>

                            <td class="is-textarea">
                                <?= $row['description'] ?>
                            </td>

                            <td>
                                <?= $row['year'] ?>
                            </td>

                            <td>
                                <?= $row['semester'] ?>
                            </td>

                            <td>
                                <?= $row['credits'] ?>
                            </td>

                            <td>
                                <?= $row['professor_name'] ?> <?= $row['professor_surname'] ?>
                            </td>

                            <td>
                                <?php
                                    $correlations = get_correlations($row['course'], $row['identifier']);
                                ?>

                                <?php if (count($correlations) === 0): ?>
                                    <div>
                                        -
                                    </div>
                                <?php endif ?>

                                <?php foreach ($correlations as $correlation): ?>
                                    <div>
                                        <?= $correlation['name'] ?> <?= $correlation['surname'] ?>
                                    </div>
                                <?php endforeach ?>
                            </td>

                            <td>
                                <?php
                                    $prerequisites = get_prerequisites($row['course'], $row['identifier']);
                                ?>

                                <?php if (count($prerequisites) === 0): ?>
                                    <div>
                                        -
                                    </div>
                                <?php endif ?>

                                <?php foreach ($prerequisites as $prerequisite): ?>
                                    <div>
                                        <?= $prerequisite['name'] ?>
                                    </div>
                                <?php endforeach ?>
                            </td>

                            <td>
                                <a href="/docenti/visualizza/esami.php?corso=<?= $row['course'] ?>&insegnamento=<?= $row['identifier'] ?>">
                                    Gestisci gli esami di questo insegnamento

                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>

                <?php if (count($rows) === 0): ?>
                    <tfoot>
                        <tr>
                            <td class="has-text-centered invalid" colspan="100">
                                <p class="mt-2">Nessun risultato trovato</p>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif ?>
            </table>
        </div>
    </div>
</body>

</html>
