<div class="human-check" data-human-check data-human-seed="{{ $humanSeed }}">
    <input type="hidden" name="human_token" value="">
    <label class="human-box">
        <input type="checkbox" data-human-tick>
        <span>I am not a robot</span>
    </label>
    <p class="hint" data-human-status>Tick the box so TSL knows a person is signing up.</p>
    <p class="human-trap" aria-hidden="true">
        <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </p>
    <noscript>
        <p class="hint">Turn on JavaScript in your browser to finish this check.</p>
    </noscript>
</div>
