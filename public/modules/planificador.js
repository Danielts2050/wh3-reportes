(function () {
    'use strict';

    /* ─── Estado global ─── */
    let materiales = window.PLAN_MATERIALES || [];
    const NUM_CONTENEDORES = 8;
    const STORAGE_KEY = 'planificador-board';
    const MAX_UNDO = 50;

    let contenedores = [];
    let historial = [];
    let currentHistoryIdx = -1;

    /* ─── DOM refs ─── */
    const $ = (s, ctx) => (ctx || document).querySelector(s);
    const $$ = (s, ctx) => Array.from((ctx || document).querySelectorAll(s));

    const panelMat = $('#panel-materiales');
    const contGrid = $('#contenedores-grid');
    const searchInput = $('#busqueda');
    const toggleAgotados = $('#toggle-agotados');
    const undoBtn = $('#undo-btn');
    const redoBtn = $('#redo-btn');
    const toast = $('#toast');
    const toastMsg = $('#toast-msg');
    const modalOverlay = $('#modal-overlay');
    const modalTitle = $('#modal-title');
    const modalInfo = $('#modal-info');
    const modalInput = $('#modal-input');
    const modalConfirm = $('#modal-confirm');
    const modalCancel = $('#modal-cancel');
    const statMat = $('#stat-materiales');
    const statReq = $('#stat-palets-req');
    const statDisp = $('#stat-palets-disp');
    const statContUso = $('#stat-contenedores-uso');

    let dragData = null; // { material, from: 'pool'|'container', containerIdx, itemIdx }
    let modalCallback = null;

    /* ─── Inicializar ─── */
    function init() {
        if (!panelMat) return; // no board view
        initContenedores();
        const saved = loadState();
        if (saved) {
            showToast('¿Restaurar sesión anterior?', [
                { label: 'Sí', action: () => { restoreState(saved); hideToast(); } },
                { label: 'No', action: () => { clearState(); hideToast(); } },
            ]);
        }
        render();
        bindEvents();
        updateUndoBtn();
        updateStats();
    }

    function initContenedores() {
        for (let i = 0; i < NUM_CONTENEDORES; i++) {
            contenedores.push({
                nombre: 'Contenedor ' + (i + 1),
                capacidad: 34,
                items: [],
            });
        }
    }

    /* ─── Save / Load / Clear ─── */
    function saveState() {
        const data = { materiales, contenedores, historial, currentHistoryIdx };
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); } catch(e) {}
    }

    function loadState() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch(e) { return null; }
    }

    function clearState() {
        try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}
        historial = [];
        currentHistoryIdx = -1;
        updateUndoBtn();
    }

    function restoreState(saved) {
        materiales = saved.materiales || [];
        contenedores = saved.contenedores || [];
        if (saved.historial) {
            historial = saved.historial;
            currentHistoryIdx = saved.currentHistoryIdx ?? (historial.length - 1);
        }
        render();
        updateStats();
        updateUndoBtn();
        saveState();
    }

    /* ─── Historial (Undo) ─── */
    function pushHistory() {
        const snapshot = JSON.stringify({ materiales, contenedores });
        historial = historial.slice(0, currentHistoryIdx + 1);
        historial.push(snapshot);
        if (historial.length > MAX_UNDO) historial.shift();
        currentHistoryIdx = historial.length - 1;
        updateUndoBtn();
    }

    function undo() {
        if (currentHistoryIdx <= 0) return;
        currentHistoryIdx--;
        const snap = JSON.parse(historial[currentHistoryIdx]);
        materiales = snap.materiales;
        contenedores = snap.contenedores;
        render();
        updateStats();
        saveState();
        updateUndoBtn();
    }

    function updateUndoBtn() {
        if (undoBtn) undoBtn.disabled = currentHistoryIdx <= 0;
        if (redoBtn) redoBtn.disabled = currentHistoryIdx >= historial.length - 1;
    }

    function redo() {
        if (currentHistoryIdx >= historial.length - 1) return;
        currentHistoryIdx++;
        const snap = JSON.parse(historial[currentHistoryIdx]);
        materiales = snap.materiales;
        contenedores = snap.contenedores;
        render();
        updateStats();
        saveState();
        updateUndoBtn();
    }

    /* ─── Búsqueda ─── */
    function getSearchTerm() {
        return searchInput ? searchInput.value.toLowerCase().trim() : '';
    }

    function showAgotados() {
        return toggleAgotados ? toggleAgotados.dataset.show === 'true' : false;
    }

    /* ─── Render ─── */
    function render() {
        renderMateriales();
        renderContenedores();
    }

    function renderMateriales() {
        if (!panelMat) return;
        const term = getSearchTerm();
        const showAgot = showAgotados();

        let html = '';
        for (const m of materiales) {
            const disp = Math.max(0, m.palets_disponibles - m.palets_asignados);
            const cumplido = m.palets_asignados >= m.palets_requeridos;
            const isAgotado = m.estado === 'agotado' || m.palets_disponibles <= 0;

            if (cumplido && !showAgot) continue;
            if (isAgotado && !showAgot) continue;
            if (term && !m.material.toLowerCase().includes(term)) continue;

            let cls = 'plan-card';
            if (cumplido) cls += ' est-completo';
            else if (isAgotado) cls += ' est-agotado';
            else if (disp > 0 && disp < m.palets_requeridos) cls += ' est-parcial';
            else if (disp > 0) cls += ' est-disponible';
            else cls += ' est-agotado';

            const draggable = disp > 0;

            html += `<div class="${cls}" draggable="${draggable}"
                data-material="${m.material}">
                <div class="mat-codigo">${m.material}</div>
                <div class="mat-detalle">
                    <span>Req: <span class="val">${fmt(m.palets_requeridos)}</span></span>
                    <span>Disp: <span class="val">${fmt(disp)}</span></span>
                    <span>Asig: <span class="val">${fmt(m.palets_asignados)}</span></span>
                </div>
            </div>`;
        }

        panelMat.innerHTML = html;

        // Re-bind dragstart via global handler
        $$('.plan-card[draggable="true"]', panelMat).forEach(el => {
            el.addEventListener('dragstart', function(e) {
                dragStartPool(e, this.dataset.material);
            });
        });
    }

    function renderContenedores() {
        if (!contGrid) return;

        contGrid.innerHTML = '';
        for (let i = 0; i < contenedores.length; i++) {
            const c = contenedores[i];
            const totalAsignado = c.items.reduce((s, it) => s + it.palets, 0);
            const pct = c.capacidad > 0 ? (totalAsignado / c.capacidad) * 100 : 0;
            let fillClass = '';
            if (pct > 95) fillClass = 'danger';
            else if (pct > 80) fillClass = 'warning';

            const div = document.createElement('div');
            div.className = 'plan-contenedor';
            div.dataset.containerIdx = i;
            div.innerHTML = `
                <div class="plan-contenedor-header no-print">
                    <input class="plan-contenedor-nombre" value="${escHtml(c.nombre)}" data-idx="${i}" />
                    <span class="plan-contenedor-cap">
                        Cap: <input type="number" min="1" max="999" value="${c.capacidad}" data-idx="${i}" />
                    </span>
                </div>
                <div class="plan-progress">
                    <div class="plan-progress-fill ${fillClass}" style="width:${Math.min(pct,100).toFixed(1)}%"></div>
                </div>
                <div class="plan-progress-text no-print">${totalAsignado.toFixed(1)} / ${c.capacidad} (${pct.toFixed(1)}%)</div>
                <div class="plan-contenedor-items" data-container="${i}">
                    ${c.items.length === 0
                        ? '<div class="plan-contenedor-empty"><i class="fa-solid fa-arrow-down"></i> Arrastra materiales aquí</div>'
                        : c.items.map((it, j) => `
                            <div class="item" draggable="true"
                                data-material="${escHtml(it.material)}"
                                data-ci="${i}"
                                data-ii="${j}">
                                <span class="item-mat">${escHtml(it.material)}</span>
                                <span class="item-qty">${fmt(it.palets)}</span>
                                <button class="item-remove no-print" data-ci="${i}" data-ii="${j}" title="Quitar">✕</button>
                            </div>
                        `).join('')}
                </div>
            `;
            contGrid.appendChild(div);
        }

        // Bind drop on each container
        $$('.plan-contenedor-items').forEach(el => {
            el.addEventListener('dragover', dragOver);
            el.addEventListener('dragenter', dragEnter);
            el.addEventListener('dragleave', dragLeave);
            el.addEventListener('drop', drop);
        });

        // Bind dragstart on container items
        $$('.plan-contenedor-items .item[draggable="true"]').forEach(el => {
            el.addEventListener('dragstart', function(e) {
                const ci = parseInt(this.dataset.ci);
                const ii = parseInt(this.dataset.ii);
                dragStartContainer(e, ci, ii);
            });
        });

        // Bind nombre edit
        $$('.plan-contenedor-nombre').forEach(inp => {
            inp.addEventListener('change', function() {
                const idx = parseInt(this.dataset.idx);
                contenedores[idx].nombre = this.value;
                saveState(); pushHistory();
            });
        });

        // Bind capacidad edit
        $$('.plan-contenedor-cap input').forEach(inp => {
            inp.addEventListener('change', function() {
                const idx = parseInt(this.dataset.idx);
                const val = parseInt(this.value) || 1;
                contenedores[idx].capacidad = Math.max(1, val);
                saveState(); pushHistory();
                render();
            });
        });

        // Bind remove item buttons
        $$('.item-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const ci = parseInt(this.dataset.ci);
                const ii = parseInt(this.dataset.ii);
                removeItemFromContainer(ci, ii);
            });
        });
    }

    /* ─── Drag & Drop ─── */

    let ghostEl = null;
    let draggedMaterial = null;

    function createGhost(e, text) {
        if (ghostEl) ghostEl.remove();
        ghostEl = document.createElement('div');
        ghostEl.className = 'plan-drag-ghost';
        ghostEl.textContent = text;
        document.body.appendChild(ghostEl);
        e.dataTransfer.setDragImage(ghostEl, 40, 14);
    }

    function dragStartPool(e, material) {
        dragData = { material, from: 'pool' };
        draggedMaterial = material;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', material);
        createGhost(e, material);
        setTimeout(() => {
            const card = panelMat.querySelector(`.plan-card[data-material="${material}"]`);
            if (card) card.classList.add('dragging');
        }, 0);
    }

    function dragStartContainer(e, ci, ii) {
        const item = contenedores[ci].items[ii];
        dragData = { material: item.material, from: 'container', containerIdx: ci, itemIdx: ii };
        draggedMaterial = item.material;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', item.material);
        createGhost(e, item.material);
    }

    function dragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function dragEnter(e) {
        e.preventDefault();
        const container = e.currentTarget.closest('.plan-contenedor');
        if (container) container.classList.add('drag-over');
    }

    function dragLeave(e) {
        const container = e.currentTarget.closest('.plan-contenedor');
        if (container && !container.contains(e.relatedTarget)) {
            container.classList.remove('drag-over');
        }
    }

    function drop(e) {
        e.preventDefault();
        const containerEl = e.currentTarget.closest('.plan-contenedor');
        if (!containerEl) return;
        containerEl.classList.remove('drag-over');

        const ci = parseInt(containerEl.dataset.containerIdx);
        const material = draggedMaterial || e.dataTransfer.getData('text/plain');
        if (!material) return;

        // Clean up ghost
        if (ghostEl) { ghostEl.remove(); ghostEl = null; }
        $$('.plan-card.dragging').forEach(el => el.classList.remove('dragging'));

        // Check if from container (move)
        if (dragData && dragData.from === 'container') {
            const { containerIdx, itemIdx } = dragData;
            if (containerIdx === ci) { dragData = null; return; } // same container
            const item = contenedores[containerIdx].items[itemIdx];
            if (!item) { dragData = null; return; }
            const qty = item.palets;

            const capRestante = contenedores[ci].capacidad - contenedores[ci].items.reduce((s, it) => s + it.palets, 0);
            if (qty > capRestante) {
                showToast('El contenedor destino no tiene suficiente capacidad.');
                dragData = null; return;
            }

            // Remove from source
            contenedores[containerIdx].items.splice(itemIdx, 1);
            // Add to target
            contenedores[ci].items.push({ material, palets: qty });

            // Update material asignado
            updateMaterialAsignado(material);

            dragData = null;
            saveState(); pushHistory(); render(); updateStats();
            return;
        }

        // From pool
        const mat = materiales.find(m => m.material === material);
        if (!mat) { dragData = null; return; }

        const disp = mat.palets_disponibles - mat.palets_asignados;
        if (disp <= 0) { showToast('Material agotado o completamente asignado.'); dragData = null; return; }

        const capRestante = contenedores[ci].capacidad - contenedores[ci].items.reduce((s, it) => s + it.palets, 0);
        if (capRestante <= 0) { showToast('El contenedor está lleno.'); dragData = null; return; }

        openModal(material, Math.min(disp, capRestante), ci, capRestante);
        dragData = null;
    }

    /* ─── Modal ─── */
    function openModal(material, maxQty, containerIdx, capRestante) {
        modalTitle.textContent = `Asignar ${material}`;
        modalInfo.innerHTML = `Disponible: <strong>${fmt(maxQty)}</strong> palets &nbsp;|&nbsp; Capacidad restante: <strong>${fmt(capRestante)}</strong>`;
        modalInput.value = '';
        modalInput.max = maxQty;
        modalInput.placeholder = `1 — ${maxQty}`;
        modalOverlay.classList.add('open');
        modalInput.focus();

        modalCallback = { material, maxQty, containerIdx };
    }

    function closeModal() {
        modalOverlay.classList.remove('open');
        modalCallback = null;
    }

    function confirmModal() {
        if (!modalCallback) return;
        const { material, maxQty, containerIdx } = modalCallback;
        const qty = parseInt(modalInput.value, 10);
        if (isNaN(qty) || qty <= 0 || qty > maxQty) {
            modalInput.style.borderColor = '#dc3545';
            return;
        }
        modalInput.style.borderColor = '';

        contenedores[containerIdx].items.push({ material, palets: qty });
        updateMaterialAsignado(material);

        closeModal();
        saveState(); pushHistory(); render(); updateStats();
    }

    modalConfirm.addEventListener('click', confirmModal);
    modalCancel.addEventListener('click', closeModal);
    modalInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') confirmModal();
        if (e.key === 'Escape') closeModal();
    });
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) closeModal();
    });

    /* ─── Remove item from container ─── */
    function removeItemFromContainer(ci, ii) {
        const item = contenedores[ci].items[ii];
        if (!item) return;
        contenedores[ci].items.splice(ii, 1);
        updateMaterialAsignado(item.material);
        saveState(); pushHistory(); render(); updateStats();
    }

    /* ─── Recalcular asignación ─── */
    function updateMaterialAsignado(material) {
        let total = 0;
        for (const c of contenedores) {
            for (const it of c.items) {
                if (it.material === material) total += it.palets;
            }
        }
        const mat = materiales.find(m => m.material === material);
        if (mat) mat.palets_asignados = total;
    }

    /* ─── Stats ─── */
    function updateStats() {
        if (!statMat) return;
        const totalMat = materiales.length;
        const reqTotal = materiales.reduce((s, m) => s + m.palets_requeridos, 0);
        const dispTotal = materiales.reduce((s, m) => s + (m.palets_disponibles - m.palets_asignados), 0);
        const contUso = contenedores.filter(c => c.items.length > 0).length;

        statMat.textContent = totalMat;
        statReq.textContent = fmt(reqTotal);
        statDisp.textContent = fmt(dispTotal);
        statContUso.textContent = `${contUso}/${NUM_CONTENEDORES}`;
    }

    /* ─── Toast ─── */
    function showToast(msg, actions) {
        toastMsg.textContent = msg;
        if (actions) {
            const container = document.getElementById('toast-actions');
            container.innerHTML = '';
            for (const a of actions) {
                const btn = document.createElement('button');
                btn.textContent = a.label;
                btn.addEventListener('click', a.action);
                container.appendChild(btn);
            }
        }
        toast.classList.add('show');
    }

    window.hideToast = function() {
        toast.classList.remove('show');
    };

    /* ─── Events ─── */
    function bindEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', render);
        }

        if (toggleAgotados) {
            toggleAgotados.addEventListener('click', function() {
                const show = this.dataset.show === 'true' ? 'false' : 'true';
                this.dataset.show = show;
                this.textContent = show === 'true' ? 'Ocultar agotados/completos' : 'Mostrar agotados';
                render();
            });
        }

        if (undoBtn) {
            undoBtn.addEventListener('click', undo);
        }

        if (redoBtn) {
            redoBtn.addEventListener('click', redo);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            const tag = e.target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
                e.preventDefault();
                redo();
            }
        });

        // Global dragend to clear stale state
        document.addEventListener('dragend', function() {
            draggedMaterial = null;
            dragData = null;
            if (ghostEl) { ghostEl.remove(); ghostEl = null; }
            $$('.plan-card.dragging').forEach(el => el.classList.remove('dragging'));
            $$('.plan-contenedor.drag-over').forEach(el => el.classList.remove('drag-over'));
        });

        // Export Excel
        const exportBtn = document.getElementById('export-excel');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportExcel);
        }

        // Print
        const printBtn = document.getElementById('btn-print');
        if (printBtn) {
            printBtn.addEventListener('click', function() { window.print(); });
        }
    }

    /* ─── Export Excel ─── */
    function exportExcel() {
        const btn = document.getElementById('export-excel');
        if (!btn || btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exportando...';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/planificador/exportar';
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = '_token';
            inp.value = csrf.content;
            form.appendChild(inp);
        }
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'estado_tablero';
        inp.value = JSON.stringify({ materiales, contenedores });
        form.appendChild(inp);
        document.body.appendChild(form);
        form.submit();
        setTimeout(() => {
            form.remove();
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-excel"></i> Exportar Excel';
        }, 5000);
    }

    /* ─── Helpers ─── */
    function fmt(n) {
        if (n === 0) return '0';
        if (Number.isInteger(n)) return n.toString();
        return n.toFixed(2).replace(/\.?0+$/, '');
    }

    function escHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /* ─── Init ─── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
