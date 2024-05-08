<?php
    require('../../scripts/init.php');

    require_role('secretary');

    [ $rows, $_ ] = execute_query('SELECT * FROM full_student ORDER BY matriculation');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Visualizza | Studenti</title>

    <script>
        function deleteUser(email) {
            if (window.confirm("Vuoi davvero cancellare questo studente?")) {
                fetch(`/api/segreteria/studenti/?email=${email}`, {
                    method: "DELETE"
                })
                    .then((_) => location.reload());
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
                            <i class="fa-solid fa-users fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza studenti</h1>
                    </span>
                </div>

                <div class="column has-text-right">
                    <a href="/segreteria/crea/studenti.php" class="button is-link is-outlined">
                        <strong>Crea nuovo studente</strong>

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

                        <th>
                            Telefono
                        </th>

                        <th>
                            Indirizzo
                        </th>

                        <th>
                            Corso di laurea
                        </th>

                        <th>
                            Carriera
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
                                <?= $row['matriculation'] ?>
                            </td>

                            <td>
                                <?= $row['email'] ?>
                            </td>

                            <td>
                                <?= $row['name'] ?>
                            </td>

                            <td>
                                <?= $row['surname'] ?>
                            </td>

                            <td>
                                <?= $row['telephone'] ?>
                            </td>

                            <td>
                                <?= $row['address'] ?>
                            </td>

                            <td>
                                <a href="/segreteria/gestisci/corsi.php?corso=<?= $row['course'] ?>">
                                    <?= $row['course_class'] ?> | <?= $row['course'] ?> | <?= $row['course_title'] ?>

                                    <span class="icon is-small">
                                        <i class="fa-solid fa-arrow-up-right-from-square fa-sm"></i>
                                    </span>
                                </a>
                            </td>

                            <td>
                                <a href="/segreteria/visualizza/carriera.php?studente=<?= $row['email'] ?>">
                                    Visualizza la carriera di questo studente

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

                                            href="/segreteria/gestisci/studenti.php?studente=<?= $row['email'] ?>"
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
                                            
                                            onclick="deleteUser('<?= $row['email'] ?>')"
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
