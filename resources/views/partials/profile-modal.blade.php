@auth
<div class="modal" id="profile-modal" @unless ($errors->profile->isNotEmpty()) hidden @endunless>
    <div class="modal-backdrop" data-close-profile></div>
    <div class="profile-sheet" role="dialog" aria-modal="true" aria-labelledby="profile-title">
        <button type="button" class="modal-close profile-close" data-close-profile aria-label="Close">×</button>
        <div class="auth-card">
            @include('partials.profile-form')
        </div>
    </div>
</div>
@endauth
