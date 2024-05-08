<?php
    require('../../scripts/init.php');

    require_role('professor');

    $email = $_SESSION['user']['email'];

    $course = $_GET['corso'];

    $identifier = $_GET['insegnamento'];

    if (!isset($course) || !isset($identifier)) {
        redirect('/docenti/visualizza/corsi.php');
    } 

    if (!isset($_POST['tab']) || $_POST['tab'] == 0) {
        [ $rows, $_ ] = execute_query(
            'SELECT * FROM get_full_exams($1) WHERE course=$2 AND identifier=$3 AND date > NOW() ORDER BY date', 
                [ $email, $course, $identifier ]);
    }
    else if ($_POST['tab'] == 1) {
        [ $rows, $_ ] = execute_query(
            'SELECT * FROM get_full_exams($1) WHERE course=$2 AND identifier=$3 AND date <= NOW() ORDER BY date DESC', 
                [ $email, $course, $identifier ]);
    }

    function get_student_count($course, $identifier, $date) {
        $query = 'SELECT COUNT(*) FROM career WHERE course=$1 AND identifier=$2 AND date=$3';

        [ $rows, $_ ] = execute_query($query, [ $course, $identifier, $date ]);

        return $rows[0]['count'];
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Docenti | Visualizza | Esami</title>

    <script>
        function deleteExam(course, identifier, date) {
            if (window.confirm("Vuoi davvero cancellare questo esame?")) {
                fetch(`/api/docenti/esami/?course=${course}&identifier=${identifier}&date=${date}`, {
                    method: "DELETE"
                })
                    .then((r) => r.ok ? location.reload() : r.json())
                    .then((data) => window.alert(data["error"]));
            }
        }
    </script>
</head>

<body data-theme="light">
    <?php require('../../components/docenti/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-three-fours">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-calendar-days fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza esami</h1>
                    </span>
                </div>

                <div class="column has-text-right">
                    <a href="/docenti/crea/esami.php?corso=<?= $course ?>&insegnamento=<?= $identifier ?>" class="button is-link is-outlined">
                        <strong>Crea nuovo esame</strong>

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
            
            <div class="tabs">
                <ul>
                    <form name="switch-to-tab-0" action="" method="post">
                        <input type="hidden" name="tab" value="0">

                        <li class="<?= $_POST['tab'] == 0 ? 'is-active' : '' ?>">
                            <a onclick="document.forms['switch-to-tab-0'].submit()">
                                Esami
                            </a>
                        </li>
                    </form>

                    <form name="switch-to-tab-1" action="" method="post">
                        <input type="hidden" name="tab" value="1">

                        <li class="<?= $_POST['tab'] == 1 ? 'is-active' : '' ?>">
                            <a onclick="document.forms['switch-to-tab-1'].submit()">
                                Esami sostenuti
                            </a>
                        </li>
                    </form>
                </ul>
            </div>

            <table class="table is-fullwidth is-hoverable">
                <thead>
                    <tr>
                        <th>
                            Corso di laurea
                        </th>

                        <th>
                            Insegnamento
                        </th>

                        <th>
                            Data
                        </th>

                        <th>
                            Ora
                        </th>

                        <th>
                            Luogo
                        </th>

                        <th>
                            Iscritti
                        </th>

                        <th>
                            Iscrizioni
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
                                <?= $row['course_class'] ?> | <?= $row['course'] ?> | <?= $row['course_title'] ?>
                            </td>

                            <td>
                                <?= $row['teaching_name'] ?>
                            </td>

                            <td>
                                <?php 
                                    $date = date_create($row['date']);
                                
                                    echo date_format($date, 'd/m/Y');
                                ?>
                            </td>

                            <td>
                                <?= $row['time'] ?? '-' ?>
                            </td>

                            <td>
                                <?= $row['place'] ?? '-' ?>
                            </td>

                            <td>
                                <?= get_student_count($row['course'], $row['identifier'], $row['date']) ?>
                            </td>

                            <td>
                                <a href="/docenti/visualizza/iscrizioni.php?corso=<?= $row['course'] ?>&insegnamento=<?= $row['identifier'] ?>&esame=<?= $row['date'] ?>">
                                    Visualizza gli studenti iscritti a questo esame

                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <?php if ($_POST['tab'] == 0): ?>
                                    <div class="columns">
                                        <div class="column pl-5">
                                            <a 
                                                class="button is-link is-small is-fullwidth"

                                                href="/docenti/gestisci/esami.php?corso=<?= $row['course'] ?>&insegnamento=<?= $row['identifier'] ?>&esame=<?= $row['date'] ?>"
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
                                                
                                                onclick="deleteExam('<?= $row['course'] ?>', '<?= $row['identifier'] ?>', '<?= $row['date'] ?>')"
                                            >
                                                Cancella

                                                <span class="icon is-large ml-1">
                                                    <i class="fa-solid fa-trash"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif ?>
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
