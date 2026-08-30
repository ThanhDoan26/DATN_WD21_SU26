@props([
    'provinces'   => null,
    'selected'    => '',
    'name'        => 'city',
    'label'       => 'Tỉnh / Thành phố',
    'placeholder' => 'Chọn Tỉnh / Thành phố...',
    'required'    => true,
    'id'          => 'province-select',
])

@php
    $provincesList = $provinces ?? config('provinces', []);
    $selectedValue = old($name, $selected ?? '');
    // Handle fuzzy matching for Ho Chi Minh if stored differently
    if ($selectedValue && !in_array($selectedValue, $provincesList)) {
        foreach ($provincesList as $p) {
            if (str_contains($selectedValue, 'Hồ Chí Minh') && str_contains($p, 'Hồ Chí Minh')) {
                $selectedValue = $p;
                break;
            }
        }
    }
    $componentId = $id . '-' . uniqid();
@endphp

<div class="mb-3" id="{{ $componentId }}-wrap">
    @if($label)
        <label class="form-label fw-semibold" style="font-size:.88rem;">
            {{ $label }} @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    {{-- ═══════ TRIGGER BUTTON ═══════ --}}
    <div style="position:relative;" id="{{ $componentId }}-root">
        <button type="button"
                id="{{ $componentId }}-trigger"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $componentId }}-dropdown"
                onclick="provSelect_toggle('{{ $componentId }}')"
                class="form-control @error($name) is-invalid @enderror"
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
            <span id="{{ $componentId }}-label" style="flex:1;min-width:0;overflow:hidden;display:flex;align-items:center;gap:.5rem;">
                <i class="fas fa-map-marker-alt text-primary" style="font-size:.85rem;"></i>
                <span id="{{ $componentId }}-text" class="{{ $selectedValue ? 'text-dark fw-medium' : 'text-muted' }}">
                    {{ $selectedValue ?: $placeholder }}
                </span>
            </span>
            <span style="display:flex;align-items:center;gap:.4rem;flex-shrink:0;">
                <i class="fas fa-chevron-down prov-arrow"
                   id="{{ $componentId }}-arrow"
                   style="font-size:.75rem;color:#94a3b8;transition:transform .2s;"></i>
            </span>
        </button>

        {{-- Hidden Input for form submit --}}
        <input type="hidden"
               name="{{ $name }}"
               id="{{ $componentId }}-input"
               value="{{ $selectedValue }}"
               @if($required) required @endif>

        {{-- ═══════ DROPDOWN PANEL ═══════ --}}
        <div id="{{ $componentId }}-dropdown"
             role="listbox"
             aria-label="{{ $label }}"
             style="
                display:none;
                position:absolute; top:calc(100% + 6px); left:0; right:0;
                z-index:1060;
                background:var(--bg-surface,#fff);
                border:1px solid var(--border-light,#e2e8f0);
                border-radius:12px;
                box-shadow:0 20px 60px rgba(0,0,0,.18), 0 4px 12px rgba(0,0,0,.08);
                backdrop-filter:blur(12px);
                -webkit-backdrop-filter:blur(12px);
                overflow:hidden;
                animation:provIn .18s ease;
             ">

            {{-- ── Search Bar ── --}}
            <div style="padding:.75rem .75rem .5rem;border-bottom:1px solid var(--border-light,#e2e8f0);background:inherit;">
                <div style="position:relative;display:flex;align-items:center;">
                    <i class="fas fa-search"
                       style="position:absolute;left:.75rem;color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                    <input type="text"
                           id="{{ $componentId }}-search"
                           placeholder="Tìm tỉnh, thành phố..."
                           autocomplete="off"
                           oninput="provSelect_filter('{{ $componentId }}')"
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
                           aria-label="Tìm kiếm tỉnh thành">
                    <button type="button"
                            id="{{ $componentId }}-clear-search"
                            onclick="provSelect_clearSearch('{{ $componentId }}')"
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
                     max-height:250px;
                     overflow-y:auto;
                     padding:.4rem 0;
                     scrollbar-width:thin;
                     scrollbar-color:#9333ea22 transparent;
                 ">

                {{-- Empty State --}}
                <div id="{{ $componentId }}-empty"
                     style="display:none;padding:1.25rem;text-align:center;color:#94a3b8;font-size:.82rem;">
                    <i class="fas fa-search-minus" style="font-size:1.3rem;margin-bottom:.4rem;opacity:.5;display:block;"></i>
                    <span>Không tìm thấy tỉnh / thành phố</span>
                </div>

                @foreach($provincesList as $prov)
                @php
                    $isCurSelected = ($selectedValue === $prov);
                @endphp
                <div class="prov-option {{ $isCurSelected ? 'selected' : '' }}"
                     id="{{ $componentId }}-opt-{{ Str::slug($prov) }}"
                     role="option"
                     aria-selected="{{ $isCurSelected ? 'true' : 'false' }}"
                     data-name="{{ $prov }}"
                     data-search="{{ Str::slug($prov, '') }} {{ mb_strtolower($prov, 'UTF-8') }}"
                     data-component="{{ $componentId }}"
                     onclick="provSelect_choose('{{ $componentId }}', '{{ addslashes($prov) }}')"
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
                         {{ $isCurSelected ? 'background:rgba(147,51,234,.08);font-weight:600;color:#9333ea;' : '' }}
                     "
                     onmouseenter="if(!this.classList.contains('selected')) this.style.background='rgba(147,51,234,.05)'"
                     onmouseleave="if(!this.classList.contains('selected')) this.style.background='transparent'">
                    <span class="prov-opt-name d-flex align-items-center gap-2">
                        <i class="fas fa-city {{ $isCurSelected ? 'text-primary' : 'text-muted opacity-50' }}" style="font-size:.8rem;"></i>
                        {{ $prov }}
                    </span>
                    <span class="prov-check-icon"
                          style="
                              width:20px; height:20px; border-radius:50%;
                              border:2px solid {{ $isCurSelected ? '#9333ea' : '#cbd5e1' }};
                              display:flex; align-items:center; justify-content:center;
                              background:{{ $isCurSelected ? 'linear-gradient(135deg,#9333ea,#ef4444)' : 'transparent' }};
                              color:#fff;
                              font-size:.65rem;
                              transition:all .15s; flex-shrink:0;
                          ">
                        @if($isCurSelected)
                            <i class="fas fa-check"></i>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @error($name)
        <div class="invalid-feedback d-block mt-1">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>

{{-- ═══════ STYLES ═══════ --}}
<style>
@keyframes provIn {
    from { opacity:0; transform:translateY(-6px) scale(.98); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
#{{ $componentId }}-list::-webkit-scrollbar { width:5px; }
#{{ $componentId }}-list::-webkit-scrollbar-thumb { background:linear-gradient(135deg,#9333ea44,#ef444444); border-radius:100px; }

/* Dark mode compat */
html.dark-theme #{{ $componentId }}-trigger { background:#131927 !important; border-color:#1f2937 !important; color:#f1f5f9 !important; }
html.dark-theme #{{ $componentId }}-dropdown { background:#131927 !important; border-color:#1f2937 !important; box-shadow:0 20px 60px rgba(0,0,0,.5) !important; }
html.dark-theme #{{ $componentId }}-search  { background:#0d1120 !important; border-color:#1f2937 !important; color:#f1f5f9 !important; }
html.dark-theme .prov-option { color:#e2e8f0 !important; }
</style>

{{-- ═══════ JAVASCRIPT LOGIC ═══════ --}}
@once
<script>
function provSelect_toggle(cId) {
    const dropdown = document.getElementById(cId + '-dropdown');
    const trigger = document.getElementById(cId + '-trigger');
    const arrow = document.getElementById(cId + '-arrow');
    const searchInput = document.getElementById(cId + '-search');

    if (!dropdown) return;

    const isOpen = dropdown.style.display !== 'none';

    // Close all other dropdowns
    document.querySelectorAll('[id$="-dropdown"]').forEach(d => {
        if (d.id !== cId + '-dropdown') {
            d.style.display = 'none';
            const oId = d.id.replace('-dropdown', '');
            const oTrigger = document.getElementById(oId + '-trigger');
            const oArrow = document.getElementById(oId + '-arrow');
            if (oTrigger) oTrigger.setAttribute('aria-expanded', 'false');
            if (oArrow) oArrow.style.transform = 'rotate(0deg)';
        }
    });

    if (isOpen) {
        dropdown.style.display = 'none';
        trigger.setAttribute('aria-expanded', 'false');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    } else {
        dropdown.style.display = 'block';
        trigger.setAttribute('aria-expanded', 'true');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}

function provSelect_choose(cId, value) {
    const input = document.getElementById(cId + '-input');
    const textEl = document.getElementById(cId + '-text');
    const dropdown = document.getElementById(cId + '-dropdown');
    const trigger = document.getElementById(cId + '-trigger');
    const arrow = document.getElementById(cId + '-arrow');
    const list = document.getElementById(cId + '-list');

    if (input) input.value = value;
    if (textEl) {
        textEl.textContent = value;
        textEl.className = 'text-dark fw-medium';
    }

    if (list) {
        const options = list.querySelectorAll('.prov-option');
        options.forEach(opt => {
            const optName = opt.getAttribute('data-name');
            const icon = opt.querySelector('.prov-check-icon');
            const cityIcon = opt.querySelector('.fa-city');
            if (optName === value) {
                opt.classList.add('selected');
                opt.style.background = 'rgba(147,51,234,.08)';
                opt.style.fontWeight = '600';
                opt.style.color = '#9333ea';
                if (icon) {
                    icon.style.borderColor = '#9333ea';
                    icon.style.background = 'linear-gradient(135deg,#9333ea,#ef4444)';
                    icon.innerHTML = '<i class="fas fa-check"></i>';
                }
                if (cityIcon) {
                    cityIcon.className = 'fas fa-city text-primary';
                }
            } else {
                opt.classList.remove('selected');
                opt.style.background = 'transparent';
                opt.style.fontWeight = 'normal';
                opt.style.color = 'var(--text-ink,#212529)';
                if (icon) {
                    icon.style.borderColor = '#cbd5e1';
                    icon.style.background = 'transparent';
                    icon.innerHTML = '';
                }
                if (cityIcon) {
                    cityIcon.className = 'fas fa-city text-muted opacity-50';
                }
            }
        });
    }

    if (dropdown) dropdown.style.display = 'none';
    if (trigger) trigger.setAttribute('aria-expanded', 'false');
    if (arrow) arrow.style.transform = 'rotate(0deg)';

    // Trigger standard change event on hidden input
    if (input) {
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function provSelect_filter(cId) {
    const searchInput = document.getElementById(cId + '-search');
    const clearBtn = document.getElementById(cId + '-clear-search');
    const list = document.getElementById(cId + '-list');
    const emptyEl = document.getElementById(cId + '-empty');

    if (!searchInput || !list) return;

    const query = searchInput.value.trim().toLowerCase();
    if (clearBtn) {
        clearBtn.style.display = query ? 'block' : 'none';
    }

    const options = list.querySelectorAll('.prov-option');
    let visibleCount = 0;

    options.forEach(opt => {
        const searchData = (opt.getAttribute('data-search') || '').toLowerCase();
        const name = (opt.getAttribute('data-name') || '').toLowerCase();

        // Vietnamese accent insensitive search
        const match = !query || searchData.includes(query) || name.includes(query);
        if (match) {
            opt.style.display = 'flex';
            visibleCount++;
        } else {
            opt.style.display = 'none';
        }
    });

    if (emptyEl) {
        emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function provSelect_clearSearch(cId) {
    const searchInput = document.getElementById(cId + '-search');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        provSelect_filter(cId);
    }
}

// Click outside handler
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id$="-root"]')) {
        document.querySelectorAll('[id$="-dropdown"]').forEach(d => {
            d.style.display = 'none';
            const cId = d.id.replace('-dropdown', '');
            const trigger = document.getElementById(cId + '-trigger');
            const arrow = document.getElementById(cId + '-arrow');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
    }
});
</script>
@endonce
