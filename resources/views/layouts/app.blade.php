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
            <a class="navbar-brand" data-href="{{ route('dashboard') }}" role="link" tabindex="0">
                <i class="bi bi-shield-fill-check"></i> Sentinela
            </a>
            @auth
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-href="{{ route('dashboard') }}" role="link" tabindex="0">
                            <i class="bi bi-speedometer2"></i> Painel
                        </a>
                    </li>
                    @role('Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" data-href="{{ route('usuarios.index') }}" role="link" tabindex="0">
                                <i class="bi bi-people"></i> Usuários
                            </a>
                        </li>
                    @endrole
                    @hasanyrole('Atendente de Ocorrências|Gerente Operacional|Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ocorrencias.*') ? 'active' : '' }}" data-href="{{ route('ocorrencias.index') }}" role="link" tabindex="0">
                                <i class="bi bi-fire"></i> Ocorrências
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('Gerenciador de Abrigo|Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('abrigos.*') ? 'active' : '' }}" data-href="{{ route('abrigos.index') }}" role="link" tabindex="0">
                                <i class="bi bi-houses"></i> Abrigos
                            </a>
                        </li>
                    @endhasanyrole
                    @hasanyrole('Gerente Operacional|Admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('viaturas.*') ? 'active' : '' }}" data-href="{{ route('viaturas.index') }}" role="link" tabindex="0">
                                <i class="bi bi-truck"></i> Viaturas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('equipamentos.*') ? 'active' : '' }}" data-href="{{ route('equipamentos.index') }}" role="link" tabindex="0">
                                <i class="bi bi-tools"></i> Equipamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('voluntarios.*') ? 'active' : '' }}" data-href="{{ route('voluntarios.index') }}" role="link" tabindex="0">
                                <i class="bi bi-person-heart"></i> Voluntários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('equipes.*') ? 'active' : '' }}" data-href="{{ route('equipes.index') }}" role="link" tabindex="0">
                                <i class="bi bi-people"></i> Equipes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recursos.*') ? 'active' : '' }}" data-href="{{ route('recursos.index') }}" role="link" tabindex="0">
                                <i class="bi bi-search"></i> Consultar Recursos
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
            <div class="alert alert-success alert-dismissible fade show" id="flash-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content confirm-modal-content">
                <div class="modal-body text-center">
                    <div class="confirm-modal-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <p class="confirm-modal-message mb-0" id="confirmModalMessage"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmModalConfirmBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        const flashSuccess = document.getElementById('flash-success');
        if (flashSuccess) {
            setTimeout(() => {
                bootstrap.Alert.getOrCreateInstance(flashSuccess).close();
            }, 4000);
        }

        document.querySelectorAll('[data-href]').forEach((el) => {
            el.addEventListener('click', () => {
                window.location.href = el.dataset.href;
            });
        });

        document.querySelectorAll('a[data-href][tabindex]').forEach((el) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.href = el.dataset.href;
                }
            });
        });

        const confirmModalEl = document.getElementById('confirmModal');
        if (confirmModalEl) {
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const confirmModalMessage = document.getElementById('confirmModalMessage');
            const confirmModalConfirmBtn = document.getElementById('confirmModalConfirmBtn');
            let pendingForm = null;

            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (e) => {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }
                    e.preventDefault();
                    pendingForm = form;
                    confirmModalMessage.textContent = form.dataset.confirm;
                    confirmModal.show();
                });
            });

            confirmModalConfirmBtn.addEventListener('click', () => {
                confirmModal.hide();
                if (pendingForm) {
                    pendingForm.dataset.confirmed = 'true';
                    pendingForm.requestSubmit();
                    pendingForm = null;
                }
            });
        }

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