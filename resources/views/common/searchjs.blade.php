<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInputs = document.querySelectorAll(".table-search");

        searchInputs.forEach(input => {
            input.addEventListener("keyup", function () {
                const searchTerm = input.value.toLowerCase();

                // Buscar el contenedor padre que tiene la tabla
                let container = input.closest(".widget, .card, .container, .row") || document;

                const table = container.querySelector(".searchable-table");
                if (!table) return;

                const rows = table.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    row.style.display = rowText.includes(searchTerm) ? "" : "none";
                });
            });
        });
    });
</script>
