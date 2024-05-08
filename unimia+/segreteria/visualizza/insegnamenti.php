<?php
    require('../../scripts/init.php');

    require_role('secretary');

    $course = $_GET['corso'];

    if (!isset($course)) {
        redirect('/segreteria/visualizza/corsi.php');
    } 

    [ $rows, $_ ] = execute_query(
        'SELECT * FROM full_teaching WHERE course=$1 ORDER BY year, semester, name', 
            [ $course ]);

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

    <title>Unimia+ | Segreteria | Visualizza | Insegnamenti</title>

    <script>
        function deleteTeaching(course, identifier) {
            if (window.confirm("Vuoi davvero cancellare questo insegnamento?")) {
                fetch(`/api/segreteria/insegnamenti/?course=${course}&identifier=${identifier}`, {
                    method: "DELETE"
                })
                    .then((r) => r.ok ? location.reload() : r.json())
                    .then((data) => window.alert(data["error"]));
            }
        }
    </script>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-three-quarters">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-book fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza insegnamenti</h1>
                    </span>
                </div>

                <div class="column has-text-right">
                    <a href="/segreteria/crea/insegnamenti.php?corso=<?= $course ?>" class="button is-link is-outlined">
                        <strong>Crea nuovo insegnamento</strong>

                        <span class="icon is-small">
                            <i class="fa-regular fa-plus fa-lg"></i>
                        </span>
                    </a>   

                    <a class="button ml-4 pl-5" onclick="history.back()">
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
                            <!-- Empty table header. -->
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td>
                                <a href="/segreteria/gestisci/corsi.php?corso=<?= $row['course'] ?>">
                                    <?= $row['course_class'] ?> | <?= $row['course'] ?> | <?= $row['course_title'] ?>

                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
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
                                <a href="/segreteria/gestisci/docenti.php?docente=<?= $row['professor'] ?>">
                                    <?= $row['professor_name'] ?> <?= $row['professor_surname'] ?>
                                    
                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
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
                                        <a href="/segreteria/gestisci/docenti.php?docente=<?= $correlation['email'] ?>">
                                            <?= $correlation['name'] ?> <?= $correlation['surname'] ?>
                                            
                                            <span class="icon is-small">
                                                <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                            </span>
                                        </a>
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
                                        <a href="/segreteria/gestisci/insegnamenti.php?corso=<?= $prerequisite['course'] ?>&insegnamento=<?= $prerequisite['identifier'] ?>">
                                            <?= $prerequisite['name'] ?>
                                            
                                            <span class="icon is-small">
                                                <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                            </span>
                                        </a>
                                    </div>
                                <?php endforeach ?>
                            </td>

                            <td>
                                <div class="columns">
                                    <div class="column pl-5">
                                        <a 
                                            class="button is-link is-small is-fullwidth"

                                            href="/segreteria/gestisci/insegnamenti.php?corso=<?= $row['course'] ?>&insegnamento=<?= $row['identifier'] ?>"
                                        >
                                            Aggiorna

                                            <span class="icon is-large ml-1">
                                                <i class="fa-solid fa-pen"></i>
                                            </span>
                                        </a>
                                    </div>

                                    <div class="column">
                                        <button 
                                            class="button is-danger is-small is-fullwidth" 
                                            
                                            onclick="deleteTeaching('<?= $row['course'] ?>', '<?= $row['identifier'] ?>')"
                                        >
                                            Cancella

                                            <span class="icon is-large ml-1">
                                                <i class="fa-solid fa-trash"></i>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>

                <?php if (count($rows) == 0): ?>
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
