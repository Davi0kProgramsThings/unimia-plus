<?php
    require('../../scripts/init.php');

    require_role('professor');

    $email = $_SESSION['user']['email'];

    [ $rows, $_ ] = execute_query(
        'SELECT * FROM get_courses($1) ORDER BY title', 
            [ $email ]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Docenti | Visualizza | Corsi di laurea</title>
</head>

<body data-theme="light">
    <?php require('../../components/docenti/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-graduation-cap fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza i tuoi corsi di laurea</h1>
                    </span>
                </div>
            </div>

            <?php require('../../components/filter.php') ?>
            
            <table class="table is-fullwidth is-hoverable">
                <thead>
                    <tr>
                        <th>
                            Codice
                        </th>

                        <th>
                            Classe
                        </th>

                        <th>
                            Titolo
                        </th>

                        <th>
                            Anni
                        </th>

                        <th>
                            Lingua
                        </th>

                        <th>
                            I tuoi insegnamenti
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td>
                                <?= $row['code'] ?>
                            </td>

                            <td>
                                <?= $row['class'] ?>
                            </td>

                            <td>
                                <?= $row['title'] ?>
                            </td>

                            <td>
                                <?= $row['years'] ?>
                            </td>

                            <td>
                                <?= $row['language'] ?>
                            </td>

                            <td>
                                <a href="/docenti/visualizza/insegnamenti.php?corso=<?= $row['code'] ?>">
                                    Visualizza i tuoi insegnamenti per questo corso di laurea

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
