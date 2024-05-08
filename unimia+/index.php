<?php
    require_once('scripts/init.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $roles = [
            '@studenti.unimi.it' => 'student',
            '@docenti.unimi.it' => 'professor',
            '@segreteria.unimi.it' => 'secretary'
        ];

        $email = $_POST['email'] . $_POST['role'];

        $password = $_POST['password'];

        $role = $roles[$_POST['role']];
        
        [ $rows, $_ ] = execute_query(
            "SELECT * FROM {$role} WHERE email=$1 AND password=MD5($2)", 
                [ $email, $password ]);

        if (isset($rows[0])) {
            $_SESSION['user'] = $rows[0];

            $_SESSION['role'] = $role;
            
            switch ($role) {
                case 'student':
                    redirect('/studenti/visualizza/insegnamenti.php');
                case 'professor':
                    redirect('/docenti/visualizza/corsi.php');
                case 'secretary':
                    redirect('/segreteria/visualizza/studenti.php');
            }
        } else {
            $error = 'Autenticazione non riuscita: e-mail o password errati.';
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require('components/head.php') ?>

    <title>Unimia+ | Login</title>
</head>

<body data-theme="light">
    <div class="columns is-centered mt-6">
        <div class="column is-one-third">
            <form class="box p-5" action="" method="post">
                <span class="icon-text">
                    <span class="icon is-large">
                        <i class="fa-solid fa-user-graduate fa-2xl"></i>
                    </span>

                    <h1 class="title py-2">Unimia+</h1>

                    <h2 class="subtitle is-5">Inserisci le tue credenziali per accedere ai servizi dell’Università degli Studi di Milano.</h2>
                </span>

                <div class="mt-5">
                    <label class="label">E-mail</label>

                    <div class="field has-addons has-addons-right">
                        <div class="control has-icons-left is-expanded">
                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-envelope"></i>
                            </span>

                            <input 
                                class="input" 
                                type="text" 
                                name="email" 
                                placeholder="nome.cognome"
                                value="<?= $_POST['email'] ?>"
                            >
                        </div>

                        <div class="control">
                            <div class="select">
                                <select name="role">
                                    <option <?= ($role == 'student') ? 'selected' : '' ?>>
                                        @studenti.unimi.it
                                    </option>

                                    <option <?= ($role == 'professor') ? 'selected' : '' ?>>
                                        @docenti.unimi.it
                                    </option>

                                    <option <?= ($role == 'secretary') ? 'selected' : '' ?>>
                                        @segreteria.unimi.it
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label">Password</label>

                    <div class="field">
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fa-solid fa-lock"></i>
                            </span>

                            <input class="input" type="password" name="password" placeholder="************">
                        </div>
                    </div>
                </div>

                <div class="field mt-5">
                    <div class="control">
                        <button class="button is-link is-fullwidth is-medium has-text-weight-semibold">
                            Log in
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="columns is-centered">
            <div class="column is-one-third">
                <div class="notification is-danger is-light">
                    <strong><?= $error ?></strong>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>
