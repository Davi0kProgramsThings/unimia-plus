<?php
    require('../../scripts/init.php');

    require_role('professor');

    $email = $_SESSION['user']['email'];

    $course = $_GET['corso'];

    $identifier = $_GET['insegnamento'];

    $date = $_GET['esame'];

    if (!isset($course) || !isset($identifier) || !isset($date)) {
        redirect('/docenti/visualizza/corsi.php');
    } 

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = "
            UPDATE career 
            SET mark_result=$1, 
                mark_publication=NOW()
            WHERE student=$2 AND 
                  course=$3 AND 
                  identifier=$4 AND 
                  date=$5 AND 
                  mark_publication IS NULL AND
                  mark_result IS NULL AND
                  $6 IN (
                      SELECT professor FROM teaching
                        WHERE course=$3 AND 
                              identifier=$4
                  )
        ";

        execute_query($query, [
            $_POST['mark_result'],

            $_POST['student'],
            $course,
            $identifier,
            $date,
            $email
        ]);
    }

    $query = 'SELECT * FROM get_exam_students($1, $2, $3) WHERE teaching_professor=$4 ORDER BY student_surname';

    [ $rows, $_ ] = execute_query($query, [ $course, $identifier, $date,  $email ]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Docenti | Visualizza | Iscrizioni</title>
</head>

<body data-theme="light">
    <?php require('../../components/docenti/navbar.php') ?>

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

                <div class="column has-text-right">
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
            
            <table class="table is-fullwidth is-hoverable">
                <thead>
                    <tr>
                        <th>
                            Matricola
                        </th>

                        <th>
                            E-mail
                        </th>

                        <th>
                            Nome
                        </th>

                        <th>
                            Cognome
                        </th>

                        <?php if ($date <= date("Y-m-d")): ?>
                            <th>
                                Esito
                            </th>

                            <th>
                                Data di pubblicazione
                            </th>

                            <th>
                                Stato
                            </th>
                        <?php endif ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td>
                                <?= $row['student_matriculation'] ?>
                            </td>

                            <td>
                                <?= $row['student'] ?>
                            </td>

                            <td>
                                <?= $row['student_name'] ?>
                            </td>

                            <td>
                                <?= $row['student_surname'] ?>
                            </td>

                            <?php if ($date <= date("Y-m-d")): ?>
                                <td>
                                    <?php if ($row['mark_result'] == null): ?>
                                        <form action="" method="post">
                                            <input type="hidden" name="student" value="<?= $row['student'] ?>">

                                            <div class="field has-addons has-addons-right">
                                                <div class="control is-expanded">
                                                    <div class="select is-small is-fullwidth">
                                                        <select name="mark_result">
                                                            <?php for ($i = 0; $i <= 31; $i++): ?>
                                                                <option>
                                                                    <?= $i ?>
                                                                </option>
                                                            <?php endfor ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="control">
                                                    <button class="button is-small" type="submit">
                                                        Invia
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <?= $row['mark_result'] ?>
                                    <?php endif ?>
                                </td>

                                <td>
                                    <?php if ($row['mark_publication'] == null): ?>
                                        -
                                    <?php else: ?>
                                        <?php 
                                            $_date = date_create($row['mark_publication']);
                                                                
                                            echo date_format($_date, 'd/m/Y H:i');
                                        ?>
                                    <?php endif ?>
                                </td>

                                <td>
                                    <?php if ($row['mark_status'] == null): ?>
                                        <?php if ($row['mark_result'] == null): ?>
                                            <div class="notification has-text-centered py-1">
                                                In attesa di valutazione
                                            </div>
                                        <?php elseif ($row['mark_result'] >= 18): ?>
                                            <div class="notification has-text-centered py-1">
                                                In attesa
                                            </div>
                                        <?php else: ?>
                                            <div class="notification has-text-centered py-1">
                                                Accettazione non richiesta
                                            </div>
                                        <?php endif ?>
                                    <?php elseif ($row['mark_status'] === 'A'): ?>
                                        <div class="notification is-success has-text-centered py-1">
                                            Accettato
                                        </div>
                                    <?php elseif ($row['mark_status'] === 'R'): ?>
                                        <div class="notification is-danger has-text-centered py-1">
                                            Rifiutato
                                        </div>
                                    <?php endif ?>
                                </td>
                            <?php endif ?>
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
