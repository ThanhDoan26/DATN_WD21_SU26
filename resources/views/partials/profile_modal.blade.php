@php $user = auth()->user(); @endphp
<div id="profile-modal" class="fixed inset-0 hidden items-center justify-center" data-name="{{ e(optional(auth()->user())->name) }}" data-email="{{ e(optional(auth()->user())->email) }}" data-phone="{{ e(optional(auth()->user())->phone) }}" style="z-index:9998;">
    <div class="fixed inset-0 bg-black/60" id="profile-modal-backdrop" style="z-index:9998;"></div>
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-3xl mx-4 overflow-auto" role="dialog" aria-modal="true" style="position:relative;z-index:9999;pointer-events:auto;">
        <div class="p-4 flex items-start justify-between border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center text-xl font-semibold text-gray-700">
                    {{ strtoupper(substr($user->name ?? 'U',0,1)) }}
                </div>
                <div>
                    <div class="font-semibold text-lg text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    @if(!empty($user->phone))<div class="text-sm text-gray-500">Số điện thoại: {{ $user->phone }}</div>@endif
                </div>
            </div>
            <button id="profile-modal-close" class="text-gray-500 hover:text-gray-800">Đóng ✕</button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div id="profile-info" class="bg-transparent">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div id="change-password" class="bg-transparent">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>

<style>
/* Ensure modal sits above other fixed elements */
#profile-modal { display: none; }
#profile-modal.open { display: flex; }
#profile-modal .modal-inner { pointer-events:auto; }
</style>

<script>
// Ensure functions are available on window so inline onclick can call them
window.openProfileModal = function(tab) {
    const modal = document.getElementById('profile-modal');
    if (!modal) return console.warn('profile-modal not found in DOM');
    // Prefill inputs from server-provided data attributes
    try {
        const name = modal.dataset.name || '';
        const email = modal.dataset.email || '';
        const phone = modal.dataset.phone || '';
        const nameEl = document.getElementById('name');
        const emailEl = document.getElementById('email');
        if (nameEl) nameEl.value = name;
        if (emailEl) emailEl.value = email;
        // phone field may not be present in partials; set if exists
        const phoneEl = document.getElementById('phone');
        if (phoneEl) phoneEl.value = phone;
    } catch (err) {
        console.warn('Failed to prefill profile modal inputs', err);
    }
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) {
        const el = document.getElementById(tab);
        if (el) el.scrollIntoView({behavior: 'smooth'});
    }
}
window.closeProfileModal = function() {
    const modal = document.getElementById('profile-modal');
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('click', function(e){
    const backdrop = document.getElementById('profile-modal-backdrop');
    if (backdrop && e.target === backdrop) window.closeProfileModal();
});

document.addEventListener('DOMContentLoaded', function(){
    const closeBtn = document.getElementById('profile-modal-close');
    if (closeBtn) closeBtn.addEventListener('click', window.closeProfileModal);
});
</script>