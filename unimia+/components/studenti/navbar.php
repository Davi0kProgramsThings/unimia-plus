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
                    Insegnamenti

                    <span class="icon ml-1">
                        <i class="fa-solid fa-book"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/studenti/visualizza/insegnamenti.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza insegnamenti
                    </a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Iscrizioni

                    <span class="icon ml-1">
                        <i class="fa-solid fa-list"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/studenti/visualizza/iscrizioni.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza iscrizioni
                    </a>
                </div>
            </div>

            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link">
                    Carriera

                    <span class="icon ml-1">
                        <i class="fa-solid fa-info"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/studenti/visualizza/carriera.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza carriera
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
                    Accesso come: <?= $user['name'] . ' ' . $user['surname'] ?> (<b>studente</b>)
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
