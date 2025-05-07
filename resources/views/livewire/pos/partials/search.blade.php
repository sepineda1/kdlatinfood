<div>
    <style>
        .input-group .input-group-prepend .input-group-text{
            background-color:#FF5100 !important;
        }
    </style>

    {{-- resources/views/layouts/navbar.blade.php --}}
    <ul class="navbar-item flex-row search-ul list-unstyled mt-2">
        <li class="nav-item align-self-center search-animated">
            <div class="form-inline search-full form-inline search position-relative" >
                <div class="input-group mb-3 w-100">
                    <div class="input-group-prepend ">
                        <span class="input-group-text" id="basic-addon1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-search toggle-search text-white">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>

                        </span>
                    </div>
                    <input type="text" id="product-search" class="form-control" placeholder="Nombre del Producto"
                        aria-label="Username" aria-describedby="basic-addon1">
                </div>

                {{-- Lista de resultados (oculta) --}}
                <ul id="search-results" class="list-group position-absolute w-100"
                    style="top:100%; left:0; z-index:1000; display:none; min-height:300px ; height:300px; overflow-y: auto; background: white;">
                    @foreach ($products as $prod)
                        <li class="list-group-item list-group-item-action" data-code="{{ $prod->barcode }}">
                            {{ $prod->full_name }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </li>
    </ul>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('product-search');
            const ul = document.getElementById('search-results');
            const items = Array.from(ul.querySelectorAll('li'));

            // Al escribir en el input
            input.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                if (q.length < 2) {
                    ul.style.display = 'none';
                    return;
                }

                let anyVisible = false;
                items.forEach(li => {
                    const name = li.textContent.trim().toLowerCase();
                    if (name.includes(q)) {
                        li.style.display = '';
                        anyVisible = true;
                    } else {
                        li.style.display = 'none';
                    }
                });

                ul.style.display = anyVisible ? 'block' : 'none';
            });

            // Al hacer clic en un resultado
            ul.addEventListener('click', function(e) {
                const li = e.target.closest('li[data-code]');
                if (!li) return;
                const code = li.dataset.code;

                // Emite igual que antes
                Livewire.emit('scan-code', code);

                // Limpia todo
                input.value = '';
                ul.style.display = 'none';
            });

            // Si otro emite scan-code, resetea también
            Livewire.on('scan-code', () => {
                input.value = '';
                ul.style.display = 'none';
            });
        });
    </script>

</div>
