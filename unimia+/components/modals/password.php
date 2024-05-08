<script>
    document.addEventListener("DOMContentLoaded", () => {
        function openModal($el) {
            $el.classList.add("is-active");
        }

        function closeModal($el) {
            $el.classList.remove("is-active");
        }

        function closeAllModals() {
            (document.querySelectorAll(".modal") || []).forEach(($modal) => {
                closeModal($modal);
            });
        }

        (document.querySelectorAll(".js-modal-trigger") || []).forEach(($trigger) => {
            const modal = $trigger.dataset.target;
            const $target = document.getElementById(modal);

            $trigger.addEventListener("click", () => {
                openModal($target);
            });
        });

        (document.querySelectorAll(".modal-background, .modal-close, .modal-card-head .delete") || []).forEach(($close) => {
            const $target = $close.closest(".modal");

            $close.addEventListener("click", () => {
                closeModal($target);
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeAllModals();
            }
        });
    });

    function updateUserPassword() {
        const oldPassword = (document.getElementsByName("old_password")[0]).value;

        const password = (document.getElementsByName("password")[0]).value;

        fetch("/api/password/", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `old_password=${oldPassword}&password=${password}`
        })
            .then((r) => {
                if (r.ok) {
                    window.alert("La tua password è stata aggiornata con successo!");

                    document.getElementById("modal-js-password").classList.remove("is-active");
                }
                else {
                    window.alert("La password inserita non corrisponde a quella del tuo account.");
                }

                document.forms["modal-js-password-form"].reset();
            });
    }
</script>

<div id="modal-js-password" class="modal">
    <form name="modal-js-password-form" onsubmit="updateUserPassword(); return false">
        <div class="modal-background">
            <!-- Empty tag. -->
        </div>

        <div class="modal-card">
            <header class="modal-card-head py-5">
                <div class="modal-card-title">
                    <span class="icon-text">
                        Modifica la tua password

                        <span class="icon">
                            <i class="fa-solid fa-lock ml-1"></i>
                        </span>
                    </span>
                </div>

                <button class="delete" aria-label="close">
                    <!-- Empty tag. -->
                </button>
            </header>

            <section class="modal-card-body py-5">
                <div class="field">
                    <label class="label">Password</label>

                    <p class="control has-icons-left">
                        <input class="input" type="password" name="old_password" 
                            placeholder="************" 
                            minlength="8"
                            required/>

                        <span class="icon is-small is-left">
                            <i class="fa-solid fa-key"></i>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <label class="label">Nuova password</label>

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
            </section>

            <footer class="modal-card-foot py-5">
                <div class="buttons">
                    <button class="button is-warning pr-5">
                        <span class="icon-text">
                            Modifica password

                            <span class="icon">
                                <i class="fa-solid fa-pen ml-3"></i>
                            </span>
                        </span>
                    </button>
                </div>
            </footer>
        </div>
    </form>
</div>
