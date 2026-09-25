@php $user = auth()->user(); @endphp
<h2 id="profile-title">Your profile</h2>
<p class="auth-lead">
    @if ($user->isAdmin())
        This name and photo appear on your account and every news post you publish.
    @else
        Set the name and photo on your account. You can sign in with this username or your phone.
    @endif
</p>
@if ($errors->profile->any())
    <p class="toast toast-err">{{ $errors->profile->first() }}</p>
@endif
<form method="POST" action="{{ route('account.profile') }}" enctype="multipart/form-data" class="auth-form">
    @csrf
    <div class="profile-row">
        @include('partials.avatar', ['person' => $user, 'size' => 'lg'])
        <label class="grow">
            <span>Username</span>
            <input class="field-pill" name="name" value="{{ old('name', $user->name === 'TSL member' ? '' : $user->name) }}" placeholder="Your name" required>
        </label>
    </div>
    <label>
        <span>Phone number</span>
        <input class="field-pill" name="phone" inputmode="tel" value="{{ old('phone', $user->phone) }}" placeholder="07XX XXX XXX">
    </label>
    <label>
        <span>Profile photo</span>
        <input class="file-input" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
        <small class="hint">JPG, PNG or WebP.</small>
    </label>
    <button class="btn btn-primary" type="submit">Save profile</button>
</form>
