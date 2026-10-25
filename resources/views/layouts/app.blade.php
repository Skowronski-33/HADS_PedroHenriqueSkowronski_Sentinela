<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sentinela') - Sentinela</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sentinela.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sentinela">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-shield-fill-check"></i> Sentinela
            </a>
            @auth
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Painel
                        </a>
                    </li>
                    @role('Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                    @endrole
                    @hasanyrole('Gerenciador de Abrigo|Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('abrigos.*') ? 'active' : '' }}" href="{{ route('abrigos.index') }}">
                                <i class="bi bi-houses"></i> Abrigos
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('Gerente Operacional|Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('viaturas.*') ? 'active' : '' }}" href="{{ route('viaturas.index') }}">
                                <i class="bi bi-truck"></i> Viaturas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('equipamentos.*') ? 'active' : '' }}" href="{{ route('equipamentos.index') }}">
                                <i class="bi bi-tools"></i> Equipamentos
                            </a>
                        </li>
                    @endhasanyrole
                    
                </ul>
            @endauth
            <div class="ms-auto">
                @auth
                    <span class="text-light me-3">
                        <i class="bi bi-person-circle"></i>
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light">Sair</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('textarea.auto-resize').forEach((el) => {
            const resize = () => {
                el.style.height = 'auto';
                el.style.height = el.scrollHeight + 'px';
            };
            el.addEventListener('input', resize);
            resize();
        });

        document.querySelectorAll('input.mask-telefone').forEach((el) => {
            el.addEventListener('input', () => {
                let v = el.value.replace(/\D/g, '').slice(0, 11);
                if (v.length > 10) {
                    v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                } else if (v.length > 6) {
                    v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (v.length > 2) {
                    v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                } else if (v.length > 0) {
                    v = v.replace(/(\d{0,2})/, '($1');
                }
                el.value = v.trim().replace(/-$/, '');
            });
        });

        document.querySelectorAll('.combobox').forEach((wrap) => {
            const input = wrap.querySelector('.combobox-input');
            const list = wrap.querySelector('.combobox-list');
            const options = JSON.parse(wrap.dataset.options || '[]');
            let activeIndex = -1;

            const close = () => { list.hidden = true; activeIndex = -1; };

            const render = () => {
                const term = input.value.trim().toLowerCase();
                const matches = term ? options.filter((o) => o.toLowerCase().includes(term)) : options;
                const exact = options.some((o) => o.toLowerCase() === term);

                list.innerHTML = '';
                activeIndex = -1;

                if (!matches.length && !term) {
                    close();
                    return;
                }

                matches.forEach((opt) => {
                    const li = document.createElement('li');
                    li.className = 'combobox-item';
                    li.innerHTML = '<i class="bi bi-tag"></i> ' + opt;
                    li.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        input.value = opt;
                        close();
                    });
                    list.appendChild(li);
                });

                if (term && !exact) {
                    const li = document.createElement('li');
                    li.className = 'combobox-item combobox-item-new';
                    li.innerHTML = '<i class="bi bi-plus-circle"></i> Criar novo: "' + input.value.trim() + '"';
                    li.addEventListener('mousedown', (e) => {
                        e.preventDefault();
                        close();
                    });
                    list.appendChild(li);
                }

                if (!list.children.length) {
                    const li = document.createElement('li');
                    li.className = 'combobox-empty';
                    li.textContent = 'Nenhuma opção encontrada';
                    list.appendChild(li);
                }

                list.hidden = false;
            };

            const items = () => Array.from(list.querySelectorAll('.combobox-item'));

            const setActive = (index) => {
                const els = items();
                els.forEach((el) => el.classList.remove('is-active'));
                if (index >= 0 && index < els.length) {
                    els[index].classList.add('is-active');
                    els[index].scrollIntoView({ block: 'nearest' });
                }
                activeIndex = index;
            };

            input.addEventListener('focus', render);
            input.addEventListener('input', render);
            input.addEventListener('blur', close);

            input.addEventListener('keydown', (e) => {
                if (list.hidden) return;
                const els = items();
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActive(Math.min(activeIndex + 1, els.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActive(Math.max(activeIndex - 1, 0));
                } else if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    els[activeIndex].dispatchEvent(new Event('mousedown'));
                } else if (e.key === 'Escape') {
                    close();
                }
            });

            document.addEventListener('click', (e) => {
                if (!wrap.contains(e.target)) close();
            });
        });
    </script>
</body>
</html>