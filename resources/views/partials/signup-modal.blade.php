<div class="modal" id="signup-modal" hidden>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-sheet" role="dialog" aria-modal="true" aria-labelledby="signup-title">
        @include('partials.paw', ['class' => 'paw paw-sheet'])
        <button type="button" class="modal-close" data-close-modal aria-label="Close">×</button>
        <img id="signup-image" src="{{ asset('images/locks/ts30.svg') }}" alt="">
        <p class="eyebrow" id="signup-kicker">Members only</p>
        <h2 id="signup-title">This lock is waiting for you</h2>
        <p class="lead" id="signup-body">Create a free TSL account first. Then this lock can start paying you every morning.</p>
        <ul class="perks">
            <li>Own a real Tuya smart lock</li>
            <li>Earn a daily amount on your lock</li>
            <li>Cash out any day (min 2,000 UGX · 6% fee)</li>
        </ul>
        <a id="signup-cta" class="btn btn-primary" href="{{ route('register') }}">Create my free account</a>
        <p class="fine">Already a member? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</div>
