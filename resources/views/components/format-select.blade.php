@props([
    'formats'    => [],       // Array các format (ví dụ: ['2D', '3D', ...])
    'selected'   => [],       // Array các format đã được chọn (cho edit mode)
    'name'       => 'format[]',
    'label'      => 'Định dạng phim',
    'placeholder'=> 'Chọn định dạng phim…',
    'id'         => 'format-select',
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
    <div style="position:relative;" id="{{ $componentId }}-root" data-event-name="format-change">
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
                <span class="mgs-placeholder" style="color:#94a3b8;">{{ $placeholder }}</span>
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
                           placeholder="Tìm định dạng..."
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
                           aria-label="Tìm kiếm định dạng">
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

                @foreach($formats as $fmt)
                <div class="mgs-option"
                     id="{{ $componentId }}-opt-{{ $loop->index }}"
                     role="option"
                     aria-selected="false"
                     data-id="{{ $fmt }}"
                     data-name="{{ $fmt }}"
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
                    <span class="mgs-opt-name">{{ $fmt }}</span>
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
                           value="{{ $fmt }}"
                           id="chk_{{ $componentId }}_{{ $loop->index }}"
                           style="display:none;"
                           {{ in_array($fmt, $selectedIds) ? 'checked' : '' }}>
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

    @error('format')
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
<x-mg-select-js />
