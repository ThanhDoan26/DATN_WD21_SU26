@once
<script>
(function() {
    /* ── Initialise on DOM ready ── */
    function init(cid) {
        const opts = document.querySelectorAll('#' + cid + '-list .mgs-option');
        opts.forEach(opt => {
            const chk = opt.querySelector('input[type="checkbox"]');
            if (chk && chk.checked) _markSelected(opt, cid);
        });

        // Save original label HTML to restore it when count is 0
        const label = document.getElementById(cid + '-label');
        if (label && !label.dataset.originalHtml) {
            label.dataset.originalHtml = label.innerHTML;
        }

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
            const searchInput = document.getElementById(cid + '-search');
            const searchPlaceholder = searchInput ? searchInput.placeholder : 'mục';
            const termName = searchPlaceholder.replace('Tìm ', '').replace('...', '').trim().toLowerCase();
            emptyMsg.textContent = q ? `Không tìm thấy ${termName} "${q}"` : 'Danh sách trống';
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
            label.innerHTML = label.dataset.originalHtml || '<span class="mgs-placeholder" style="color:#94a3b8;">Chọn…</span>';
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
            const eventName = root.dataset.eventName || 'genre-change';
            root.dispatchEvent(new CustomEvent(eventName, {
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
@endonce
