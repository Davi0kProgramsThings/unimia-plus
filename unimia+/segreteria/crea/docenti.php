<?php
    require('../../scripts/init.php');

    require_role('secretary');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = "
            INSERT INTO professor VALUES (
                $1, 
                MD5($2), 
                $3, 
                $4, 
                $5, 
                $6, 
                $7, 
                $8, 
                $9
            )
        ";

        [ $result, $err ] = execute_query($query, [
            "{$_POST['email']}@docenti.unimi.it",
            $_POST['password'],
            $_POST['name'], 
            $_POST['surname'],  
            $_POST['website'], 
            $_POST['workplace'], 
            $_POST['reception'],
            !empty($_POST['telephone']) ? $_POST['telephone'] : null,
            !empty($_POST['address']) ? $_POST['address'] : null
        ]);

        if (isset($result)) {
            redirect('/segreteria/visualizza/docenti.php');
        }

        if (isset($err)) {
            $error = parse_error_message($err, [
                "professor_pkey" => [ "field" => "email", "message" => "L'indirizzo e-mail è già in uso da un altro docente." ],
                "email_check" => [ "field" => "email", "message" => "Inserisci un indirizzo e-mail valido." ],
                "website_check" => [ "field" => "website", "message" => "Inserisci un sito web valido." ],
                "telephone_check" => [ "field" => "telephone", "message" => "Inserisci un numero di telefono valido." ]
            ]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Crea | Docenti</title>
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
                                <i class="fa-solid fa-user fa-2xl"></i>
                            </span>

                            <h1 class="title mt-2">Crea docente</h1>
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
                    <label class="label">E-mail <span class="has-text-danger">*</span></label>

                    <div class="field has-addons has-addons-right mb-1">
                        <p class="control has-icons-left is-expanded">
                            <input class="input" type="text" name="email" 
                                placeholder="john.doe" 
                                value="<?= $_POST['email'] ?>"
                                maxlength="254"
                                required/>
                        
                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                        </p>

                        <p class="control">
                            <a class="button is-static">
                                @docenti.unimi.it
                            </a>
                        </p>
                    </div>

                    <?php if ($error['field'] === 'email'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="mt-3">
                    <div class="columns">
                        <div class="column field">
                            <label class="label">Nome <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="name" 
                                    placeholder="John" 
                                    value="<?= $_POST['name'] ?>"
                                    maxlength="30"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-user-tag"></i>
                                </span>
                            </p>
                        </div>

                        <div class="column field">
                            <label class="label">Cognome <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="surname" 
                                    placeholder="Doe"
                                    value="<?= $_POST['surname'] ?>"
                                    maxlength="30"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-user-tag"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Password <span class="has-text-danger">*</span></label>

                    <p class="control has-icons-left">
                        <input class="input" type="password" name="password" 
                            placeholder="************" 
                            value="<?= $_POST['password'] ?>" 
                            minlength="8"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-key"></i>
                        </span>
                    </p>
                </div>

                <div class="field mt-3">
                    <label class="label">Sito web <span class="has-text-danger">*</span></label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="website" 
                            placeholder="https://doe.di.unimi.it" 
                            value="<?= $_POST['website'] ?>"
                            maxlength="2048"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-globe"></i>
                        </span>
                    </p>

                    <?php if ($error['field'] === 'website'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="mt-3">
                    <div class="columns">
                        <div class="column field">
                            <label class="label">Sede di lavoro <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="workplace" 
                                    placeholder="Via Giovanni Celoria, 18" 
                                    value="<?= $_POST['workplace'] ?>"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>
                            </p>
                        </div>

                        <div class="column field">
                            <label class="label">Luogo di ricevimento <span class="has-text-danger">*</span></label>

                            <p class="control has-icons-left">
                                <input class="input" type="text" name="reception" 
                                    placeholder="Dipartimento di informatica" 
                                    value="<?= $_POST['reception'] ?>"
                                    required/>

                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-circle-info"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Telefono</label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="telephone" 
                            placeholder="1234567890" 
                            value="<?= $_POST['telephone'] ?>"
                            maxlength="10"/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                    </p>

                    <?php if ($error['field'] === 'telephone'): ?>
                        <p class="help is-danger"><?= $error['message'] ?></p>
                    <?php endif ?>
                </div>

                <div class="field mt-3">
                    <label class="label">Indirizzo</label>

                    <p class="control has-icons-left">
                        <input class="input" type="text" name="address" 
                            placeholder="Via Celoria 18 20131 Milano MI" 
                            value="<?= $_POST['address'] ?>"/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                    </p>
                </div>

                <div class="field mt-5">
                    <p class="control">
                        <button class="button is-link is-fullwidth" type="submit">
                            Crea docente +
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