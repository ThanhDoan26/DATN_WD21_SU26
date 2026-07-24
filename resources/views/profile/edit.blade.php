@php
    $user       = auth()->user();
    $isAdmin    = $user->isAdmin();
    $isManager  = !$isAdmin  && $user->isManager();
    $isStaff    = !$isAdmin  && !$isManager && $user->isStaff();
    $isCustomer = !$isAdmin  && !$isManager && !$isStaff;

    if ($isAdmin)       $roleKey = 'admin';
    elseif ($isManager) $roleKey = 'manager';
    elseif ($isStaff)   $roleKey = 'staff';
    else                $roleKey = 'customer';

    $parentLayout = $isAdmin   ? 'admin.layouts.app'
                  : ($isManager ? 'layouts.manager'
                  : ($isStaff   ? 'layouts.staff'
                  : 'layouts.frontend'));
@endphp

@extends($parentLayout)

@section('title', 'Hồ Sơ Cá Nhân — movieGo')

@if($isAdmin || $isManager || $isStaff)
    @section('page_title', 'Hồ Sơ Cá Nhân')
@endif

@if($isAdmin || $isManager || $isStaff)
    @section('extra_css')
@else
    @push('styles')
@endif

<style>
/* ═══════════════════════════════════════════
   DESIGN TOKENS — always-dark defaults (Customer / frontend layout)
═══════════════════════════════════════════ */
:root {
    --mgp-bg-deep:     #0B0F19;
    --mgp-bg-card:     #131927;
    --mgp-border:      #1F2937;
    --mgp-border-glow: rgba(147,51,234,.27);
    --accent-red:      #EF4444;
    --accent-pur:      #9333EA;
    --accent-pink:     #ec4899;
    --mgp-text-pri:    #F1F5F9;
    --mgp-text-sec:    #CBD5E1;
    --mgp-text-muted:  #64748B;
    --success:         #10B981;
    --warn:            #F59E0B;
    /* shorthand aliases used in components */
    --bg-deep:     var(--mgp-bg-deep);
    --bg-card:     var(--mgp-bg-card);
    --border:      var(--mgp-border);
    --border-glow: var(--mgp-border-glow);
    --text-pri:    var(--mgp-text-pri);
    --text-sec:    var(--mgp-text-sec);
    --text-muted:  var(--mgp-text-muted);
}

/* ── LIGHT MODE (admin panel default) ──
   When admin panel is in light mode (no .dark-theme on html),
   profile adapts to a light surface so it blends with the panel.
──────────────────────────────────────── */
html:not(.dark-theme) .mgp-wrap {
    --bg-deep:     #f1f5f9;
    --bg-card:     #ffffff;
    --border:      #e2e8f0;
    --border-glow: rgba(147,51,234,.18);
    --text-pri:    #0f172a;
    --text-sec:    #334155;
    --text-muted:  #64748b;
}
html:not(.dark-theme) .mgp-wrap .glass {
    background: rgba(255,255,255,.88) !important;
    border-color: #e2e8f0 !important;
}
html:not(.dark-theme) .mgp-wrap .glass:hover {
    box-shadow: 0 0 20px rgba(147,51,234,.1) !important;
}
html:not(.dark-theme) .mgp-wrap .mgp-hero {
    background: linear-gradient(135deg,#f8f0ff 0%,#f1f5f9 55%,#faf0ff 100%) !important;
    border-color: #e9d5ff !important;
}
html:not(.dark-theme) .mgp-wrap .kpi-card {
    background: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
html:not(.dark-theme) .mgp-wrap .kpi-val  { color: #0f172a !important; }
html:not(.dark-theme) .mgp-wrap .mgp-tabs { border-color: #e2e8f0 !important; }
html:not(.dark-theme) .mgp-wrap .mgp-tab  { color: #94a3b8; }
html:not(.dark-theme) .mgp-wrap .mgp-tab:hover { color: #475569; }
html:not(.dark-theme) .mgp-wrap .mgp-input {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
html:not(.dark-theme) .mgp-wrap .mgp-input::placeholder { color: #94a3b8 !important; }
html:not(.dark-theme) .mgp-wrap .identity-card { background: #ffffff !important; border-color: #e2e8f0 !important; }
html:not(.dark-theme) .mgp-wrap .device-card   { background: #f8fafc !important; border-color: #e2e8f0 !important; }
html:not(.dark-theme) .mgp-wrap .timeline-item { border-color: #e2e8f0 !important; }
html:not(.dark-theme) .mgp-wrap .perm-row      { border-color: #f1f5f9 !important; }
html:not(.dark-theme) .mgp-wrap .mgp-blob { display: none; }
html:not(.dark-theme) .mgp-wrap .mgp-modal,
html:not(.dark-theme) .mgp-overlay .mgp-modal  { background: #ffffff !important; border-color: #e2e8f0 !important; }
html:not(.dark-theme) .mgp-wrap #mgp-toasts .toast { background: #ffffff !important; border-color: #e2e8f0 !important; color: #0f172a !important; }
html:not(.dark-theme) .mgp-wrap .tfa-slider    { background: #cbd5e1 !important; }
html:not(.dark-theme) .mgp-wrap .pw-seg        { background: #e2e8f0 !important; }

/* ─── WRAPPER ─── */
.mgp-wrap {
    background: var(--bg-deep);
    min-height: 100vh;
    font-family: 'Inter', 'Outfit', system-ui, sans-serif;
    color: var(--text-pri);
    padding: 1.5rem;
    position: relative;
    overflow-x: hidden;
    transition: background .3s, color .3s;
}

/* Ambient glow blobs */
.mgp-blob {
    position: fixed; border-radius: 50%;
    filter: blur(130px); opacity: .10;
    pointer-events: none; z-index: 0;
}
.mgp-blob-red { width:520px;height:520px;background:var(--accent-red);top:-120px;right:-120px; }
.mgp-blob-pur { width:600px;height:600px;background:var(--accent-pur);bottom:-160px;left:-160px; }
.mgp-wrap > * { position:relative; z-index:1; }

/* ─── GLASS CARD ─── */
.glass {
    background: rgba(19,25,39,.82);
    border: 1px solid var(--border);
    border-radius: 16px;
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    transition: border-color .25s, box-shadow .25s;
}
.glass:hover { border-color: var(--border-glow); box-shadow: 0 0 28px rgba(147,51,234,.12); }

/* ─── KPI GRID ─── */
.kpi-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(4, 1fr);
    margin-bottom: 1.5rem;
}
@media(max-width:900px){ .kpi-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:480px){ .kpi-grid{ grid-template-columns:1fr 1fr; } }

.kpi-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.2rem 1.4rem;
    position: relative; overflow: hidden;
    transition: transform .22s, box-shadow .22s;
    cursor: default;
}
.kpi-card::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg,rgba(147,51,234,.07) 0%,transparent 60%);
    border-radius: inherit;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 34px rgba(147,51,234,.18); }
.kpi-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.7rem; }
.kpi-val  { font-size:1.5rem;font-weight:800;color:var(--text-pri);line-height:1; }
.kpi-lbl  { font-size:.68rem;font-weight:600;color:var(--text-muted);margin-top:.22rem;text-transform:uppercase;letter-spacing:.05em; }

/* ─── MAIN LAYOUT 8-4 ─── */
.mgp-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
}
/* Mobile: identity card goes on TOP */
@media(max-width:1100px){
    .mgp-layout { grid-template-columns:1fr; }
    .mgp-sidebar { order: -1; }
}

/* ─── TABS ─── */
.mgp-tabs {
    display: flex; gap: .2rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.mgp-tab {
    background: transparent; border: none; outline: none;
    color: var(--text-muted);
    padding: .65rem 1.1rem;
    font-size: .82rem; font-weight: 600;
    letter-spacing: .03em; cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
    white-space: nowrap;
    position: relative;
}
.mgp-tab:hover  { color: var(--text-sec); }
.mgp-tab:focus-visible { outline: 2px solid var(--accent-pur); outline-offset: -2px; border-radius: 4px; }
.mgp-tab[aria-selected="true"]  { color: var(--accent-pur); border-bottom-color: var(--accent-pur); }
.mgp-pane { display:none; }
.mgp-pane.active { display:block; animation: fadeIn .2s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

/* ─── FORMS ─── */
.mgp-label {
    display: block; font-size:.73rem; font-weight:700;
    color: var(--text-sec); margin-bottom:.4rem;
    text-transform:uppercase; letter-spacing:.05em;
}
.mgp-input {
    width:100%; background:#0d1120;
    border:1px solid var(--border); border-radius:10px;
    color:var(--text-pri); padding:.65rem 1rem; font-size:.88rem;
    transition: border-color .2s, box-shadow .2s; outline:none;
}
.mgp-input:focus { border-color:var(--accent-pur); box-shadow:0 0 0 3px rgba(147,51,234,.16); }
.mgp-input::placeholder { color:var(--text-muted); }
.mgp-input:disabled { opacity:.48; cursor:not-allowed; background:#0a0e18; }
.mgp-input.is-invalid { border-color:var(--accent-red); }
.field-err { color:var(--accent-red); font-size:.72rem; margin-top:.3rem; }
.mgp-field { margin-bottom:1.1rem; }

/* Password eye wrap */
.pw-wrap { position:relative; }
.pw-wrap .mgp-input { padding-right:2.8rem; }
.eye-btn {
    position:absolute; right:.85rem; top:50%; transform:translateY(-50%);
    background:none; border:none; color:var(--text-muted);
    cursor:pointer; padding:0; font-size:.95rem;
    transition: color .15s;
}
.eye-btn:hover { color:var(--text-pri); }
.eye-btn:focus-visible { outline:2px solid var(--accent-pur); border-radius:3px; }

/* ─── BUTTONS ─── */
.btn-primary {
    background: linear-gradient(135deg,var(--accent-pur),var(--accent-red));
    border:none; border-radius:10px; color:#fff;
    font-weight:700; font-size:.85rem; padding:.65rem 1.7rem;
    cursor:pointer; transition:opacity .2s,transform .15s,box-shadow .2s;
    letter-spacing:.03em; display:inline-flex; align-items:center; gap:.4rem;
    text-decoration:none;
}
.btn-primary:hover:not(:disabled) { opacity:.88; transform:translateY(-1px); box-shadow:0 6px 24px rgba(147,51,234,.35); }
.btn-primary:disabled { opacity:.5; cursor:not-allowed; }

.btn-danger {
    background:transparent; border:1.5px solid var(--accent-red);
    border-radius:10px; color:var(--accent-red);
    font-weight:700; font-size:.85rem; padding:.6rem 1.4rem;
    cursor:pointer; transition:background .2s,box-shadow .2s;
    display:inline-flex; align-items:center; gap:.4rem;
}
.btn-danger:hover { background:rgba(239,68,68,.1); box-shadow:0 0 18px rgba(239,68,68,.28); }

.btn-ghost {
    background:rgba(255,255,255,.05); border:1px solid var(--border);
    border-radius:10px; color:var(--text-sec);
    font-size:.8rem; font-weight:600; padding:.5rem 1.1rem;
    cursor:pointer; transition:background .18s;
    display:inline-flex; align-items:center; gap:.35rem;
}
.btn-ghost:hover { background:rgba(255,255,255,.1); }
.btn-ghost:focus-visible { outline:2px solid var(--accent-pur); }

/* ─── BADGES ─── */
.role-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.28rem .8rem; border-radius:100px;
    font-size:.7rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
}
.role-badge.admin    { background:rgba(147,51,234,.18); color:#c084fc; border:1px solid rgba(147,51,234,.4); }
.role-badge.manager  { background:rgba(16,185,129,.14); color:#34d399; border:1px solid rgba(16,185,129,.35); }
.role-badge.staff    { background:rgba(245,158,11,.14); color:#fbbf24; border:1px solid rgba(245,158,11,.35); }
.role-badge.customer { background:rgba(239,68,68,.14);  color:#f87171; border:1px solid rgba(239,68,68,.35); }

/* ─── HERO BANNER ─── */
.mgp-hero {
    background: linear-gradient(135deg,#1a0a2e 0%,#0f172a 55%,#170a2e 100%);
    border:1px solid var(--border); border-radius:16px;
    padding:2rem; margin-bottom:1.5rem;
    position:relative; overflow:hidden;
}
.mgp-hero::before {
    content:'';
    position:absolute; width:340px; height:340px;
    background:radial-gradient(circle,rgba(147,51,234,.22) 0%,transparent 70%);
    right:-60px; top:-60px; border-radius:50%;
}

/* ─── STATUS DOT ─── */
.status-dot {
    width:9px; height:9px; border-radius:50%;
    background:var(--success); display:inline-block;
    box-shadow:0 0 7px var(--success);
    animation:pulse-dot 2s infinite;
}
@keyframes pulse-dot { 0%,100%{box-shadow:0 0 5px var(--success)} 50%{box-shadow:0 0 14px var(--success)} }

/* ─── IDENTITY CARD ─── */
.identity-card { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; overflow:hidden; margin-bottom:1.25rem; }
.identity-banner { height:72px; background:linear-gradient(135deg,var(--accent-pur),var(--accent-red)); position:relative; }
.identity-banner::after {
    content:''; position:absolute; inset:0;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='200' cy='20' r='120' fill='white' opacity='.05'/%3E%3C/svg%3E");
}
.identity-body { padding:1.25rem; padding-top:0; }

/* Avatar */
.avatar-ring {
    width:88px; height:88px; border-radius:50%;
    background:linear-gradient(135deg,var(--accent-pur),var(--accent-red));
    padding:3px; display:inline-block;
    box-shadow:0 0 22px rgba(147,51,234,.45);
    cursor:pointer; position:relative; margin-top:-44px;
}
.avatar-inner {
    width:100%; height:100%; border-radius:50%;
    background:var(--bg-card);
    display:flex; align-items:center; justify-content:center;
    font-size:2rem; font-weight:800; color:#fff; overflow:hidden;
}
.avatar-inner img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.avatar-overlay {
    position:absolute; inset:0; border-radius:50%;
    background:rgba(0,0,0,.52);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    opacity:0; transition:opacity .2s;
    font-size:.62rem; color:#fff; font-weight:700; gap:.15rem;
}
.avatar-ring:hover .avatar-overlay { opacity:1; }
.avatar-ring:focus-visible { outline:3px solid var(--accent-pur); outline-offset:2px; }

/* ─── PRIVILEGE ITEM ─── */
.priv-item { display:flex; align-items:center; gap:.55rem; padding:.45rem; border-radius:8px; font-size:.79rem; color:var(--text-sec); margin-bottom:.2rem; }
.priv-item .pi { color:var(--accent-pur); width:14px; text-align:center; }

/* ─── LOYALTY CARD ─── */
.loyalty-card {
    background:linear-gradient(135deg,#1a0a2e 0%,#2d1566 100%);
    border:1px solid rgba(147,51,234,.3); border-radius:14px;
    padding:1.2rem; margin-top:1rem; position:relative; overflow:hidden;
}
.loyalty-card::before {
    content:''; position:absolute;
    width:180px; height:180px; border-radius:50%;
    background:rgba(147,51,234,.14); right:-50px; top:-50px;
}
.loyalty-bar { height:5px; border-radius:100px; background:var(--border); overflow:hidden; margin-top:.5rem; }
.loyalty-fill { height:100%; border-radius:100px; background:linear-gradient(90deg,var(--accent-pur),var(--accent-pink)); transition:width .9s ease; }

/* ─── TIMELINE ─── */
.timeline-item { display:flex; gap:1rem; padding:.9rem 0; border-bottom:1px solid var(--border); }
.timeline-item:last-child { border-bottom:none; }
.tl-dot { width:36px; height:36px; min-width:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.88rem; }

/* ─── DEVICE CARD ─── */
.device-card { background:#0d1120; border:1px solid var(--border); border-radius:12px; padding:.9rem 1.2rem; display:flex; align-items:center; gap:.9rem; margin-bottom:.75rem; }

/* ─── PERM ROW ─── */
.perm-row { display:flex; align-items:center; justify-content:space-between; padding:.55rem 0; border-bottom:1px solid #1a2236; font-size:.8rem; }
.perm-row:last-child { border-bottom:none; }

/* ─── 2FA TOGGLE ─── */
.tfa-toggle { position:relative; width:46px; height:24px; flex-shrink:0; }
.tfa-toggle input { opacity:0; width:0; height:0; }
.tfa-slider { position:absolute; inset:0; background:var(--border); border-radius:100px; cursor:pointer; transition:background .2s; }
.tfa-slider::before { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; left:3px; top:3px; transition:transform .2s; }
.tfa-toggle input:checked + .tfa-slider { background:var(--accent-pur); }
.tfa-toggle input:checked + .tfa-slider::before { transform:translateX(22px); }

/* ─── PW STRENGTH ─── */
.pw-str-bar { display:flex; gap:4px; margin-top:.5rem; }
.pw-seg { height:4px; flex:1; border-radius:100px; background:var(--border); transition:background .25s; }
.pw-seg.weak   { background:var(--accent-red); }
.pw-seg.medium { background:var(--warn); }
.pw-seg.strong { background:var(--success); }

/* ─── MODAL ─── */
.mgp-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.72);
    backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
    z-index:9999; display:flex; align-items:center; justify-content:center;
    opacity:0; pointer-events:none; transition:opacity .28s;
}
.mgp-overlay.open { opacity:1; pointer-events:auto; }
.mgp-modal {
    background:var(--bg-card); border:1px solid var(--border-glow);
    border-radius:20px; padding:2rem; width:90%;
    box-shadow:0 24px 70px rgba(0,0,0,.7);
    transform:scale(.9); transition:transform .28s;
    max-height:90vh; overflow-y:auto;
}
.mgp-overlay.open .mgp-modal { transform:scale(1); }
.mgp-modal-qr { max-width:360px; text-align:center; }
.mgp-modal-del { max-width:420px; border-color:rgba(239,68,68,.35); }

/* ─── TOAST ─── */
#mgp-toasts {
    position:fixed; bottom:24px; right:24px; z-index:10000;
    display:flex; flex-direction:column; gap:.55rem; pointer-events:none;
}
.toast {
    background:var(--bg-card); border:1px solid var(--border);
    border-left:3px solid var(--success);
    border-radius:12px; padding:.8rem 1.2rem;
    color:var(--text-pri); font-size:.8rem; font-weight:600;
    display:flex; align-items:center; gap:.55rem; min-width:200px;
    box-shadow:0 8px 32px rgba(0,0,0,.7);
    animation:toast-in .28s ease; pointer-events:auto;
}
.toast.error { border-left-color:var(--accent-red); }
@keyframes toast-in { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:none} }

/* ─── SECTION HEADINGS ─── */
.sec-hd {
    font-size:.72rem; font-weight:700; color:var(--text-muted);
    letter-spacing:.1em; text-transform:uppercase; margin-bottom:1.2rem;
    display:flex; align-items:center; gap:.5rem;
}
.sec-hd i { color:var(--accent-pur); }

/* ─── LOADING SPINNER ─── */
.spinner { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ─── SCROLLBAR ─── */
::-webkit-scrollbar { width:5px; }
::-webkit-scrollbar-thumb { background:var(--border); border-radius:100px; }

/* ─── READABLE BADGE ─── */
.readonly-badge { background:rgba(100,116,139,.15); color:var(--text-muted); border:1px solid rgba(100,116,139,.3); border-radius:100px; font-size:.62rem; font-weight:700; padding:.1rem .5rem; letter-spacing:.04em; }
</style>

@if($isAdmin || $isManager || $isStaff)
    @endsection
@else
    @endpush
@endif

@section('content')
<div class="mgp-wrap">
    {{-- Ambient glow blobs (customer full page only) --}}
    @if($isCustomer)
    <div class="mgp-blob mgp-blob-red" aria-hidden="true"></div>
    <div class="mgp-blob mgp-blob-pur" aria-hidden="true"></div>
    @endif

    {{-- Session toast triggers --}}
    @if(session('status') === 'profile-updated')
        <div id="js-ok-profile" data-msg="✅ Cập nhật thông tin thành công!" hidden></div>
    @endif
    @if(session('status') === 'password-updated')
        <div id="js-ok-password" data-msg="🔐 Mật khẩu đã được đổi thành công!" hidden></div>
    @endif

    {{-- ════ HERO BANNER ════ --}}
    <div class="mgp-hero mb-4" role="banner">
        <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                    <span style="font-size:1.4rem;">🎬</span>
                    <span style="font-size:.73rem;font-weight:700;color:var(--text-muted);letter-spacing:.1em;text-transform:uppercase;">movieGo — Hồ Sơ Cá Nhân</span>
                </div>
                <h1 style="font-size:1.65rem;font-weight:800;margin:0 0 .5rem;color:var(--text-pri);">Xin chào, {{ $user->name }}!</h1>
                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                    <span class="status-dot" title="Đang hoạt động" aria-label="Trạng thái: Đang hoạt động"></span>
                    <span style="font-size:.78rem;color:var(--text-sec);">Đang hoạt động</span>
                    <span aria-hidden="true" style="color:var(--border);">·</span>
                    <span class="role-badge {{ $roleKey }}">
                        @if($isAdmin) 🛡️ ADMIN @elseif($isManager) 🏢 MANAGER @elseif($isStaff) 🎟️ STAFF @else 🍿 KHÁCH HÀNG @endif
                    </span>
                </div>
            </div>
            <div>
                @if($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="btn-primary" style="font-size:.82rem;"><i class="fas fa-shield-alt" aria-hidden="true"></i> Trang Admin</a>
                @elseif($isStaff)
                    <a href="#" class="btn-primary" style="font-size:.82rem;"><i class="fas fa-cash-register" aria-hidden="true"></i> Bán Vé POS</a>
                @elseif($isCustomer)
                    <a href="{{ url('/movies') }}" class="btn-primary" style="font-size:.82rem;"><i class="fas fa-ticket-alt" aria-hidden="true"></i> Đặt Vé Mới</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ════ KPI GRID ════ --}}
    <div class="kpi-grid" role="region" aria-label="Thống kê nhanh">
        @if($isAdmin)
            @php $kpis = [
                ['bg'=>'rgba(147,51,234,.14)','color'=>'#c084fc','icon'=>'fa-chart-line','val'=>'4.2 Tỷ','lbl'=>'Doanh Thu HT'],
                ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-film',      'val'=> \App\Models\Movie::count(),'lbl'=>'Phim Quản Lý'],
                ['bg'=>'rgba(239,68,68,.14)',  'color'=>'#f87171','icon'=>'fa-ticket-alt','val'=>'1,284','lbl'=>'Vé Hôm Nay'],
                ['bg'=>'rgba(16,185,129,.14)', 'color'=>'#34d399','icon'=>'fa-building',  'val'=>'12',   'lbl'=>'Cụm Rạp Active'],
            ]; @endphp
        @elseif($isManager)
            @php $kpis = [
                ['bg'=>'rgba(16,185,129,.14)', 'color'=>'#34d399','icon'=>'fa-money-bill-wave','val'=>'186M','lbl'=>'Doanh Thu Rạp'],
                ['bg'=>'rgba(147,51,234,.14)', 'color'=>'#c084fc','icon'=>'fa-tv',             'val'=>'6',   'lbl'=>'Phòng Chiếu'],
                ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-cookie-bite',    'val'=>'432', 'lbl'=>'Combo Bán Ra'],
                ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-users',          'val'=>'18',  'lbl'=>'NV Trong Ca'],
            ]; @endphp
        @elseif($isStaff)
            @php $kpis = [
                ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-qrcode',        'val'=>'127', 'lbl'=>'Vé Đã Quét'],
                ['bg'=>'rgba(16,185,129,.14)', 'color'=>'#34d399','icon'=>'fa-clock',         'val'=>'8h',  'lbl'=>'Ca Làm Việc'],
                ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-cash-register', 'val'=>'3.2M','lbl'=>'Doanh Số Quầy'],
                ['bg'=>'rgba(239,68,68,.14)',  'color'=>'#f87171','icon'=>'fa-star',          'val'=>'4.8★','lbl'=>'Đánh Giá'],
            ]; @endphp
        @else
            @php $kpis = [
                ['bg'=>'rgba(147,51,234,.14)', 'color'=>'#c084fc','icon'=>'fa-coins',     'val'=>number_format($user->loyalty_points ?? 0),'lbl'=>'Điểm Tích Lũy'],
                ['bg'=>'rgba(239,68,68,.14)',  'color'=>'#f87171','icon'=>'fa-ticket-alt','val'=>'24',  'lbl'=>'Vé Đã Đặt'],
                ['bg'=>'rgba(16,185,129,.14)', 'color'=>'#34d399','icon'=>'fa-tags',      'val'=>'3',   'lbl'=>'Voucher'],
                ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-cookie-bite','val'=>'11', 'lbl'=>'Combo Đã Dùng'],
            ]; @endphp
        @endif

        @foreach($kpis as $k)
        <article class="kpi-card">
            <div class="kpi-icon" style="background:{{ $k['bg'] }};color:{{ $k['color'] }};" aria-hidden="true"><i class="fas {{ $k['icon'] }}"></i></div>
            <div class="kpi-val">{{ $k['val'] }}</div>
            <div class="kpi-lbl">{{ $k['lbl'] }}</div>
        </article>
        @endforeach
    </div>

    {{-- ════ MAIN 8-4 LAYOUT ════ --}}
    <div class="mgp-layout">

        {{-- ── LEFT: TABS ── --}}
        <main>
            {{-- Tab Navigation --}}
            <div class="mgp-tabs" role="tablist" aria-label="Điều hướng hồ sơ">
                <button class="mgp-tab" id="tab-btn-info" role="tab" aria-selected="true" aria-controls="tab-info" data-tab="info">
                    <i class="fas fa-user me-1" aria-hidden="true"></i> Thông Tin
                </button>
                <button class="mgp-tab" id="tab-btn-security" role="tab" aria-selected="false" aria-controls="tab-security" data-tab="security">
                    <i class="fas fa-shield-alt me-1" aria-hidden="true"></i> Bảo Mật &amp; 2FA
                </button>
                <button class="mgp-tab" id="tab-btn-logs" role="tab" aria-selected="false" aria-controls="tab-logs" data-tab="logs">
                    <i class="fas fa-history me-1" aria-hidden="true"></i>
                    @if($isCustomer) Lịch Sử Vé @else Nhật Ký @endif
                </button>
                <button class="mgp-tab" id="tab-btn-devices" role="tab" aria-selected="false" aria-controls="tab-devices" data-tab="devices">
                    <i class="fas fa-laptop me-1" aria-hidden="true"></i> Thiết Bị &amp; Quyền
                </button>
            </div>

            {{-- ─ TAB 1: THÔNG TIN ─ --}}
            <div class="glass p-4 mgp-pane active" id="tab-info" role="tabpanel" aria-labelledby="tab-btn-info">
                <p class="sec-hd"><i class="fas fa-user-edit" aria-hidden="true"></i> Chỉnh Sửa Thông Tin Cá Nhân</p>
                <form method="post" action="{{ route('profile.update') }}" id="profileForm" novalidate>
                    @csrf @method('patch')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mgp-field">
                            <label class="mgp-label" for="name">Họ và Tên <span style="color:var(--accent-red);">*</span></label>
                            <input type="text" class="mgp-input @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}"
                                   required autocomplete="name" minlength="2" maxlength="100"
                                   aria-describedby="name-err">
                            @error('name')<div class="field-err" id="name-err" role="alert">{{ $message }}</div>@enderror
                        </div>
                        <div class="mgp-field">
                            <label class="mgp-label" for="phone">
                                Số Điện Thoại
                                <span style="margin-left:.3rem;background:rgba(16,185,129,.14);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:100px;padding:.08rem .5rem;font-size:.62rem;">Đã xác thực</span>
                            </label>
                            <input type="tel" class="mgp-input @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   autocomplete="tel" placeholder="0912345678"
                                   aria-describedby="phone-err phone-hint">
                            <div id="phone-hint" style="font-size:.69rem;color:var(--text-muted);margin-top:.25rem;">Định dạng: 0xxxxxxxxx (10 số)</div>
                            @error('phone')<div class="field-err" id="phone-err" role="alert">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mgp-field">
                        <label class="mgp-label" for="email">
                            Địa Chỉ Email
                            <span style="margin-left:.3rem;background:rgba(147,51,234,.1);color:#c084fc;border:1px solid rgba(147,51,234,.3);border-radius:100px;padding:.08rem .5rem;font-size:.62rem;">🔒 Đóng băng bảo mật</span>
                        </label>
                        <input type="email" class="mgp-input" id="email"
                               value="{{ $user->email }}" disabled
                               aria-describedby="email-note">
                        <div id="email-note" style="font-size:.69rem;color:var(--text-muted);margin-top:.25rem;">
                            <i class="fas fa-info-circle" aria-hidden="true"></i> Email không thể thay đổi trực tiếp. Liên hệ quản trị viên nếu cần đổi.
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mgp-field">
                            <label class="mgp-label" for="dob">Ngày Sinh</label>
                            <input type="date" class="mgp-input @error('dob') is-invalid @enderror"
                                   id="dob" name="dob"
                                   value="{{ old('dob', $user->dob ?? '') }}"
                                   max="{{ date('Y-m-d') }}"
                                   aria-describedby="dob-err">
                            @error('dob')<div class="field-err" id="dob-err" role="alert">{{ $message }}</div>@enderror
                        </div>
                        <div class="mgp-field">
                            <label class="mgp-label" for="gender">Giới Tính</label>
                            <select class="mgp-input" id="gender" name="gender">
                                <option value="">-- Chọn --</option>
                                <option value="male"   {{ ($user->gender ?? '') == 'male'   ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ ($user->gender ?? '') == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other"  {{ ($user->gender ?? '') == 'other'  ? 'selected' : '' }}>Khác</option>
                            </select>
                        </div>
                    </div>
                    @if($user->cinema)
                    <div class="mgp-field">
                        <label class="mgp-label">Chi Nhánh Rạp</label>
                        <input type="text" class="mgp-input" value="{{ $user->cinema->name }}" disabled>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:flex-end;margin-top:.75rem;">
                        <button type="submit" class="btn-primary" id="profileSubmitBtn">
                            <i class="fas fa-save" aria-hidden="true"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─ TAB 2: BẢO MẬT & 2FA ─ --}}
            <div class="glass p-4 mgp-pane" id="tab-security" role="tabpanel" aria-labelledby="tab-btn-security" hidden>
                <p class="sec-hd"><i class="fas fa-shield-alt" aria-hidden="true"></i> Bảo Mật &amp; Xác Thực Hai Yếu Tố</p>

                {{-- 2FA --}}
                <div class="glass p-3 mb-4" style="background:rgba(147,51,234,.05);">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                        <div>
                            <div style="font-weight:700;font-size:.88rem;display:flex;align-items:center;gap:.4rem;">
                                <i class="fas fa-mobile-alt" style="color:#c084fc;" aria-hidden="true"></i> Xác Thực 2 Yếu Tố (2FA)
                            </div>
                            <div style="font-size:.76rem;color:var(--text-muted);margin-top:.25rem;">Thêm lớp bảo vệ với Google Authenticator hoặc Authy.</div>
                        </div>
                        <label class="tfa-toggle" aria-label="Bật/Tắt xác thực 2 yếu tố">
                            <input type="checkbox" id="tfa-check" aria-describedby="tfa-desc">
                            <span class="tfa-slider"></span>
                        </label>
                    </div>
                    <div id="tfa-desc" style="display:none;margin-top:.75rem;font-size:.76rem;color:#c084fc;background:rgba(147,51,234,.08);padding:.6rem .8rem;border-radius:8px;">
                        <i class="fas fa-info-circle" aria-hidden="true"></i> Quét mã QR bằng ứng dụng Authenticator. Lưu mã backup ở nơi an toàn.
                    </div>
                </div>

                {{-- Change Password --}}
                <p class="sec-hd"><i class="fas fa-key" aria-hidden="true"></i> Đổi Mật Khẩu</p>
                <form method="post" action="{{ route('password.update') }}" id="passwordForm" novalidate>
                    @csrf @method('put')
                    <div class="mgp-field">
                        <label class="mgp-label" for="current_password">Mật Khẩu Hiện Tại <span style="color:var(--accent-red);">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" class="mgp-input @error('current_password','updatePassword') is-invalid @enderror"
                                   id="current_password" name="current_password"
                                   autocomplete="current-password" required
                                   aria-describedby="cur-pw-err">
                            <button type="button" class="eye-btn" aria-label="Ẩn/hiện mật khẩu" onclick="togglePw('current_password',this)">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('current_password','updatePassword')
                            <div class="field-err" id="cur-pw-err" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mgp-field">
                        <label class="mgp-label" for="new_password">Mật Khẩu Mới <span style="color:var(--accent-red);">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" class="mgp-input @error('password','updatePassword') is-invalid @enderror"
                                   id="new_password" name="password"
                                   autocomplete="new-password" required minlength="8"
                                   aria-describedby="new-pw-err pw-str-label"
                                   oninput="checkPwStrength(this.value)">
                            <button type="button" class="eye-btn" aria-label="Ẩn/hiện mật khẩu" onclick="togglePw('new_password',this)">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="pw-str-bar" aria-hidden="true"><div class="pw-seg" id="ps1"></div><div class="pw-seg" id="ps2"></div><div class="pw-seg" id="ps3"></div></div>
                        <div id="pw-str-label" role="status" aria-live="polite" style="font-size:.7rem;color:var(--text-muted);margin-top:.28rem;min-height:1em;"></div>
                        @error('password','updatePassword')
                            <div class="field-err" id="new-pw-err" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mgp-field">
                        <label class="mgp-label" for="pw_confirm">Xác Nhận Mật Khẩu Mới <span style="color:var(--accent-red);">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" class="mgp-input @error('password_confirmation','updatePassword') is-invalid @enderror"
                                   id="pw_confirm" name="password_confirmation"
                                   autocomplete="new-password" required
                                   aria-describedby="pw-conf-err">
                            <button type="button" class="eye-btn" aria-label="Ẩn/hiện mật khẩu" onclick="togglePw('pw_confirm',this)">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password_confirmation','updatePassword')
                            <div class="field-err" id="pw-conf-err" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                        <input type="checkbox" id="logout-others" name="logout_others" value="1" style="accent-color:var(--accent-pur);width:14px;height:14px;">
                        <label for="logout-others" style="font-size:.8rem;color:var(--text-sec);cursor:pointer;">Đồng thời đăng xuất khỏi tất cả thiết bị khác</label>
                    </div>
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn-primary" id="pwSubmitBtn">
                            <i class="fas fa-lock" aria-hidden="true"></i> Cập Nhật Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─ TAB 3: NHẬT KÝ / LỊCH SỬ ─ --}}
            <div class="glass p-4 mgp-pane" id="tab-logs" role="tabpanel" aria-labelledby="tab-btn-logs" hidden>
                <p class="sec-hd">
                    <i class="fas fa-history" aria-hidden="true"></i>
                    @if($isCustomer) Lịch Sử Vé &amp; Hoạt Động @else Nhật Ký Thao Tác @endif
                </p>
                @php
                $logs = $isAdmin ? [
                    ['bg'=>'rgba(147,51,234,.14)','color'=>'#c084fc','icon'=>'fa-calendar-plus','action'=>'Tạo lịch chiếu mới','desc'=>'Phim Avengers: Secret Wars — Rạp Cầu Giấy','time'=>'Hôm nay, 09:15','badge'=>'Thành công','bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                    ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-undo',       'action'=>'Duyệt hoàn tiền',   'desc'=>'Đơn #VE2024-8821 — Nguyễn Văn A',           'time'=>'Hôm nay, 08:40','badge'=>'Đã duyệt','bc'=>'rgba(59,130,246,.14)','bcc'=>'#60a5fa'],
                    ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-tags',       'action'=>'Cấu hình giá vé',   'desc'=>'IMAX Weekend +15%',                         'time'=>'Hôm qua, 17:00','badge'=>'Đã lưu',  'bc'=>'rgba(245,158,11,.14)','bcc'=>'#fbbf24'],
                ] : ($isManager ? [
                    ['bg'=>'rgba(16,185,129,.14)', 'color'=>'#34d399','icon'=>'fa-user-clock', 'action'=>'Phân ca nhân viên', 'desc'=>'Ca sáng 8 người, Ca chiều 10 người',        'time'=>'Hôm nay, 07:30','badge'=>'Đã phân', 'bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                    ['bg'=>'rgba(147,51,234,.14)', 'color'=>'#c084fc','icon'=>'fa-tools',      'action'=>'Lịch bảo trì',     'desc'=>'Phòng chiếu 3 — 26/07/2026',                'time'=>'Hôm nay, 11:00','badge'=>'Lên lịch','bc'=>'rgba(245,158,11,.14)','bcc'=>'#fbbf24'],
                    ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-boxes',      'action'=>'Nhập kho combo',    'desc'=>'Popcorn x200, Nước ngọt x300',              'time'=>'Hôm qua, 16:20','badge'=>'Hoàn tất','bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                ] : ($isStaff ? [
                    ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-qrcode',     'action'=>'Quét QR vé',       'desc'=>'Avengers: SW — Phòng 2 — Ghế B12',         'time'=>'Hôm nay, 09:05','badge'=>'Hợp lệ',  'bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                    ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-receipt',    'action'=>'In hóa đơn POS',   'desc'=>'Combo Lớn + 2 vé 2D — 285,000đ',           'time'=>'Hôm nay, 08:50','badge'=>'Xong',    'bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                    ['bg'=>'rgba(239,68,68,.14)',  'color'=>'#f87171','icon'=>'fa-ticket-alt', 'action'=>'Bán vé walk-in',   'desc'=>'Lớp Ma 2 — 1 vé IMAX — 120,000đ',         'time'=>'Hôm nay, 08:20','badge'=>'Thành công','bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399'],
                ] : [
                    ['bg'=>'rgba(147,51,234,.14)','color'=>'#c084fc','icon'=>'fa-ticket-alt','action'=>'Đặt vé — Avengers: Secret Wars','desc'=>'Rạp Cầu Giấy · IMAX · Ghế D8,D9 · 240,000đ','time'=>'Hôm nay, 10:30','badge'=>'Đã thanh toán','bc'=>'rgba(16,185,129,.14)','bcc'=>'#34d399','qr'=>true],
                    ['bg'=>'rgba(59,130,246,.14)', 'color'=>'#60a5fa','icon'=>'fa-ticket-alt','action'=>'Đặt vé — Deadpool & Wolverine','desc'=>'Rạp Hà Đông · Phòng 3 · Ghế F12 · 90,000đ',  'time'=>'20/07/2026',    'badge'=>'Đã xem',      'bc'=>'rgba(100,116,139,.14)','bcc'=>'#94a3b8','qr'=>true],
                    ['bg'=>'rgba(245,158,11,.14)', 'color'=>'#fbbf24','icon'=>'fa-coins',     'action'=>'Tích điểm thưởng', 'desc'=>'+150 điểm từ đơn #VE2026-0041',            'time'=>'18/07/2026',    'badge'=>'+150đ',       'bc'=>'rgba(245,158,11,.14)','bcc'=>'#fbbf24','qr'=>false],
                ]));
                @endphp
                <div role="list">
                @foreach($logs as $log)
                <div class="timeline-item" role="listitem">
                    <div class="tl-dot" style="background:{{ $log['bg'] }};color:{{ $log['color'] }};" aria-hidden="true">
                        <i class="fas {{ $log['icon'] }}"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.84rem;color:var(--text-pri);">{{ $log['action'] }}</div>
                        <div style="font-size:.76rem;color:var(--text-muted);margin:.2rem 0;">{{ $log['desc'] }}</div>
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-top:.3rem;">
                            <span style="font-size:.69rem;color:var(--text-muted);"><i class="fas fa-clock" aria-hidden="true"></i> {{ $log['time'] }}</span>
                            <span style="background:{{ $log['bc'] }};color:{{ $log['bcc'] }};border-radius:100px;padding:.1rem .55rem;font-size:.67rem;font-weight:700;" aria-label="Trạng thái: {{ $log['badge'] }}">{{ $log['badge'] }}</span>
                            @if(isset($log['qr']) && $log['qr'])
                                <button onclick="openQR('{{ addslashes($log['action']) }}')" class="btn-ghost" style="padding:.18rem .6rem;font-size:.69rem;" aria-label="Xem QR vé: {{ $log['action'] }}">
                                    <i class="fas fa-qrcode" aria-hidden="true"></i> Xem QR
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                </div>
            </div>

            {{-- ─ TAB 4: THIẾT BỊ & QUYỀN ─ --}}
            <div class="glass p-4 mgp-pane" id="tab-devices" role="tabpanel" aria-labelledby="tab-btn-devices" hidden>
                <p class="sec-hd"><i class="fas fa-laptop" aria-hidden="true"></i> Thiết Bị Đang Đăng Nhập</p>

                <div role="list">
                    <div class="device-card" role="listitem">
                        <div style="font-size:1.4rem;color:#c084fc;" aria-hidden="true"><i class="fas fa-desktop"></i></div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:.84rem;">Windows PC — Chrome 124</div>
                            <div style="font-size:.74rem;color:var(--text-muted);">Hà Nội, Việt Nam · <span style="color:var(--success);">● Đang hoạt động</span></div>
                        </div>
                        <span style="background:rgba(16,185,129,.14);color:#34d399;border:1px solid rgba(16,185,129,.35);border-radius:100px;padding:.18rem .65rem;font-size:.68rem;font-weight:700;">Thiết bị này</span>
                    </div>
                    <div class="device-card" role="listitem">
                        <div style="font-size:1.4rem;color:#60a5fa;" aria-hidden="true"><i class="fas fa-mobile-alt"></i></div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:.84rem;">iPhone 15 — Safari</div>
                            <div style="font-size:.74rem;color:var(--text-muted);">Hà Nội, Việt Nam · 2 giờ trước</div>
                        </div>
                        <button class="btn-ghost" style="font-size:.7rem;padding:.28rem .65rem;" onclick="showToast('Đã đăng xuất khỏi iPhone 15')">Đăng xuất</button>
                    </div>
                    <div class="device-card" role="listitem">
                        <div style="font-size:1.4rem;color:#fbbf24;" aria-hidden="true"><i class="fas fa-tablet-alt"></i></div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:.84rem;">iPad Pro — Safari</div>
                            <div style="font-size:.74rem;color:var(--text-muted);">TP. Hồ Chí Minh · 3 ngày trước</div>
                        </div>
                        <button class="btn-ghost" style="font-size:.7rem;padding:.28rem .65rem;" onclick="showToast('Đã đăng xuất khỏi iPad Pro')">Đăng xuất</button>
                    </div>
                </div>

                <div style="margin-top:.75rem;">
                    <button class="btn-danger" onclick="showToast('Đã đăng xuất khỏi tất cả thiết bị khác','error')">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Đăng xuất khỏi mọi thiết bị khác
                    </button>
                </div>

                {{-- Permissions --}}
                <p class="sec-hd" style="margin-top:1.75rem;">
                    <i class="fas fa-key" aria-hidden="true"></i>
                    Quyền Hạn Tài Khoản
                    <span class="readonly-badge" style="margin-left:.4rem;">Chỉ đọc</span>
                </p>
                <p style="font-size:.74rem;color:var(--text-muted);margin-bottom:1rem;">Đây là quyền hạn hiện tại của tài khoản bạn. Liên hệ quản trị viên để thay đổi.</p>
                @php
                $perms = $isAdmin ? [
                    ['Quản lý toàn bộ Phim & Lịch chiếu', true],
                    ['Quản lý Người dùng & Phân quyền', true],
                    ['Xem & Xuất báo cáo Doanh thu', true],
                    ['Duyệt Hoàn tiền & Voucher', true],
                    ['Cấu hình hệ thống', true],
                ] : ($isManager ? [
                    ['Quản lý Phim & Lịch chiếu Chi Nhánh', true],
                    ['Quản lý Nhân viên trong rạp', true],
                    ['Xem Báo cáo Doanh thu Chi Nhánh', true],
                    ['Nhập kho Bắp nước & Combo', true],
                    ['Truy cập Trang Admin', false],
                ] : ($isStaff ? [
                    ['Quét mã QR vé điện tử', true],
                    ['Bán vé Walk-in qua POS', true],
                    ['In hóa đơn', true],
                    ['Quản lý Phim & Lịch chiếu', false],
                    ['Xem Báo cáo Doanh thu', false],
                ] : [
                    ['Đặt vé trực tuyến', true],
                    ['Sử dụng Voucher & Điểm tích lũy', true],
                    ['Xem lịch sử giao dịch', true],
                    ['Truy cập Trang quản trị', false],
                    ['Quản lý Người dùng', false],
                ]));
                @endphp
                <div role="list" aria-label="Danh sách quyền hạn">
                @foreach($perms as [$pname, $has])
                <div class="perm-row" role="listitem">
                    <span style="color:var(--text-sec);font-size:.8rem;">{{ $pname }}</span>
                    @if($has)
                        <i class="fas fa-check-circle" style="color:var(--success);font-size:.95rem;" aria-label="Có quyền"></i>
                    @else
                        <i class="fas fa-times-circle" style="color:var(--text-muted);font-size:.95rem;" aria-label="Không có quyền"></i>
                    @endif
                </div>
                @endforeach
                </div>
            </div>
        </main>

        {{-- ── RIGHT: IDENTITY CARD (mobile = order -1 = shows FIRST) ── --}}
        <aside class="mgp-sidebar">
            {{-- Identity Card --}}
            <div class="identity-card mb-4" aria-label="Thẻ thông tin người dùng">
                <div class="identity-banner" aria-hidden="true"></div>
                <div class="identity-body">
                    <div style="text-align:center;margin-bottom:.75rem;">
                        <div class="avatar-ring" tabindex="0" role="button"
                             aria-label="Bấm để thay ảnh đại diện (xem trước, chưa lưu)"
                             onclick="document.getElementById('avatarInput').click()"
                             onkeydown="if(event.key==='Enter'||event.key===' ')document.getElementById('avatarInput').click()">
                            <div class="avatar-inner" id="avatarDisplay">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="avatar-overlay" aria-hidden="true">
                                <i class="fas fa-camera" style="font-size:.9rem;"></i>
                                <span>Xem trước</span>
                            </div>
                        </div>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp"
                               aria-hidden="true" style="display:none;" onchange="previewAvatar(this)">
                    </div>
                    <div style="text-align:center;margin-bottom:.75rem;">
                        <div style="font-weight:800;font-size:1.02rem;color:var(--text-pri);">{{ $user->name }}</div>
                        <div style="font-size:.73rem;color:var(--text-muted);margin:.2rem 0;">{{ $user->email }}</div>
                        <span class="role-badge {{ $roleKey }}" style="margin-top:.3rem;">
                            @if($isAdmin) 🛡️ Administrator @elseif($isManager) 🏢 Cinema Manager @elseif($isStaff) 🎟️ Staff Member @else 🍿 Customer @endif
                        </span>
                    </div>

                    <hr style="border-color:var(--border);margin:.9rem 0;">

                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);letter-spacing:.07em;text-transform:uppercase;margin-bottom:.7rem;">Thông tin</div>
                    <dl style="font-size:.78rem;color:var(--text-sec);">
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                            <i class="fas fa-phone" style="color:var(--accent-pur);width:14px;flex-shrink:0;" aria-hidden="true"></i>
                            <dt class="visually-hidden">Số điện thoại:</dt>
                            <dd style="margin:0;">{{ $user->phone ?? 'Chưa cập nhật' }}</dd>
                        </div>
                        @if($user->cinema)
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                            <i class="fas fa-building" style="color:var(--accent-pur);width:14px;flex-shrink:0;" aria-hidden="true"></i>
                            <dt class="visually-hidden">Chi nhánh:</dt>
                            <dd style="margin:0;">{{ $user->cinema->name }}</dd>
                        </div>
                        @endif
                        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
                            <i class="fas fa-calendar-alt" style="color:var(--accent-pur);width:14px;flex-shrink:0;" aria-hidden="true"></i>
                            <dt class="visually-hidden">Ngày tham gia:</dt>
                            <dd style="margin:0;">Tham gia {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</dd>
                        </div>
                        @if($isStaff)
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <i class="fas fa-id-badge" style="color:var(--accent-pur);width:14px;flex-shrink:0;" aria-hidden="true"></i>
                            <dt class="visually-hidden">Staff ID:</dt>
                            <dd style="margin:0;">STF-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        @endif
                    </dl>

                    <hr style="border-color:var(--border);margin:.9rem 0;">

                    <div style="font-size:.72rem;font-weight:700;color:var(--text-muted);letter-spacing:.07em;text-transform:uppercase;margin-bottom:.7rem;">Đặc Quyền Vai Trò</div>
                    @if($isAdmin)
                        <div class="priv-item"><i class="pi fas fa-globe"></i> Toàn hệ thống (HQ)</div>
                        <div class="priv-item"><i class="pi fas fa-crown"></i> Full Control</div>
                        <div class="priv-item"><i class="pi fas fa-infinity"></i> Không giới hạn</div>
                    @elseif($isManager)
                        <div class="priv-item"><i class="pi fas fa-building"></i> {{ $user->cinema->name ?? 'Chi nhánh' }}</div>
                        <div class="priv-item"><i class="pi fas fa-users-cog"></i> Quản lý nhân sự &amp; ca</div>
                        <div class="priv-item"><i class="pi fas fa-chart-bar"></i> Báo cáo chi nhánh</div>
                    @elseif($isStaff)
                        <div class="priv-item"><i class="pi fas fa-qrcode"></i> Soát vé &amp; POS</div>
                        <div class="priv-item"><i class="pi fas fa-print"></i> In hóa đơn</div>
                        <div class="priv-item"><i class="pi fas fa-cash-register"></i> Quản lý quầy</div>
                    @else
                        <div class="priv-item"><i class="pi fas fa-ticket-alt"></i> Đặt vé online</div>
                        <div class="priv-item"><i class="pi fas fa-coins"></i> Tích &amp; đổi điểm</div>
                        <div class="priv-item"><i class="pi fas fa-tags"></i> Dùng voucher</div>
                    @endif
                </div>
            </div>

            {{-- Loyalty Card (Customer only) --}}
            @if($isCustomer)
            @php $pct = min(100, round(($user->loyalty_points ?? 0) / 5000 * 100)); @endphp
            <div class="loyalty-card mb-4" role="region" aria-label="Thẻ tích điểm thành viên">
                <div style="position:relative;z-index:1;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
                        <div>
                            <div style="font-size:.62rem;font-weight:700;color:#c084fc;letter-spacing:.1em;text-transform:uppercase;">movieGo Loyalty</div>
                            <div style="font-weight:800;font-size:.95rem;color:#fff;margin-top:.1rem;">Thành Viên GOLD</div>
                        </div>
                        <span aria-hidden="true" style="font-size:1.4rem;">🥇</span>
                    </div>
                    <div style="font-size:.75rem;color:rgba(255,255,255,.6);">{{ number_format($user->loyalty_points ?? 0) }} / 5,000 điểm → Hạng PLATINUM</div>
                    <div class="loyalty-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Tiến trình tích điểm {{ $pct }}%">
                        <div class="loyalty-fill" style="width:{{ $pct }}%;"></div>
                    </div>
                    <div style="font-size:.68rem;color:rgba(255,255,255,.4);margin-top:.35rem;">{{ $pct }}% đến hạng tiếp theo</div>
                </div>
            </div>
            @endif

            {{-- Danger Zone --}}
            <div class="glass p-4" style="border-color:rgba(239,68,68,.28)!important;" role="region" aria-label="Khu vực nguy hiểm">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;">
                    <i class="fas fa-exclamation-triangle" style="color:var(--accent-red);" aria-hidden="true"></i>
                    <span style="font-weight:800;color:var(--accent-red);font-size:.88rem;">Khu Vực Nguy Hiểm</span>
                </div>
                <p style="font-size:.77rem;color:var(--text-muted);margin-bottom:1rem;">
                    Xóa tài khoản là hành động <strong style="color:var(--accent-red);">không thể hoàn tác</strong>. Tất cả dữ liệu sẽ bị xóa vĩnh viễn.
                </p>
                <button class="btn-danger" style="width:100%;justify-content:center;" onclick="openDelModal()">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Xóa Tài Khoản
                </button>
            </div>
        </aside>
    </div>
</div>

{{-- ════ QR MODAL ════ --}}
<div class="mgp-overlay" id="qrOverlay"
     role="dialog" aria-modal="true" aria-labelledby="qr-modal-title"
     onclick="if(event.target===this)closeQR()">
    <div class="mgp-modal mgp-modal-qr" id="qrModal">
        <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);letter-spacing:.1em;text-transform:uppercase;margin-bottom:.6rem;">
            <i class="fas fa-ticket-alt" style="color:#c084fc;" aria-hidden="true"></i> Vé Điện Tử movieGo
        </div>
        <div id="qr-modal-title" style="font-weight:800;color:var(--text-pri);font-size:.88rem;margin-bottom:1rem;"></div>
        <div style="background:#fff;border-radius:12px;padding:.9rem;display:inline-block;margin-bottom:1rem;" aria-label="Mã QR vé (mockup thị giác)">
            <svg width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <rect width="140" height="140" fill="white"/>
                <rect x="10" y="10" width="40" height="40" fill="black" rx="3"/><rect x="15" y="15" width="30" height="30" fill="white" rx="2"/><rect x="20" y="20" width="20" height="20" fill="black" rx="1"/>
                <rect x="90" y="10" width="40" height="40" fill="black" rx="3"/><rect x="95" y="15" width="30" height="30" fill="white" rx="2"/><rect x="100" y="20" width="20" height="20" fill="black" rx="1"/>
                <rect x="10" y="90" width="40" height="40" fill="black" rx="3"/><rect x="15" y="95" width="30" height="30" fill="white" rx="2"/><rect x="20" y="100" width="20" height="20" fill="black" rx="1"/>
                <rect x="58" y="10" width="8" height="8" fill="black"/><rect x="70" y="10" width="8" height="8" fill="black"/>
                <rect x="58" y="22" width="8" height="8" fill="black"/><rect x="74" y="58" width="8" height="8" fill="black"/>
                <rect x="58" y="58" width="8" height="8" fill="black"/><rect x="86" y="58" width="8" height="8" fill="black"/>
                <rect x="98" y="58" width="8" height="8" fill="black"/><rect x="110" y="58" width="8" height="8" fill="black"/>
                <rect x="58" y="70" width="8" height="8" fill="black"/><rect x="86" y="70" width="8" height="8" fill="black"/>
                <rect x="58" y="82" width="8" height="8" fill="black"/><rect x="74" y="82" width="8" height="8" fill="black"/>
                <rect x="98" y="82" width="8" height="8" fill="black"/><rect x="110" y="82" width="8" height="8" fill="black"/>
                <rect x="10" y="58" width="8" height="8" fill="black"/><rect x="22" y="58" width="8" height="8" fill="black"/>
                <rect x="34" y="58" width="8" height="8" fill="black"/><rect x="46" y="58" width="8" height="8" fill="black"/>
                <rect x="10" y="70" width="8" height="8" fill="black"/><rect x="34" y="70" width="8" height="8" fill="black"/>
                <rect x="22" y="82" width="8" height="8" fill="black"/><rect x="46" y="82" width="8" height="8" fill="black"/>
            </svg>
        </div>
        <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:1.2rem;">Mã QR mockup — xuất trình tại quầy soát vé</div>
        <button class="btn-ghost" onclick="closeQR()" style="width:100%;justify-content:center;" id="qr-close-btn">
            <i class="fas fa-times" aria-hidden="true"></i> Đóng
        </button>
    </div>
</div>

{{-- ════ DELETE MODAL ════ --}}
<div class="mgp-overlay" id="delOverlay"
     role="dialog" aria-modal="true" aria-labelledby="del-modal-title"
     onclick="if(event.target===this)closeDelModal()">
    <div class="mgp-modal mgp-modal-del" id="delModal">
        <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:1rem;">
            <div style="width:38px;height:38px;border-radius:10px;background:rgba(239,68,68,.14);display:flex;align-items:center;justify-content:center;color:var(--accent-red);font-size:1rem;" aria-hidden="true">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div id="del-modal-title" style="font-weight:800;color:var(--accent-red);font-size:.98rem;">Xác Nhận Xóa Tài Khoản</div>
        </div>
        <p style="font-size:.8rem;color:var(--text-sec);margin-bottom:1.2rem;">
            Hành động này <strong style="color:var(--accent-red);">không thể hoàn tác</strong>. Lịch sử vé, điểm tích lũy và toàn bộ dữ liệu sẽ bị xóa vĩnh viễn.
        </p>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf @method('delete')
            <div class="mgp-field">
                <label class="mgp-label" for="del_password">Nhập mật khẩu để xác nhận <span style="color:var(--accent-red);">*</span></label>
                <input type="password" class="mgp-input @error('password','userDeletion') is-invalid @enderror"
                       id="del_password" name="password" placeholder="Mật khẩu hiện tại" required
                       aria-describedby="del-pw-err">
                @error('password','userDeletion')
                    <div class="field-err" id="del-pw-err" role="alert">{{ $message }}</div>
                @enderror
            </div>
            <div style="display:flex;gap:.7rem;justify-content:flex-end;margin-top:.75rem;">
                <button type="button" class="btn-ghost" onclick="closeDelModal()" id="del-cancel-btn">Hủy bỏ</button>
                <button type="submit" class="btn-danger"><i class="fas fa-trash-alt" aria-hidden="true"></i> Xóa vĩnh viễn</button>
            </div>
        </form>
    </div>
</div>

{{-- ════ TOAST CONTAINER ════ --}}
<div id="mgp-toasts" aria-live="polite" aria-atomic="false"></div>

<script>
/* ═══════════════ TABS ═══════════════ */
const tabBtns = document.querySelectorAll('.mgp-tab');
const tabPanes = document.querySelectorAll('.mgp-pane');

tabBtns.forEach((btn, i) => {
    btn.addEventListener('click', () => activateTab(btn));
    btn.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') { tabBtns[(i+1) % tabBtns.length].focus(); }
        if (e.key === 'ArrowLeft')  { tabBtns[(i-1+tabBtns.length) % tabBtns.length].focus(); }
        if (e.key === 'Home') { tabBtns[0].focus(); }
        if (e.key === 'End')  { tabBtns[tabBtns.length-1].focus(); }
    });
});

function activateTab(btn) {
    tabBtns.forEach(b => { b.setAttribute('aria-selected','false'); });
    tabPanes.forEach(p => { p.classList.remove('active'); p.hidden = true; });
    btn.setAttribute('aria-selected','true');
    const pane = document.getElementById('tab-' + btn.dataset.tab);
    if (pane) { pane.classList.add('active'); pane.hidden = false; }
}

/* Open security tab if password error exists */
@if($errors->updatePassword->any())
    document.addEventListener('DOMContentLoaded', () => {
        const secBtn = document.querySelector('[data-tab="security"]');
        if (secBtn) activateTab(secBtn);
    });
@endif

/* ═══════════════ TOAST ═══════════════ */
function showToast(msg, type='') {
    const container = document.getElementById('mgp-toasts');
    const t = document.createElement('div');
    t.className = 'toast' + (type==='error' ? ' error' : '');
    const icon = type==='error' ? 'fa-exclamation-circle' : 'fa-check-circle';
    const color = type==='error' ? 'var(--accent-red)' : 'var(--success)';
    t.innerHTML = `<i class="fas ${icon}" style="color:${color}" aria-hidden="true"></i>${msg}`;
    container.appendChild(t);
    setTimeout(() => {
        t.style.transition = 'opacity .3s, transform .3s';
        t.style.opacity = '0'; t.style.transform = 'translateX(40px)';
        setTimeout(() => t.remove(), 320);
    }, 3800);
}

/* Auto-show session toasts */
window.addEventListener('DOMContentLoaded', () => {
    ['profile','password'].forEach(k => {
        const el = document.getElementById('js-ok-' + k);
        if (el) showToast(el.dataset.msg);
    });
});

/* ═══════════════ MODAL UTILS ═══════════════ */
function trapFocus(modal) {
    const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    const first = focusable[0], last = focusable[focusable.length-1];
    modal.addEventListener('keydown', function handle(e) {
        if (e.key !== 'Tab') return;
        if (e.shiftKey) { if (document.activeElement===first){ e.preventDefault(); last.focus(); } }
        else            { if (document.activeElement===last) { e.preventDefault(); first.focus(); } }
    });
}

/* ═══════════════ QR MODAL ═══════════════ */
let qrPrevFocus;
function openQR(title) {
    qrPrevFocus = document.activeElement;
    document.getElementById('qr-modal-title').textContent = title;
    const overlay = document.getElementById('qrOverlay');
    overlay.classList.add('open');
    document.getElementById('qr-close-btn').focus();
    trapFocus(document.getElementById('qrModal'));
}
function closeQR() {
    document.getElementById('qrOverlay').classList.remove('open');
    if (qrPrevFocus) qrPrevFocus.focus();
}

/* ═══════════════ DELETE MODAL ═══════════════ */
let delPrevFocus;
function openDelModal() {
    delPrevFocus = document.activeElement;
    const overlay = document.getElementById('delOverlay');
    overlay.classList.add('open');
    document.getElementById('del_password').focus();
    trapFocus(document.getElementById('delModal'));
}
function closeDelModal() {
    document.getElementById('delOverlay').classList.remove('open');
    if (delPrevFocus) delPrevFocus.focus();
}

/* ESC closes any open modal */
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (document.getElementById('qrOverlay').classList.contains('open')) closeQR();
    if (document.getElementById('delOverlay').classList.contains('open')) closeDelModal();
});

/* ═══════════════ PASSWORD STRENGTH ═══════════════ */
function checkPwStrength(val) {
    const segs = [document.getElementById('ps1'),document.getElementById('ps2'),document.getElementById('ps3')];
    const lbl = document.getElementById('pw-str-label');
    segs.forEach(s => s.className='pw-seg');
    if (!val) { lbl.textContent=''; return; }
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val) && val.length >= 12) score++;
    const info = [
        { cls:'weak',   label:'Mật khẩu yếu', color:'var(--accent-red)' },
        { cls:'medium', label:'Độ bảo mật trung bình', color:'var(--warn)' },
        { cls:'strong', label:'🔒 Mật khẩu cực mạnh', color:'var(--success)' },
    ];
    if (score > 0) {
        for (let i=0; i<score; i++) segs[i].classList.add(info[score-1].cls);
        lbl.textContent = info[score-1].label;
        lbl.style.color = info[score-1].color;
    }
}

/* ═══════════════ EYE TOGGLE ═══════════════ */
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    if (inp.type==='password') { inp.type='text';     btn.querySelector('i').className='fas fa-eye-slash'; btn.setAttribute('aria-label','Ẩn mật khẩu'); }
    else                       { inp.type='password'; btn.querySelector('i').className='fas fa-eye';       btn.setAttribute('aria-label','Hiện mật khẩu'); }
}

/* ═══════════════ AVATAR PREVIEW ═══════════════ */
function previewAvatar(inp) {
    if (!inp.files || !inp.files[0]) return;
    const file = inp.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('⚠️ Ảnh quá lớn! Tối đa 5MB.','error'); inp.value=''; return; }
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { showToast('⚠️ Chỉ hỗ trợ JPG, PNG, WebP.','error'); inp.value=''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        const d = document.getElementById('avatarDisplay');
        d.innerHTML = `<img src="${e.target.result}" alt="Ảnh đại diện xem trước">`;
    };
    reader.readAsDataURL(file);
    showToast('🖼️ Ảnh đại diện đã được cập nhật (xem trước — chưa lưu lên server)');
}

/* ═══════════════ 2FA TOGGLE ═══════════════ */
document.getElementById('tfa-check').addEventListener('change', function() {
    const msg = document.getElementById('tfa-desc');
    msg.style.display = this.checked ? 'block' : 'none';
    showToast(this.checked ? '🔐 Đã bật xác thực 2 yếu tố' : '⚠️ Đã tắt xác thực 2 yếu tố', this.checked?'':'error');
});

/* ═══════════════ FORM LOADING STATES ═══════════════ */
function setLoading(btn, loading) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.origText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner" aria-hidden="true"></span> Đang lưu…';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.origText || btn.innerHTML;
    }
}

/* Profile form validation */
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    let valid = true;

    if (name.length < 2) {
        document.querySelector('#profileForm #name').classList.add('is-invalid');
        showToast('⚠️ Họ tên phải có ít nhất 2 ký tự.','error'); valid=false;
    }
    if (phone && !/^(0|\+84)[0-9]{8,9}$/.test(phone)) {
        document.getElementById('phone').classList.add('is-invalid');
        showToast('⚠️ Số điện thoại không đúng định dạng.','error'); valid=false;
    }
    if (!valid) { e.preventDefault(); return; }
    setLoading(document.getElementById('profileSubmitBtn'), true);
});

/* Password form */
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('pw_confirm').value;
    if (np !== cp) {
        document.getElementById('pw_confirm').classList.add('is-invalid');
        showToast('⚠️ Mật khẩu xác nhận không khớp.','error');
        e.preventDefault(); return;
    }
    setLoading(document.getElementById('pwSubmitBtn'), true);
});

/* Clear invalid on input */
document.querySelectorAll('.mgp-input').forEach(inp => {
    inp.addEventListener('input', () => inp.classList.remove('is-invalid'));
});
</script>
@endsection
