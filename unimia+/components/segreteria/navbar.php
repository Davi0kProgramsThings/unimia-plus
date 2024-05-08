<?php
    $user = $_SESSION['user'];
?>

<script>
    function logout() {
        fetch("/api/logout/")
            .then((_) => location.href = "/");
    }
</script>

<nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item">
            <span class="icon is-large">
                <i class="fa-solid fa-user-graduate fa-2xl"></i>
            </span>

            <h1 class="title">Unimia+</h1>
        </a>
    </div>

    <div class="navbar-menu">
        <div class="navbar-start">
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Studenti 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/segreteria/visualizza/studenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza studenti
                    </a>

                    <a class="navbar-item" href="/segreteria/visualizza/storico/studenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-trash-can"></i>
                        </span>

                        Visualizza storico studenti
                    </a>

                    <a class="navbar-item" href="/segreteria/crea/studenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        Nuovo studente
                    </a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Docenti 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-user"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/segreteria/visualizza/docenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza docenti
                    </a>

                    <a class="navbar-item" href="/segreteria/crea/docenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        Nuovo docente
                    </a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Corsi di laurea 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/segreteria/visualizza/corsi.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza corsi di laurea
                    </a>

                    <a class="navbar-item" href="/segreteria/crea/corsi.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        Nuovo corso di laurea
                    </a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Insegnamenti 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-book"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/segreteria/crea/insegnamenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        Nuovo insegnamento
                    </a>
                </div>
            </div>
        </div>

        <div class="navbar-end">
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Impostazioni 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-gear fa-md"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <button class="navbar-item js-modal-trigger" data-target="modal-js-password">
                        Modifica la tua password
                    </button>
                </div>
            </div>

            <div class="navbar-item">
                <div class="notification is-light py-2">
                    Accesso come: <?= $user['name'] . ' ' . $user['surname'] ?> (<b>segreteria</b>)
                </div>
            </div>

            <div class="navbar-item">
                <div class="buttons">
                    <button class="button is-light" onclick="logout()">
                        Log out

                        <span class="icon ml-1">
                            <i class="fa-solid fa-right-from-bracket fa-md"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<?php require($_SERVER['DOCUMENT_ROOT'] . '/components/modals/password.php') ?>
