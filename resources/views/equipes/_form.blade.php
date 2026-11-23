@php
    $isEdit = $equipe !== null;

    // Após um reenvio com erro de validação (create ou edit), o old() do próprio envio
    // manda — tanto na seleção de membros quanto na função digitada para cada um.
    if ($errors->any()) {
        $membrosIniciais = collect(old('voluntarios', []))
            ->map(function ($id) use ($voluntarios) {
                $voluntario = $voluntarios->firstWhere('id', (int) $id);

                return $voluntario ? [
                    'id' => $voluntario->id,
                    'nome' => $voluntario->nome,
                    'telefone' => $voluntario->telefone,
                    'funcao' => old('funcoes.'.$id, ''),
                ] : null;
            })
            ->filter()
            ->values();
    } elseif ($isEdit) {
        $membrosIniciais = $equipe->voluntarios->map(fn ($v) => [
            'id' => $v->id,
            'nome' => $v->nome,
            'telefone' => $v->telefone,
            'funcao' => $funcoesEquipeMap[$v->pivot->funcao_equipe_id] ?? '',
        ])->values();
    } else {
        $membrosIniciais = collect();
    }

    $voluntariosDisponiveis = $voluntarios->map(fn ($v) => [
        'id' => $v->id,
        'nome' => $v->nome,
        'telefone' => $v->telefone,
        // Outras equipes de que esse voluntário já faz parte (excluindo a atual, se for edição)
        // — usado para avisar antes de adicionar alguém que já está em outro time.
        'outrasEquipes' => $v->equipes
            ->when($isEdit, fn ($c) => $c->reject(fn ($e) => $e->id === $equipe->id))
            ->pluck('nome')
            ->values(),
    ])->values();

    $funcoesEquipeNomes = $funcoesEquipe->pluck('nome')->values();
@endphp

<div class="form-section-title"><i class="bi bi-people-fill"></i> Identificação</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="nome" class="form-label">Nome da equipe</label>
        <input type="text" name="nome" id="nome"
               class="form-control @error('nome') is-invalid @enderror"
               value="{{ old('nome', $equipe->nome ?? '') }}" required autofocus>
        @error('nome')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="tipo_equipe_id" class="form-label">Tipo</label>
        <select name="tipo_equipe_id" id="tipo_equipe_id" class="form-select @error('tipo_equipe_id') is-invalid @enderror" required>
            <option value="">Selecione...</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->id }}" {{ (int) old('tipo_equipe_id', $equipe->tipo_equipe_id ?? 0) === $tipo->id ? 'selected' : '' }}>
                    {{ $tipo->nome }}
                </option>
            @endforeach
        </select>
        @error('tipo_equipe_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@if ($isEdit)
    <div class="form-check mb-3">
        <input type="checkbox" name="ativo" id="ativo" value="1" class="form-check-input"
               {{ old('ativo', $equipe->ativo) ? 'checked' : '' }}>
        <label for="ativo" class="form-check-label">Equipe ativa</label>
    </div>
@endif

<div class="form-section-title d-flex align-items-center justify-content-between">
    <span><i class="bi bi-person-check"></i> Membros</span>
    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarMembros">
        <i class="bi bi-plus-lg"></i> Adicionar Membros
    </button>
</div>

<div class="mb-1">
    <div class="membros-wrapper">
        <p id="membrosVazio" class="text-muted small text-center py-4 mb-0" hidden>
            Nenhum membro adicionado ainda. Clique em "Adicionar Membros" para incluir voluntários nesta equipe.
        </p>
        <div id="membrosLista"></div>
    </div>
</div>
<p class="text-muted small mb-3">Função é opcional (ex: Motorista/Condutor, Socorrista). Digitar uma função nova a cadastra automaticamente.</p>

<div class="modal fade" id="modalAdicionarMembros" tabindex="-1" aria-labelledby="modalAdicionarMembrosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarMembrosLabel">Adicionar Membros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                @if ($voluntarios->isEmpty())
                    <p class="text-muted small mb-0">Nenhum voluntário ativo disponível para compor a equipe.</p>
                @else
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="buscaVoluntarioModal" class="form-control" placeholder="Buscar voluntário por nome...">
                    </div>
                    <ul class="list-group" id="listaVoluntariosModal"></ul>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Concluído</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const voluntariosDisponiveis = @json($voluntariosDisponiveis);
        const membrosIniciais = @json($membrosIniciais);
        const funcoesEquipeOpcoes = @json($funcoesEquipeNomes);

        const listaEl = document.getElementById('membrosLista');
        const vazioEl = document.getElementById('membrosVazio');
        const listaModalEl = document.getElementById('listaVoluntariosModal');
        const buscaModalEl = document.getElementById('buscaVoluntarioModal');

        const membrosIds = new Set();

        function escapeHtml(texto) {
            const div = document.createElement('div');
            div.textContent = texto ?? '';
            return div.innerHTML;
        }

        // Mesma lógica do combobox genérico de layouts/app.blade.php, só que aplicável a
        // um elemento específico — aqui as linhas de membro são criadas dinamicamente,
        // então não existem no DOM no momento em que o script genérico roda.
        function initCombobox(wrap) {
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

                if (!matches.length && !term) { close(); return; }

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
                    li.addEventListener('mousedown', (e) => { e.preventDefault(); close(); });
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
                if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(activeIndex + 1, els.length - 1)); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(activeIndex - 1, 0)); }
                else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); els[activeIndex].dispatchEvent(new Event('mousedown')); }
                else if (e.key === 'Escape') { close(); }
            });

            document.addEventListener('click', (e) => {
                if (!wrap.contains(e.target)) close();
            });
        }

        function atualizarVazio() {
            vazioEl.hidden = membrosIds.size > 0;
        }

        function criarLinhaMembro(voluntario, funcaoInicial) {
            const row = document.createElement('div');
            row.className = 'membro-row';
            row.dataset.id = voluntario.id;

            row.innerHTML = `
                <input type="hidden" name="voluntarios[]" value="${voluntario.id}">
                <div class="membro-info">
                    <span class="membro-nome">${escapeHtml(voluntario.nome)}</span>
                    ${voluntario.telefone ? `<span class="membro-telefone">${escapeHtml(voluntario.telefone)}</span>` : ''}
                </div>
                <div class="membro-funcao combobox" data-options='${JSON.stringify(funcoesEquipeOpcoes)}'>
                    <input type="text" name="funcoes[${voluntario.id}]" autocomplete="off"
                           class="combobox-input form-control form-control-sm text-end" placeholder="Função (opcional)">
                    <ul class="combobox-list" hidden></ul>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger membro-remover" title="Remover membro">
                    <i class="bi bi-trash"></i>
                </button>
            `;

            row.querySelector('.combobox-input').value = funcaoInicial || '';
            row.querySelector('.membro-remover').addEventListener('click', () => removerMembro(voluntario.id));
            initCombobox(row.querySelector('.combobox'));

            return row;
        }

        function adicionarMembro(voluntario, funcaoInicial) {
            if (membrosIds.has(voluntario.id)) return;

            membrosIds.add(voluntario.id);
            listaEl.appendChild(criarLinhaMembro(voluntario, funcaoInicial));
            atualizarVazio();
        }

        function removerMembro(id) {
            membrosIds.delete(id);

            const row = listaEl.querySelector('.membro-row[data-id="' + id + '"]');
            if (row) row.remove();
            atualizarVazio();

            const checkbox = document.getElementById('chk_voluntario_' + id);
            if (checkbox) checkbox.checked = false;
        }

        function renderListaModal(filtro) {
            if (!listaModalEl) return;

            const termo = filtro.trim().toLowerCase();
            const filtrados = voluntariosDisponiveis.filter((v) => v.nome.toLowerCase().includes(termo));

            listaModalEl.innerHTML = '';

            if (!filtrados.length) {
                listaModalEl.innerHTML = '<li class="list-group-item text-muted text-center">Nenhum voluntário encontrado.</li>';
                return;
            }

            filtrados.forEach((v) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center gap-2';
                li.innerHTML = `
                    <input type="checkbox" class="form-check-input mt-0 flex-shrink-0" id="chk_voluntario_${v.id}" ${membrosIds.has(v.id) ? 'checked' : ''}>
                    <label for="chk_voluntario_${v.id}" class="flex-grow-1 mb-0" style="cursor: pointer;">
                        ${escapeHtml(v.nome)}
                        ${v.telefone ? `<span class="text-muted small ms-1">${escapeHtml(v.telefone)}</span>` : ''}
                        ${v.outrasEquipes.length ? `<br><span class="text-warning small"><i class="bi bi-exclamation-triangle"></i> Já é membro de: ${escapeHtml(v.outrasEquipes.join(', '))}</span>` : ''}
                    </label>
                `;
                li.querySelector('input').addEventListener('change', (e) => {
                    if (e.target.checked) {
                        if (v.outrasEquipes.length) {
                            const plural = v.outrasEquipes.length > 1;
                            const pergunta = `${v.nome} já faz parte d${plural ? 'as equipes' : 'a equipe'} ${v.outrasEquipes.join(', ')}. Deseja adicionar mesmo assim?`;

                            if (!window.confirm(pergunta)) {
                                e.target.checked = false;
                                return;
                            }
                        }

                        adicionarMembro(v, '');
                    } else {
                        removerMembro(v.id);
                    }
                });
                listaModalEl.appendChild(li);
            });
        }

        membrosIniciais.forEach((m) => {
            adicionarMembro({ id: m.id, nome: m.nome, telefone: m.telefone }, m.funcao);
        });
        atualizarVazio();

        if (buscaModalEl) {
            buscaModalEl.addEventListener('input', () => renderListaModal(buscaModalEl.value));
            renderListaModal('');
        }
    })();
</script>

<div class="form-section-title"><i class="bi bi-journal-text"></i> Observações</div>

<div class="mb-3">
    <textarea name="observacoes" id="observacoes" rows="3"
              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
              style="resize: none; overflow: hidden;">{{ old('observacoes', $equipe->observacoes ?? '') }}</textarea>
    @error('observacoes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
