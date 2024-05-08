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
                    Corsi di laurea 

                    <span class="icon ml-1">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </span>
                </a>

                <div class="navbar-dropdown">
                    <a class="navbar-item" href="/docenti/visualizza/corsi.php">
                        <span class="icon mr-1">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        Visualizza corsi di laurea
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
                    Accesso come: <?= $user['name'] . ' ' . $user['surname'] ?> (<b>docente</b>)
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
