<?php
    require('../../scripts/init.php');

    require_role('secretary');

    [ $rows, $_ ] = execute_query('SELECT * FROM course ORDER BY years DESC, title');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Visualizza | Corsi di laurea</title>

    <script>
        function deleteCourse(code) {
            if (window.confirm("Vuoi davvero cancellare questo corso di laurea?")) {
                fetch(`/api/segreteria/corsi/?code=${code}`, {
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
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-graduation-cap fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza corsi di laurea</h1>
                    </span>
                </div>

                <div class="column has-text-right">
                    <a href="/segreteria/crea/corsi.php" class="button is-link is-outlined">
                        <strong>Crea nuovo corso di laurea</strong>

                        <span class="icon is-small">
                            <i class="fa-regular fa-plus fa-lg"></i>
                        </span>
                    </a>    
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
                            Insegnamenti
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
                                <a href="/segreteria/visualizza/insegnamenti.php?corso=<?= $row['code'] ?>">
                                    Gestisci gli insegnamenti di questo corso

                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <div class="columns">
                                    <div class="column pl-5">
                                        <a 
                                            class="button is-link is-small is-fullwidth"

                                            href="/segreteria/gestisci/corsi.php?corso=<?= $row['code'] ?>"
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
                                            
                                            onclick="deleteCourse('<?= $row['code'] ?>')"
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
