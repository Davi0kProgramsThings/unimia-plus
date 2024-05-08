<?php
    require('../../scripts/init.php');

    require_role('secretary');

    $email = $_GET['studente'];

    $query = 'SELECT * FROM student WHERE email=$1';

    [ $rows, $_ ] = execute_query($query, [ $email ]);

    $student = $rows[0];

    if (!isset($student)) {
        redirect('/segreteria/visualizza/studenti.php');
    }

    [ $courses, $_ ] = execute_query('SELECT * FROM course ORDER BY years DESC, title');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $query = "
            UPDATE student 
            SET email=$1,
                name=$2,
                surname=$3,
                course=$4, 
                telephone=$5, 
                address=$6
            WHERE email=$7
        ";

        [ $result, $err ] = execute_query($query, [
            "{$_POST['email']}@studenti.unimi.it",
            $_POST['name'],
            $_POST['surname'],
            $_POST['course'],
            !empty($_POST['telephone']) ? $_POST['telephone'] : null,
            !empty($_POST['address']) ? $_POST['address'] : null,

            $student['email']
        ]);

        if (isset($result)) {
            redirect('/segreteria/visualizza/studenti.php');
        }

        if (isset($err)) {
            $error = parse_error_message($err, [
                "student_pkey" => [ "field" => "email", "message" => "L'indirizzo e-mail è già in uso da un altro studente." ],
                "email_check" => [ "field" => "email", "message" => "Inserisci un indirizzo e-mail valido." ],
                "telephone_check" => [ "field" => "telephone", "message" => "Inserisci un numero di telefono valido." ]
            ]);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('../../components/head.php') ?>

    <title>Unimia+ | Segreteria | Gestisci | Studenti</title>

    <script>
        function updateStudentPassword() {
            const password = (document.getElementsByName("password")[0]).value;

            fetch(`/api/segreteria/studenti/password/?email=<?= $email ?>`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `password=${password}`
            })
                .then((_) => {
                    document.forms["change-password"].reset();

                    window.alert("La password è stata aggiornata con successo!");
                });
        }
    </script>
</head>

<body data-theme="light">
    <?php require('../../components/segreteria/navbar.php') ?>

    <div class="columns is-centered p-5">
        <div class="column is-two-fifths">
            <div class="box">
                <form action="" method="post">
                    <div class="columns">
                        <div class="column is-four-fifths">
                            <span class="icon-text">
                                <span class="icon is-large">
                                    <i class="fa-solid fa-user fa-2xl"></i>
                                </span>

                                <h1 class="title mt-2">Gestisci studente</h1>
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

                    <div class="field mt-3">
                        <label class="label">Matricola</label>

                        <p class="control has-icons-left">
                            <input class="input" type="text"
                                value="<?= $student['matriculation'] ?>"
                                disabled
                                readonly/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-tag"></i>
                            </span>
                        </p>
                    </div>

                    <div class="mt-3">
                        <label class="label">E-mail <span class="has-text-danger">*</span></label>

                        <div class="field has-addons has-addons-right mb-1">
                            <p class="control has-icons-left is-expanded">
                                <input class="input" type="text" name="email" 
                                    placeholder="john.doe" 
                                    value="<?= $_POST['email'] ?? explode('@', $student['email'])[0] ?>"
                                    maxlength="254"
                                    required/>
                            
                                <span class="icon is-small is-left">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                            </p>

                            <p class="control">
                                <a class="button is-static">
                                    @studenti.unimi.it
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
                                        value="<?= $_POST['name'] ?? $student['name'] ?>"
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
                                        value="<?= $_POST['surname'] ?? $student['surname'] ?>"
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
                        <label class="label">Corso di laurea <span class="has-text-danger">*</span></label>
                        
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="course">
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['code'] ?>" <?= ($course['code'] == ($_POST['course'] ?? $student['course'])) ? 'selected' : '' ?>>
                                            <?= $course['class'] ?> | <?= $course['code'] ?> | <?= $course['title'] ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="icon is-small is-left">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        </div>
                    </div>

                    <div class="field mt-3">
                        <label class="label">Telefono</label>

                        <p class="control has-icons-left">
                            <input class="input" type="text" name="telephone" 
                                placeholder="1234567890" 
                                value="<?= $_POST['telephone'] ?? $student['telephone'] ?>"
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
                                value="<?= $_POST['address'] ?? $student['address'] ?>"/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                        </p>
                    </div>

                    <div class="field mt-5">
                        <p class="control">
                            <button class="button is-link is-fullwidth" type="submit">
                                <span class="icon-text">
                                    Aggiorna studente

                                    <span class="icon">
                                        <i class="fa-solid fa-pen ml-3"></i>
                                    </span>
                                </span>
                            </button>
                        </p>
                    </div>
                </form>

                <hr />

                <form name="change-password" onsubmit="updateStudentPassword(); return false">
                    <div class="field mt-3">
                        <label class="label">Password</label>

                        <p class="control has-icons-left">
                            <input class="input" type="password" name="password" 
                                placeholder="************" 
                                minlength="8"
                                required/>

                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-key"></i>
                            </span>
                        </p>
                    </div>

                    <div class="field mt-5">
                        <p class="control">
                            <button class="button is-warning is-fullwidth" type="submit">
                                <span class="icon-text">
                                    Aggiorna password

                                    <span class="icon">
                                        <i class="fa-solid fa-lock ml-3"></i>
                                    </span>
                                </span>
                            </button>
                        </p>
                    </div>
                </form>
            </div>

            <?php if ($error === false): ?>
                <div class="notification is-danger is-light mt-3">
                    <strong>Errore non previsto: riprovare più tardi...</strong>
                </div>
            <?php endif ?>
        </div>
    </div>
</body>

</html>
