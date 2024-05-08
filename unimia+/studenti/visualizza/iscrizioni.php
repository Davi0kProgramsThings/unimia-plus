<?php
    require('../../scripts/init.php');

    require_role('student');

    $email = $_SESSION['user']['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = 'DELETE FROM career WHERE student=$1 AND course=$2 AND identifier=$3 AND date=$4 AND date > NOW()';

        execute_query($query, [ $email, $_POST['course'], $_POST['identifier'], $_POST['date'] ]);
    }

    $query = 'SELECT * FROM get_student_enrollments($1) ORDER BY date';

    [ $rows, $_ ] = execute_query($query, [ $email ]);

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

    <title>Unimia+ | Studenti | Visualizza | Iscrizioni</title>

    <style>
        .is-actions {
            max-width: 150px;
        }
    </style>
</head>

<body data-theme="light">
    <?php require('../../components/studenti/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-list fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza iscrizioni</h1>
                    </span>
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
                                        <div class="notification is-success has-text-centered py-1">
                                            Iscritto
                                        </div>
                                    </div>

                                    <div class="column">
                                        <form action="" method="post">
                                            <input type="hidden" name="course" value="<?= $row['course'] ?>">
                                            <input type="hidden" name="identifier" value="<?= $row['identifier'] ?>">
                                            <input type="hidden" name="date" value="<?= $row['date'] ?>">

                                            <button class="button is-danger is-small is-fullwidth" type="submit">
                                                Annulla iscrizione

                                                <span class="icon is-large ml-1">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </span>
                                            </button>
                                        </form>
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
