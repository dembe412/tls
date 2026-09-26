@extends('layouts.app')

@section('title', 'Hero Slider Studio — Manager console')

@section('content')
<div class="studio-page-wrapper">
    <header class="page-head wide studio-head">
        <div class="head-actions" style="margin-bottom: 0.65rem;">
            <a class="text-link" href="{{ route('admin.index') }}">← Manager console</a>
            <a class="text-link" href="{{ route('account') }}">My account</a>
            <a class="text-link" href="{{ route('home') }}" target="_blank" rel="noopener">View live homepage ↗</a>
        </div>

        <p class="kicker">Homepage Dynamic Showcase</p>
        <h1>Hero <span>Slider</span> Studio</h1>
        <p class="sub" style="max-width: 60rem;">
            Organize your dynamic 2 to 4 banner slides. Images animate automatically from left to right on the live homepage.
        </p>

        {{-- Capacity and Dynamic Rule Dashboard (Visible High-Contrast Pills) --}}
        <div class="hero-studio-dashboard">
            <div class="studio-stat-pill">
                <span class="pill-dot {{ $activeCount >= 2 ? 'is-live' : 'is-warn' }}"></span>
                <span class="pill-text-primary">{{ $activeCount }} of 4 Slots Active</span>
            </div>

            <div class="studio-stat-pill">
                <span class="pill-tag">Requirement</span>
                <span class="pill-text-regular">Min 2 · Max 4 Slides</span>
            </div>

            <div class="studio-stat-pill">
                <span class="pill-tag">Motion</span>
                <span class="pill-text-regular">Left to Right (➔)</span>
            </div>

            <div class="studio-stat-pill">
                <span class="pill-tag">Speed</span>
                <span class="pill-text-regular">{{ $hero['slider_speed'] ?? 4 }}s Interval</span>
            </div>
        </div>
    </header>

    {{-- Mini Live Simulator --}}
    <section class="catalog" style="margin-top: 1.25rem; padding-left: 0; padding-right: 0;">
        <div class="studio-live-box">
            <div class="studio-live-head">
                <div class="live-indicator">
                    <span class="pulse-dot"></span>
                    <strong>Live Motion Simulator</strong>
                </div>
                <span class="muted" style="font-size: 0.82rem; color: #475569;">
                    How your active slides transition smoothly from left to right for visitors
                </span>
            </div>

            <div class="studio-sim-frame" id="admin-mini-slider" data-speed="{{ $hero['slider_speed'] ?? 4 }}">
                @foreach ($images->where('is_active', true) as $idx => $simSlide)
                    <div class="sim-slide {{ $idx === 0 ? 'is-active' : '' }}" data-sim-idx="{{ $idx }}">
                        <img src="{{ $simSlide->imageUrl() }}" alt="{{ $simSlide->title }}">
                        @if (!empty($simSlide->title))
                            <div class="sim-caption">
                                <span>{{ $simSlide->title }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="sim-dots">
                    @foreach ($images->where('is_active', true) as $idx => $simSlide)
                        <span class="sim-dot {{ $idx === 0 ? 'is-active' : '' }}"></span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 4-Slot Slide Deck Grid (Spreads Across Full Width) --}}
    <section class="catalog" style="margin-top: 2rem; padding-left: 0; padding-right: 0;">
        <div class="section-head" style="margin-bottom: 1.25rem;">
            <div>
                <h2>Hero Slide Deck (2 to 4 Images)</h2>
                <p class="muted" style="margin: 0.25rem 0 0 0; font-size: 0.88rem; color: #475569;">
                    Configure each slide slot directly. You can have between 2 and 4 images.
                </p>
            </div>
        </div>

        <div class="hero-slots-grid">
            @for ($slot = 1; $slot <= 4; $slot++)
                @php
                    $slide = $images->get($slot - 1);
                @endphp

                @if ($slide)
                    {{-- Filled Slot Card --}}
                    <div class="hero-slot-card is-filled">
                        <div class="slot-card-header">
                            <span class="slot-badge">SLOT 0{{ $slot }}</span>
                            <span class="pill {{ $slide->is_active ? 'pill-on' : '' }}">
                                {{ $slide->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </div>

                        <div class="slot-img-wrap">
                            <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title ?: 'Slide '.$slot }}" class="slot-thumb">
                            @if (!empty($slide->title))
                                <span class="slot-thumb-label">{{ $slide->title }}</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('admin.hero.images.update', $slide) }}" enctype="multipart/form-data" class="slot-form">
                            @csrf
                            @method('PUT')

                            <label>
                                <span class="label-text">Slide Title / Caption</span>
                                <input class="field-pill" name="title" value="{{ old('title', $slide->title) }}" placeholder="e.g. Tuya Smart Lock Pro" maxlength="120">
                            </label>

                            <label>
                                <span class="label-text">Click URL (Optional)</span>
                                <input class="field-pill" name="link_url" value="{{ old('link_url', $slide->link_url) }}" placeholder="#catalog or /products" maxlength="255">
                            </label>

                            <label>
                                <span class="label-text">Replace Slide Image</span>
                                <input class="field-pill" type="file" name="image" accept="image/*">
                            </label>

                            <div class="slot-actions">
                                <button type="submit" class="btn btn-primary btn-sm">Save Slot 0{{ $slot }}</button>
                            </div>
                        </form>

                        <div class="slot-secondary-actions">
                            {{-- Toggle Active --}}
                            <form method="POST" action="{{ route('admin.hero.images.toggle', $slide) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm">
                                    {{ $slide->is_active ? 'Hide Slide' : 'Show Slide' }}
                                </button>
                            </form>

                            {{-- Delete Slide (disabled if at minimum of 2) --}}
                            @if ($canDelete)
                                <form method="POST" action="{{ route('admin.hero.images.destroy', $slide) }}" onsubmit="return confirm('Remove this slide from the hero slider?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-danger btn-sm">Remove</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-ghost btn-sm muted" disabled title="Minimum 2 slides required for dynamic animation" style="opacity: 0.5; cursor: not-allowed;">
                                    Min 2 Slides
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Empty Slot Card (Slot 3 or 4) --}}
                    <div class="hero-slot-card is-empty">
                        <div class="slot-card-header">
                            <span class="slot-badge slot-badge-empty">SLOT 0{{ $slot }}</span>
                            <span class="pill">Available</span>
                        </div>

                        <div class="empty-dropzone">
                            <span class="dropzone-icon">+</span>
                            <strong>Add Slide 0{{ $slot }}</strong>
                            <p class="muted" style="color: #64748b;">Upload image to add this slide to the dynamic slider</p>
                        </div>

                        <form method="POST" action="{{ route('admin.hero.images.store') }}" enctype="multipart/form-data" class="slot-form">
                            @csrf
                            <input type="hidden" name="sort_order" value="{{ $slot }}">

                            <label>
                                <span class="label-text">Image File (JPG, PNG, WEBP) *</span>
                                <input class="field-pill" type="file" name="image" accept="image/*" required>
                            </label>

                            <label>
                                <span class="label-text">Slide Title / Caption (Optional)</span>
                                <input class="field-pill" name="title" placeholder="e.g. TS-40 Smart Hardware" maxlength="120">
                            </label>

                            <label>
                                <span class="label-text">Click URL (Optional)</span>
                                <input class="field-pill" name="link_url" placeholder="#catalog" maxlength="255">
                            </label>

                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; margin-top: 0.4rem;">
                                + Upload Slide 0{{ $slot }}
                            </button>
                        </form>
                    </div>
                @endif
            @endfor
        </div>
    </section>

    {{-- Hero Headlines, CTAs & ROI Calculator (Full-Width Studio Card Spreading Edge-to-Edge) --}}
    <section class="catalog" style="margin-top: 2.5rem; padding-left: 0; padding-right: 0;">
        <div class="section-head">
            <div>
                <h2>Hero Headlines & Content Copy</h2>
                <p class="muted" style="margin: 0.25rem 0 0 0; font-size: 0.88rem; color: #475569;">
                    Customize text copy, action buttons, trust points, and interactive ROI calculator.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.hero.settings.update') }}" class="studio-full-card">
            @csrf
            @method('PUT')

            <div class="studio-grid-three">
                {{-- Column 1: Headlines & Text Copy --}}
                <div class="studio-col">
                    <fieldset>
                        <legend>1. Headlines & Typography</legend>

                        <label>
                            <span>Kicker Badge Text</span>
                            <input class="field-pill" name="badge" value="{{ old('badge', $hero['badge']) }}" placeholder="TUYA SMART HARDWARE · DAILY YIELD FLEET" maxlength="120">
                        </label>

                        <label>
                            <span>Main Headline</span>
                            <input class="field-pill" name="title" value="{{ old('title', $hero['title']) }}" required placeholder="ACHIEVE THE HIGHEST" maxlength="150">
                        </label>

                        <label>
                            <span>Gold Gradient Highlight Text</span>
                            <input class="field-pill" name="title_highlight" value="{{ old('title_highlight', $hero['title_highlight']) }}" placeholder="DAILY LOCK YIELD" maxlength="150">
                        </label>

                        <label>
                            <span>Description Paragraph</span>
                            <textarea class="field-pill" name="description" rows="4" maxlength="1000" placeholder="TSL smart locks description...">{{ old('description', $hero['description']) }}</textarea>
                        </label>
                    </fieldset>
                </div>

                {{-- Column 2: Call to Action Buttons & Trust Features --}}
                <div class="studio-col">
                    <fieldset>
                        <legend>2. Action Buttons & Links</legend>

                        <label>
                            <span>Primary Button Text</span>
                            <input class="field-pill" name="cta_text" value="{{ old('cta_text', $hero['cta_text']) }}" placeholder="Start Earning" maxlength="60">
                        </label>

                        <label>
                            <span>Primary Button Target URL</span>
                            <input class="field-pill" name="cta_url" value="{{ old('cta_url', $hero['cta_url']) }}" placeholder="#catalog" maxlength="255">
                        </label>

                        <div style="height: 0.75rem;"></div>

                        <label>
                            <span>Secondary Button Text</span>
                            <input class="field-pill" name="secondary_text" value="{{ old('secondary_text', $hero['secondary_text']) }}" placeholder="Redeem Bonus 🎁" maxlength="60">
                        </label>

                        <label>
                            <span>Secondary Button Target URL</span>
                            <input class="field-pill" name="secondary_url" value="{{ old('secondary_url', $hero['secondary_url']) }}" placeholder="/bonus" maxlength="255">
                        </label>
                    </fieldset>

                    <fieldset style="margin-top: 1rem;">
                        <legend>Trust Highlights</legend>

                        <label>
                            <span>Trust Point 1</span>
                            <input class="field-pill" name="trust_1" value="{{ old('trust_1', $hero['trust_1']) }}" placeholder="Cash Out Any Day" maxlength="60">
                        </label>

                        <label>
                            <span>Trust Point 2</span>
                            <input class="field-pill" name="trust_2" value="{{ old('trust_2', $hero['trust_2']) }}" placeholder="Daily Automated Payouts" maxlength="60">
                        </label>

                        <label>
                            <span>Trust Point 3</span>
                            <input class="field-pill" name="trust_3" value="{{ old('trust_3', $hero['trust_3']) }}" placeholder="Min 2,000 UGX · 6% fee" maxlength="60">
                        </label>
                    </fieldset>
                </div>

                {{-- Column 3: Dynamics & ROI Calculator --}}
                <div class="studio-col">
                    <fieldset>
                        <legend>3. Dynamic Motion Settings</legend>

                        <label>
                            <span>Slide Transition Speed</span>
                            <select class="field-pill" name="slider_speed">
                                <option value="2" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 2)>2 seconds (Fast)</option>
                                <option value="3" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 3)>3 seconds</option>
                                <option value="4" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 4)>4 seconds (Recommended)</option>
                                <option value="5" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 5)>5 seconds</option>
                                <option value="6" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 6)>6 seconds</option>
                                <option value="8" @selected(old('slider_speed', $hero['slider_speed'] ?? 4) == 8)>8 seconds</option>
                            </select>
                        </label>

                        <label>
                            <span>Slide Direction</span>
                            <select class="field-pill" name="slider_direction">
                                <option value="ltr" @selected(old('slider_direction', $hero['slider_direction'] ?? 'ltr') === 'ltr')>Left to Right (➔)</option>
                                <option value="rtl" @selected(old('slider_direction', $hero['slider_direction'] ?? 'ltr') === 'rtl')>Right to Left (←)</option>
                            </select>
                        </label>

                        <p class="muted" style="font-size: 0.8rem; margin: 0.5rem 0 0; color: #64748b;">
                            Images slide in from the left and advance toward the right automatically.
                        </p>
                    </fieldset>

                    <fieldset style="margin-top: 1rem;">
                        <legend>Interactive ROI Calculator</legend>

                        <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; padding: 0.5rem 0;">
                            <input type="checkbox" name="show_calculator" value="1" @checked(old('show_calculator', $hero['show_calculator'])) style="width: 1.2rem; height: 1.2rem; accent-color: #ef8a2c; margin-top: 0.15rem;">
                            <div>
                                <strong style="display: block; font-size: 0.9rem; color: #0f172a;">Enable Homepage Calculator</strong>
                                <span style="font-size: 0.8rem; color: #64748b; line-height: 1.35; display: block; margin-top: 0.15rem;">
                                    Shows the "How Much Will I Earn?" reactive calculator card under the hero section.
                                </span>
                            </div>
                        </label>
                    </fieldset>

                    <div class="studio-save-action" style="margin-top: 1.5rem;">
                        <button class="btn btn-primary" type="submit" style="width: 100%; padding: 0.85rem 1.5rem; font-size: 1rem; font-weight: 800;">
                            Save All Hero Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

{{-- Mini Simulator Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const frame = document.getElementById('admin-mini-slider');
        if (!frame) return;

        const slides = Array.from(frame.querySelectorAll('.sim-slide'));
        if (slides.length <= 1) return;

        const dots = Array.from(frame.querySelectorAll('.sim-dot'));
        const speed = Math.max(2, parseInt(frame.dataset.speed || '4', 10));
        let cur = 0;

        setInterval(() => {
            const next = (cur + 1) % slides.length;
            const curSlide = slides[cur];
            const nextSlide = slides[next];

            // Smooth Left to Right animation
            nextSlide.style.transition = 'none';
            nextSlide.style.transform = 'translate3d(-100%, 0, 0)';
            nextSlide.style.opacity = '1';
            nextSlide.classList.add('is-active');

            void nextSlide.offsetWidth;

            curSlide.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.5s ease';
            nextSlide.style.transition = 'transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.5s ease';

            curSlide.style.transform = 'translate3d(100%, 0, 0)';
            curSlide.style.opacity = '0';
            nextSlide.style.transform = 'translate3d(0, 0, 0)';

            dots.forEach((d, i) => d.classList.toggle('is-active', i === next));

            setTimeout(() => {
                curSlide.classList.remove('is-active');
                curSlide.style.transform = '';
                curSlide.style.opacity = '';
                curSlide.style.transition = '';
                nextSlide.style.transition = '';
                cur = next;
            }, 600);
        }, speed * 1000);
    });
</script>
@endsection
