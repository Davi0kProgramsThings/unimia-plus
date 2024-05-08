<?php
    require('../../scripts/init.php');

    require_role('secretary');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = "
            INSERT INTO course VALUES (
                $1, 
                $2, 
                $3, 
                $4, 
                $5
            )
        ";

        [ $result, $err ] = execute_query($query, [
            $_POST['code'],
            $_POST['class'],
            $_POST['title'], 
            $_POST['years'],  
            $_POST['language']
        ]);

        if (isset($result)) {
            redirect('/segreteria/visualizza/corsi.php');
        }

        if (isset($err)) {
            $error = parse_error_message($err, [
                "course_pkey" => [ "field" => "code", "message" => "Il codice è già in uso da un altro corso di laurea." ],
            ]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Crea | Corsi di laurea</title>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="columns is-centered p-5">
        <div class="column is-two-fifths">
            <form class="box" action="" method="post">
                <div class="columns">
                    <div class="column is-four-fifths">
                        <span class="icon-text">
                            <span class="icon is-large">
                                <i class="fa-solid fa-graduation-cap fa-2xl"></i>
                            </span>

                            <h1 class="title mt-2">Crea corso di laurea</h1>
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

                <div class="mt-3">
                    <div class="columns">
                        <div class="column field">
                            <label class="label">Codice <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="code" 
                                    placeholder="ABC" 
                                    value="<?= $_POST['code'] ?>"
                                    minlength="3"
                                    maxlength="3"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-tag"></i>
                                </span>
                            </p>

                            <?php if ($error['field'] === 'code'): ?>
                                <p class="help is-danger"><?= $error['message'] ?></p>
                            <?php endif ?>
                        </div>

                        <div class="column field">
                            <label class="label">Classe <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="class" 
                                    placeholder="L-01"
                                    value="<?= $_POST['class'] ?>"
                                    maxlength="5"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-tags"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Titolo <span class="has-text-danger">*</span></label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="title" 
                            placeholder="Matematica" 
                            value="<?= $_POST['title'] ?>" 
                            maxlength="100"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-diamond"></i>
                        </span>
                    </p>
                </div>

                <div class="mt-3">
                    <div class="columns">
                        <div class="column field">
                            <label class="label">Anni <span class="has-text-danger">*</span></label>
                            
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="years">
                                        <option <?= (3 == $_POST['years']) ? 'selected' : ''?>>
                                            3
                                        </option>

                                        <option <?= (2 == $_POST['years']) ? 'selected' : ''?>>
                                            2
                                        </option>
                                    </select>
                                </div>

                                <div class="icon is-small is-left">
                                    <i class="fa-solid fa-calendar"></i>
                                </div>
                            </div>
                        </div>

                        <div class="column field">
                            <label class="label">Lingua <span class="has-text-danger">*</span></label>
                            
                            <div class="control has-icons-left">
                                <div class="select is-fullwidth">
                                    <select name="language">
                                        <option value="italiano" <?= ('italiano' == $_POST['language']) ? 'selected' : '' ?>>
                                            Italiano
                                        </option>

                                        <option value="inglese" <?= ('inglese' == $_POST['language']) ? 'selected' : '' ?>>
                                            Inglese
                                        </option>

                                        <option value="francese" <?= ('francese' == $_POST['language']) ? 'selected' : '' ?>>
                                            Francese
                                        </option>

                                        <option value="spagnolo" <?= ('spagnolo' == $_POST['language']) ? 'selected' : '' ?>>
                                            Spagnolo
                                        </option>

                                        <option value="tedesco" <?= ('tedesco' == $_POST['language']) ? 'selected' : '' ?>>
                                            Tedesco
                                        </option>
                                    </select>
                                </div>

                                <div class="icon is-small is-left">
                                    <i class="fa-solid fa-language"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field mt-3">
                    <p class="control">
                        <button class="button is-link is-fullwidth" type="submit">
                            Crea corso di laurea +
                        </button>
                    </p>
                </div>
            </form>

            <?php if ($error === false): ?>
                <div class="notification is-danger is-light mt-3">
                    <strong>Errore non previsto: riprovare più tardi...</strong>
                </div>
            <?php endif ?>
        </div>
    </div>
</body>

</html>