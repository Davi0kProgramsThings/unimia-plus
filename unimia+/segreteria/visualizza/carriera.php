<?php
    require('../../scripts/init.php');

    require_role('secretary');

    $email = $_GET['studente'];

    if (!isset($email)) {
        redirect('/segreteria/visualizza/studenti.php');
    }

    if (!isset($_POST['tab']) || $_POST['tab'] == 0) {
        [ $rows, $_ ] = execute_query(
            'SELECT * FROM get_full_career($1) ORDER BY date DESC', 
                [ $email ]);
    }
    else if ($_POST['tab'] == 1) {
        [ $rows, $_ ] = execute_query(
            "SELECT * FROM get_full_career($1) WHERE mark_status = 'A' ORDER BY date DESC", 
                [ $email ]);
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Visualizza | Carriera</title>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="container is-fluid p-5">
        <div class="box">
            <div class="columns mb-0">
                <div class="column is-four-fifths">
                    <span class="icon-text mb-4">
                        <span class="icon is-large mr-1">
                            <i class="fa-solid fa-info fa-2xl"></i>
                        </span>

                        <h1 class="title mt-2">Visualizza carriera</h1>
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

            <div class="tabs">
                <ul>
                    <form name="switch-to-tab-0" action="" method="post">
                        <input type="hidden" name="tab" value="0">

                        <li class="<?= $_POST['tab'] == 0 ? 'is-active' : '' ?>">
                            <a onclick="document.forms['switch-to-tab-0'].submit()">
                                Carriera
                            </a>
                        </li>
                    </form>

                    <form name="switch-to-tab-1" action="" method="post">
                        <input type="hidden" name="tab" value="1">

                        <li class="<?= $_POST['tab'] == 1 ? 'is-active' : '' ?>">
                            <a onclick="document.forms['switch-to-tab-1'].submit()">
                                Carriera valida
                            </a>
                        </li>
                    </form>
                </ul>
            </div>

            <table class="table is-fullwidth is-hoverable">
                <thead>
                    <tr>
                        <th>
                            Insegnamento e codice
                        </th>

                        <th>
                            Data di sostenimento
                        </th>

                        <th>
                            Crediti
                        </th>

                        <th>
                            Professore
                        </th>

                        <th>
                            Esito
                        </th>

                        <th>
                            Data di pubblicazione
                        </th>

                        <th>
                            Stato
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($rows as $row): ?>
                        <tr>
                            <td>
                                <?= $row['teaching_name'] ?> (<?= $row['course'] ?> <?= $row['identifier'] ?>)
                            </td>

                            <td>
                                <?php 
                                    $_date = date_create($row['date']);
                                                                
                                    echo date_format($_date, 'd/m/Y');
                                ?>
                            </td>

                            <td>
                                <?= $row['teaching_credits'] ?>
                            </td>

                            <td>
                                <?= $row['professor_name'] ?> <?= $row['professor_surname'] ?>
                            </td>

                            <td>
                                <?= $row['mark_result'] ?? '-' ?>
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
