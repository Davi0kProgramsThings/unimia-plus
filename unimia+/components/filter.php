<script>
    function filter(value) {
        for (const tr of document.querySelectorAll("tbody > tr")) {
            tr.classList.add("is-hidden");

            for (const td of tr.querySelectorAll("td")) {
                if (td.innerText.toLowerCase().includes(value))
                    tr.classList.remove("is-hidden");
            }
        }
    }
</script>

<div class="field mb-5">
    <label class="label">
        Cerca nella tabella:
    </label>

    <div class="control has-icons-left">
        <input 
            class="input" 
            type="text" 
            placeholder="Filtra per un qualsiasi valore presente nella tabella..." 
            
            oninput="filter(this.value.toLowerCase())"
        >

        <span class="icon is-small is-left">
            <i class="fa-solid fa-search"></i>
        </span>
    </div>
</div>
