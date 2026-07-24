@props([
    'categories' => [],       // Collection hoặc array các category (id, name)
    'selected'   => [],       // Array các id đã được chọn (cho edit mode)
    'name'       => 'categories[]',
    'label'      => 'Danh mục phim',
    'placeholder'=> 'Tìm thể loại...',
    'id'         => 'genre-select',
])

@php
    $selectedIds = is_array($selected) ? $selected : $selected->toArray();
    $componentId = $id . '-' . uniqid();
@endphp

<div class="mb-3" id="{{ $componentId }}-wrap">
    <label class="form-label fw-semibold" style="font-size:.88rem;">
        {{ $label }}
    </label>

    {{-- ═══════ TRIGGER BUTTON ═══════ --}}
    <div style="position:relative;" id="{{ $componentId }}-root">
        <button type="button"
                id="{{ $componentId }}-trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $componentId }}-dropdown"
                onclick="mgSelect_toggle('{{ $componentId }}')"
                style="
                    width:100%; text-align:left;
                    background:var(--bg-surface,#fff);
                    border:1px solid var(--border-light,#dee2e6);
                    border-radius:8px;
                    padding:.55rem .9rem;
                    font-size:.875rem;
                    color:var(--text-ink,#212529);
                    cursor:pointer;
                    display:flex; align-items:center; justify-content:space-between; gap:.5rem;
                    transition:border-color .15s, box-shadow .15s;
                    min-height:42px;
                ">
            <span id="{{ $componentId }}-label" style="flex:1;min-width:0;overflow:hidden;">
                <span class="mgs-placeholder" style="color:#94a3b8;">Chọn thể loại phim…</span>
            </span>
            <span style="display:flex;align-items:center;gap:.4rem;flex-shrink:0;">
                <span id="{{ $componentId }}-count"
                      style="display:none;background:linear-gradient(135deg,#9333ea,#ef4444);color:#fff;border-radius:100px;padding:.1rem .55rem;font-size:.7rem;font-weight:700;line-height:1.4;">
                </span>
                <i class="fas fa-chevron-down mgs-arrow"
                   id="{{ $componentId }}-arrow"
                   style="font-size:.75rem;color:#94a3b8;transition:transform .2s;"></i>
            </span>
        </button>

        {{-- ═══════ DROPDOWN PANEL ═══════ --}}
        <div id="{{ $componentId }}-dropdown"
             role="listbox"
             aria-multiselectable="true"
             aria-label="{{ $label }}"
             style="
                display:none;
                position:absolute; top:calc(100% + 6px); left:0; right:0;
                z-index:1050;
                background:var(--bg-surface,#fff);
                border:1px solid var(--border-light,#e2e8f0);
                border-radius:12px;
                box-shadow:0 20px 60px rgba(0,0,0,.18), 0 4px 12px rgba(0,0,0,.08);
                backdrop-filter:blur(12px);
                -webkit-backdrop-filter:blur(12px);
                overflow:hidden;
                animation:mgsIn .18s ease;
             ">

            {{-- ── Search Bar ── --}}
            <div style="padding:.75rem .75rem .5rem;border-bottom:1px solid var(--border-light,#e2e8f0);background:inherit;">
                <div style="position:relative;display:flex;align-items:center;">
                    <i class="fas fa-search"
                       style="position:absolute;left:.75rem;color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                    <input type="text"
                           id="{{ $componentId }}-search"
                           placeholder="{{ $placeholder }}"
                           autocomplete="off"
                           oninput="mgSelect_filter('{{ $componentId }}')"
                           style="
                               width:100%;
                               padding:.5rem .5rem .5rem 2.2rem;
                               border:1px solid var(--border-light,#dee2e6);
                               border-radius:8px;
                               font-size:.82rem;
                               background:var(--bg-base,#f8fafc);
                               color:var(--text-ink,#212529);
                               outline:none;
                               transition:border-color .15s;
                           "
                           onfocus="this.style.borderColor='#9333ea'"
                           onblur="this.style.borderColor=''"
                           aria-label="Tìm kiếm thể loại">
                    <button type="button"
                            id="{{ $componentId }}-clear-search"
                            onclick="mgSelect_clearSearch('{{ $componentId }}')"
                            title="Xóa từ khóa"
                            style="
                                display:none;
                                position:absolute; right:.5rem;
                                background:none; border:none; cursor:pointer;
                                color:#94a3b8; padding:.2rem .4rem; border-radius:4px;
                                font-size:.8rem; line-height:1;
                            "
                            aria-label="Xóa từ khóa tìm kiếm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- ── Options List ── --}}
            <div id="{{ $componentId }}-list"
                 style="
                     max-height:240px;
                     overflow-y:auto;
                     padding:.4rem 0;
                     scrollbar-width:thin;
                     scrollbar-color:#9333ea22 transparent;
                 ">

                {{-- Empty State --}}
                <div id="{{ $componentId }}-empty"
                     style="display:none;padding:1.25rem;text-align:center;color:#94a3b8;font-size:.82rem;">
                    <i class="fas fa-search-minus" style="font-size:1.3rem;margin-bottom:.4rem;opacity:.5;display:block;"></i>
                    <span id="{{ $componentId }}-empty-msg">Không tìm thấy kết quả</span>
                </div>

                @foreach($categories as $cat)
                <div class="mgs-option"
                     id="{{ $componentId }}-opt-{{ $cat->id }}"
                     role="option"
                     aria-selected="false"
                     data-id="{{ $cat->id }}"
                     data-name="{{ $cat->name }}"
                     data-component="{{ $componentId }}"
                     onclick="mgSelect_toggle_item(this)"
                     tabindex="-1"
                     style="
                         display:flex; align-items:center; justify-content:space-between;
                         padding:.6rem 1rem;
                         cursor:pointer;
                         font-size:.85rem;
                         color:var(--text-ink,#212529);
                         min-height:40px;
                         transition:background .12s;
                         user-select:none;
                     "
                     onmouseenter="this.style.background='rgba(147,51,234,.08)'"
                     onmouseleave="this.style.background=this.classList.contains('selected')?'rgba(147,51,234,.06)':'transparent'">
                    <span class="mgs-opt-name">{{ $cat->name }}</span>
                    <span class="mgs-check-icon"
                          style="
                              width:20px; height:20px; border-radius:6px;
                              border:2px solid #cbd5e1;
                              display:flex; align-items:center; justify-content:center;
                              transition:all .15s; flex-shrink:0;
                              font-size:.7rem;
                          ">
                    </span>

                    {{-- Hidden checkbox that actually submits --}}
                    <input type="checkbox"
                           name="{{ $name }}"
                           value="{{ $cat->id }}"
                           id="chk_{{ $componentId }}_{{ $cat->id }}"
                           style="display:none;"
                           {{ in_array($cat->id, $selectedIds) ? 'checked' : '' }}>
                </div>
                @endforeach
            </div>

            {{-- ── Footer ── --}}
            <div style="
                     padding:.6rem .75rem;
                     border-top:1px solid var(--border-light,#e2e8f0);
                     display:flex; align-items:center; justify-content:space-between;
                     font-size:.78rem; background:inherit;
                 ">
                <span id="{{ $componentId }}-footer-count" style="color:#94a3b8;">
                    0 đã chọn
                </span>
                <div style="display:flex;gap:.4rem;">
                    <button type="button"
                            onclick="mgSelect_selectAll('{{ $componentId }}')"
                            style="
                                background:rgba(147,51,234,.1); color:#9333ea;
                                border:1px solid rgba(147,51,234,.25); border-radius:6px;
                                padding:.28rem .75rem; font-size:.74rem; font-weight:600;
                                cursor:pointer; transition:background .15s;
                            "
                            onmouseenter="this.style.background='rgba(147,51,234,.18)'"
                            onmouseleave="this.style.background='rgba(147,51,234,.1)'">
                        <i class="fas fa-check-double me-1"></i> Chọn tất cả
                    </button>
                    <button type="button"
                            onclick="mgSelect_clearAll('{{ $componentId }}')"
                            style="
                                background:rgba(239,68,68,.08); color:#ef4444;
                                border:1px solid rgba(239,68,68,.2); border-radius:6px;
                                padding:.28rem .75rem; font-size:.74rem; font-weight:600;
                                cursor:pointer; transition:background .15s;
                            "
                            onmouseenter="this.style.background='rgba(239,68,68,.16)'"
                            onmouseleave="this.style.background='rgba(239,68,68,.08)'">
                        <i class="fas fa-times me-1"></i> Xóa tất cả
                    </button>
                </div>
            </div>
        </div>
    </div>

    @error('categories')
        <div style="color:#ef4444;font-size:.78rem;margin-top:.3rem;">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>

{{-- ═══════ STYLES ═══════ --}}
<style>
@keyframes mgsIn {
    from { opacity:0; transform:translateY(-6px) scale(.98); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.mgs-option.selected .mgs-check-icon {
    background: linear-gradient(135deg,#9333ea,#ef4444) !important;
    border-color: transparent !important;
    color: #fff !important;
}
.mgs-option.selected .mgs-check-icon::before { content: '✓'; font-weight:800; }
.mgs-option.selected .mgs-opt-name { color: #9333ea; font-weight:600; }
#{{ $componentId }}-list::-webkit-scrollbar { width:5px; }
#{{ $componentId }}-list::-webkit-scrollbar-thumb { background:linear-gradient(135deg,#9333ea44,#ef444444); border-radius:100px; }

/* Dark mode compat */
html.dark-theme #{{ $componentId }}-trigger { background:#131927 !important; border-color:#1f2937 !important; color:#f1f5f9 !important; }
html.dark-theme #{{ $componentId }}-dropdown { background:#131927 !important; border-color:#1f2937 !important; box-shadow:0 20px 60px rgba(0,0,0,.5) !important; }
html.dark-theme #{{ $componentId }}-search  { background:#0d1120 !important; border-color:#1f2937 !important; color:#f1f5f9 !important; }
html.dark-theme .mgs-option { color:#e2e8f0 !important; }
</style>

{{-- ═══════ JAVASCRIPT ═══════ --}}
<script>
(function() {
    /* ── Initialise on DOM ready ── */
    function init(cid) {
        const opts = document.querySelectorAll('#' + cid + '-list .mgs-option');
        opts.forEach(opt => {
            const chk = opt.querySelector('input[type="checkbox"]');
            if (chk && chk.checked) _markSelected(opt, cid);
        });
        _refreshTrigger(cid);
    }

    /* ── Open / Close ── */
    window.mgSelect_toggle = function(cid) {
        const dd = document.getElementById(cid + '-dropdown');
        const arrow = document.getElementById(cid + '-arrow');
        const trigger = document.getElementById(cid + '-trigger');
        const isOpen = dd.style.display === 'block';

        if (isOpen) {
            dd.style.display = 'none';
            arrow.style.transform = '';
            trigger.setAttribute('aria-expanded','false');
        } else {
            // Close all other open selects first
            document.querySelectorAll('[id$="-dropdown"]').forEach(d => {
                if (d.id !== cid + '-dropdown' && d.style.display === 'block') {
                    d.style.display = 'none';
                    const aid = d.id.replace('-dropdown','');
                    const a = document.getElementById(aid + '-arrow');
                    if (a) a.style.transform = '';
                }
            });
            dd.style.display = 'block';
            arrow.style.transform = 'rotate(180deg)';
            trigger.setAttribute('aria-expanded','true');
            // Auto focus search input
            setTimeout(() => {
                const si = document.getElementById(cid + '-search');
                if (si) si.focus();
            }, 50);
        }
    };

    /* ── Toggle single item ── */
    window.mgSelect_toggle_item = function(el) {
        const cid = el.dataset.component;
        const chk = el.querySelector('input[type="checkbox"]');
        if (el.classList.contains('selected')) {
            _unmarkSelected(el);
            if (chk) chk.checked = false;
        } else {
            _markSelected(el, cid);
            if (chk) chk.checked = true;
        }
        _refreshTrigger(cid);
    };

    /* ── Real-time search / filter ── */
    window.mgSelect_filter = function(cid) {
        const q = document.getElementById(cid + '-search').value.trim().toLowerCase();
        const clearBtn = document.getElementById(cid + '-clear-search');
        clearBtn.style.display = q ? 'block' : 'none';

        const opts = document.querySelectorAll('#' + cid + '-list .mgs-option');
        let visible = 0;
        opts.forEach(opt => {
            const name = opt.dataset.name.toLowerCase();
            const match = name.includes(q);
            opt.style.display = match ? 'flex' : 'none';
            if (match) visible++;
        });

        const empty = document.getElementById(cid + '-empty');
        const emptyMsg = document.getElementById(cid + '-empty-msg');
        if (visible === 0) {
            emptyMsg.textContent = q ? `Không tìm thấy thể loại "${q}"` : 'Danh sách trống';
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
        }
    };

    /* ── Clear search ── */
    window.mgSelect_clearSearch = function(cid) {
        const si = document.getElementById(cid + '-search');
        si.value = '';
        si.focus();
        document.getElementById(cid + '-clear-search').style.display = 'none';
        mgSelect_filter(cid);
    };

    /* ── Select All (only visible / filtered) ── */
    window.mgSelect_selectAll = function(cid) {
        document.querySelectorAll('#' + cid + '-list .mgs-option').forEach(opt => {
            if (opt.style.display !== 'none') {
                const chk = opt.querySelector('input[type="checkbox"]');
                _markSelected(opt, cid);
                if (chk) chk.checked = true;
            }
        });
        _refreshTrigger(cid);
    };

    /* ── Clear All ── */
    window.mgSelect_clearAll = function(cid) {
        document.querySelectorAll('#' + cid + '-list .mgs-option').forEach(opt => {
            const chk = opt.querySelector('input[type="checkbox"]');
            _unmarkSelected(opt);
            if (chk) chk.checked = false;
        });
        _refreshTrigger(cid);
    };

    /* ── Helpers ── */
    function _markSelected(el, cid) {
        el.classList.add('selected');
        el.setAttribute('aria-selected','true');
        el.style.background = 'rgba(147,51,234,.06)';
    }
    function _unmarkSelected(el) {
        el.classList.remove('selected');
        el.setAttribute('aria-selected','false');
        el.style.background = 'transparent';
    }

    /* ── Refresh trigger button label + count ── */
    function _refreshTrigger(cid) {
        const selected = document.querySelectorAll('#' + cid + '-list .mgs-option.selected');
        const label  = document.getElementById(cid + '-label');
        const count  = document.getElementById(cid + '-count');
        const footer = document.getElementById(cid + '-footer-count');
        const n = selected.length;

        if (n === 0) {
            label.innerHTML = '<span class="mgs-placeholder" style="color:#94a3b8;">Chọn thể loại phim…</span>';
            if (count) count.style.display = 'none';
        } else {
            const names = Array.from(selected).map(o => o.dataset.name);
            const shown = names.slice(0, 3);
            const rest  = names.length - shown.length;
            let html = shown.map(n =>
                `<span style="background:rgba(147,51,234,.12);color:#9333ea;border:1px solid rgba(147,51,234,.25);border-radius:100px;padding:.08rem .55rem;font-size:.72rem;font-weight:600;white-space:nowrap;">${n}</span>`
            ).join(' ');
            if (rest > 0) html += ` <span style="color:#94a3b8;font-size:.78rem;">+${rest}</span>`;
            label.innerHTML = html;
            if (count) {
                count.textContent = n;
                count.style.display = 'inline-flex';
            }
        }

        if (footer) footer.textContent = n + ' đã chọn';

        const root = document.getElementById(cid + '-root');
        if (root) {
            const selectedIds = Array.from(selected).map(o => o.dataset.id);
            root.dispatchEvent(new CustomEvent('genre-change', {
                bubbles: true,
                detail: { cid, selectedIds, count: n }
            }));
        }
    }

    /* ── Helper to get selected values programmatically ── */
    window.mgSelect_getSelectedValues = function(cid) {
        const selected = document.querySelectorAll('#' + cid + '-list .mgs-option.selected');
        return Array.from(selected).map(o => o.dataset.id);
    };

    /* ── Click outside to close ── */
    document.addEventListener('click', function(e) {
        document.querySelectorAll('[id$="-dropdown"]').forEach(dd => {
            if (dd.style.display !== 'block') return;
            const cid = dd.id.replace('-dropdown','');
            const root = document.getElementById(cid + '-root');
            if (root && !root.contains(e.target)) {
                dd.style.display = 'none';
                const arrow = document.getElementById(cid + '-arrow');
                if (arrow) arrow.style.transform = '';
                const trigger = document.getElementById(cid + '-trigger');
                if (trigger) trigger.setAttribute('aria-expanded','false');
            }
        });
    }, true);

    /* ── ESC key closes dropdown ── */
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[id$="-dropdown"]').forEach(dd => {
            if (dd.style.display === 'block') {
                const cid = dd.id.replace('-dropdown','');
                dd.style.display = 'none';
                const arrow = document.getElementById(cid + '-arrow');
                if (arrow) arrow.style.transform = '';
                document.getElementById(cid + '-trigger')?.focus();
            }
        });
    });

    /* ── Auto-init all on page load ── */
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id$="-list"] .mgs-option').forEach(opt => {
            const cid = opt.dataset.component;
            if (cid) init(cid);
        });
    });
    // Also init immediately if DOM is already loaded
    if (document.readyState !== 'loading') {
        document.querySelectorAll('[id$="-list"] .mgs-option').forEach(opt => {
            const cid = opt.dataset.component;
            if (cid) init(cid);
        });
    }
})();
</script>
