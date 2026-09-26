<?php

namespace App\Http\Controllers;

use App\Models\HeroImage;
use App\Models\HeroSetting;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminHeroController extends Controller
{
    /**
     * Display the hero section manager page.
     */
    public function index(): View
    {
        $images = HeroImage::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $hero = HeroSetting::allSettings();

        return view('admin.hero.index', [
            'images' => $images,
            'hero' => $hero,
            'activeCount' => $images->where('is_active', true)->count(),
            'totalCount' => $images->count(),
            'canAdd' => $images->count() < 4,
            'canDelete' => $images->count() > 2,
        ]);
    }

    /**
     * Store a newly created hero image (max 4 images allowed).
     */
    public function storeImage(Request $request): RedirectResponse
    {
        if (HeroImage::count() >= 4) {
            return back()->with('info', 'Maximum limit of 4 hero slider images reached. Please replace or edit an existing slide.');
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:10240'],
            'title' => ['nullable', 'string', 'max:120'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = Media::store($request->file('image'), 'hero');

        $nextSortOrder = (int) ($validated['sort_order'] ?? (HeroImage::max('sort_order') + 1));

        HeroImage::create([
            'title' => $request->string('title')->trim()->value() ?: null,
            'image_path' => $path,
            'link_url' => $request->string('link_url')->trim()->value() ?: null,
            'sort_order' => $nextSortOrder,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Hero image added successfully.');
    }

    /**
     * Update an existing hero image.
     */
    public function updateImage(Request $request, HeroImage $heroImage): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['nullable', 'image', 'max:10240'],
            'title' => ['nullable', 'string', 'max:120'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = [
            'title' => $request->string('title')->trim()->value() ?: null,
            'link_url' => $request->string('link_url')->trim()->value() ?: null,
            'sort_order' => (int) ($validated['sort_order'] ?? $heroImage->sort_order),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $heroImage->is_active,
        ];

        if ($request->hasFile('image')) {
            if (! str_starts_with($heroImage->image_path, 'images/')) {
                Media::delete($heroImage->image_path);
            }
            $data['image_path'] = Media::store($request->file('image'), 'hero');
        }

        $heroImage->update($data);

        return back()->with('success', 'Hero slide updated successfully.');
    }

    /**
     * Toggle active visibility of an image (enforcing min 2 active).
     */
    public function toggleImage(HeroImage $heroImage): RedirectResponse
    {
        if ($heroImage->is_active && HeroImage::where('is_active', true)->count() <= 2) {
            return back()->with('info', 'At least 2 active images are required for dynamic left-to-right slider animation.');
        }

        $heroImage->update([
            'is_active' => ! $heroImage->is_active,
        ]);

        $status = $heroImage->is_active ? 'active' : 'hidden';

        return back()->with('success', "Hero slide is now {$status}.");
    }

    /**
     * Remove a hero image (enforcing min 2 images).
     */
    public function destroyImage(HeroImage $heroImage): RedirectResponse
    {
        if (HeroImage::count() <= 2) {
            return back()->with('info', 'A minimum of 2 hero images is required to maintain dynamic slider movement. You can replace this image instead.');
        }

        if (! str_starts_with($heroImage->image_path, 'images/')) {
            Media::delete($heroImage->image_path);
        }

        $heroImage->delete();

        return back()->with('success', 'Hero slide removed.');
    }

    /**
     * Update general hero section settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:150'],
            'title_highlight' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:60'],
            'secondary_url' => ['nullable', 'string', 'max:255'],
            'trust_1' => ['nullable', 'string', 'max:60'],
            'trust_2' => ['nullable', 'string', 'max:60'],
            'trust_3' => ['nullable', 'string', 'max:60'],
            'show_calculator' => ['nullable', 'boolean'],
            'slider_speed' => ['nullable', 'integer', 'min:1', 'max:30'],
            'slider_direction' => ['nullable', 'string', 'in:ltr,rtl'],
        ]);

        foreach ($validated as $key => $val) {
            HeroSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($val) ? ($val ? '1' : '0') : (string) ($val ?? '')]
            );
        }

        HeroSetting::updateOrCreate(
            ['key' => 'show_calculator'],
            ['value' => $request->boolean('show_calculator') ? '1' : '0']
        );

        return back()->with('success', 'Hero section settings saved successfully.');
    }
}
