<?php
    require('../../scripts/init.php');

    require_role('student');

    $email = $_SESSION['user']['email'];

    $course = $_GET['corso'];

    $identifier = $_GET['insegnamento'];

    if (!isset($course) || !isset($identifier)) {
        redirect('/studenti/visualizza/insegnamenti.php');
    } 

    [ $rows, $_ ] = execute_query(
        'SELECT * FROM full_exam WHERE course=$1 AND identifier=$2 AND date > NOW() ORDER BY date',
            [ $course, $identifier ]);

    function get_student_count($course, $identifier, $date) {
        $query = 'SELECT COUNT(*) FROM career WHERE course=$1 AND identifier=$2 AND date=$3';

        [ $rows, $_ ] = execute_query($query, [ $course, $identifier, $date ]);

        return $rows[0]['count'];
    }

    function is_subscribed($exam) {
        global $email;

        $query = 'SELECT * FROM career WHERE student = $1 AND course = $2 AND identifier = $3 AND date = $4';

        [ $rows, $_ ] = execute_query($query, [ $email, $exam['course'], $exam['identifier'], $exam['date'] ]);

        return isset($rows[0]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];

        if ($action === 'subscribe') {
            $query = 'INSERT INTO career VALUES ($1, $2, $3, $4)';

            [ $_, $err ] = execute_query($query, [ $email, $course, $identifier, $_POST['date'] ]);

            if (isset($err)) {
                $error = parse_error_message($err, [
                    "check_career_mark" => "Hai già una valutazione sufficiente per questo insegnamento.",
                    "check_career_missing_requisites" => "Non rispetti i requisiti necessari per iscriverti a esami di questo insegnamento.",
                    "check_career_student_already_enrolled" => "Sei già iscritto a un esame di questo insegnamento."
                ]);
            }
        }

        if ($action === "unsubscribe") {
            $query = 'DELETE FROM career WHERE student=$1 AND course=$2 AND identifier=$3 AND date=$4 AND date > NOW()';

            execute_query($query, [ $email, $course, $identifier, $_POST['date'] ]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Studenti | Visualizza | Esami</title>

    <style>
        .is-actions {
            max-width: 150px;
        }
    </style>

    <?php if(isset($error)): ?>
        <script>
            window.addEventListener("load", (_) => {
                window.alert("<?= $error ?>");
            });
        </script>
    <?php endif ?>
</head>

<body data-theme="light">
    <?php require('../../components/studenti/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-calendar-days fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza esami</h1>
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
            
            <table class="table is-fullwidth is-hoverable">
                <thead>
                    <tr>
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
                            <!-- Empty table header. -->
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
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

                            <td class="is-actions">
                                <div class="columns">
                                    <div class="column">
                                        <?php if (is_subscribed($row)): ?>
                                            <div class="notification is-success has-text-centered py-1">
                                                Iscritto
                                            </div>
                                        <?php else: ?>
                                            <div class="notification is-light has-text-centered py-1">
                                                Non iscritto
                                            </div>  
                                        <?php endif ?>
                                    </div>

                                    <div class="column">
                                        <?php if (!is_subscribed($row)): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="action" value="subscribe">

                                                <input type="hidden" name="date" value="<?= $row['date'] ?>">

                                                <button class="button is-link is-small is-fullwidth" type="submit">
                                                    Iscriviti

                                                    <span class="icon is-large ml-1">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="action" value="unsubscribe">

                                                <input type="hidden" name="date" value="<?= $row['date'] ?>">

                                                <button class="button is-danger is-small is-fullwidth" type="submit">
                                                    Annulla iscrizione

                                                    <span class="icon is-large ml-1">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php endif ?>
                                    </div>
                                </div>
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
