<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;

class HeroSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function valueFor(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public static function allSettings(): array
    {
        $badge = static::valueFor('badge', 'TUYA SMART HARDWARE · DAILY YIELD FLEET');
        $title = static::valueFor('title', 'ACHIEVE THE HIGHEST');
        $titleHighlight = static::valueFor('title_highlight', 'DAILY LOCK YIELD');
        $description = static::valueFor('description', 'TSL is an IoT equipment investment platform designed for daily returns. Own Tuya-verified smart locks, collect automated daily earnings, and cash out every 35 days.');
        $ctaText = static::valueFor('cta_text', 'Start Earning');
        $ctaUrl = static::valueFor('cta_url', '#catalog');
        $secondaryText = static::valueFor('secondary_text', 'Redeem Bonus 🎁');
        $secondaryUrl = static::valueFor('secondary_url', route('bonus'));
        $trust1 = static::valueFor('trust_1', '35-Day Cashout');
        $trust2 = static::valueFor('trust_2', 'Daily Automated Payouts');
        $trust3 = static::valueFor('trust_3', 'Min Withdraw: 2,000 UGX');
        $imagePath = static::valueFor('image_path');
        $showCalculator = static::valueFor('show_calculator', '1');
        $sliderSpeed = static::valueFor('slider_speed', '4');
        $sliderDirection = static::valueFor('slider_direction', 'ltr');

        $imageUrl = $imagePath ? Media::url($imagePath) : asset('images/hero-banner.jpg');

        return [
            'badge' => $badge,
            'title' => $title,
            'title_highlight' => $titleHighlight,
            'description' => $description,
            'cta_text' => $ctaText,
            'cta_url' => $ctaUrl,
            'secondary_text' => $secondaryText,
            'secondary_url' => $secondaryUrl,
            'trust_1' => $trust1,
            'trust_2' => $trust2,
            'trust_3' => $trust3,
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'show_calculator' => (bool) $showCalculator,
            'slider_speed' => (int) $sliderSpeed,
            'slider_direction' => $sliderDirection,
        ];
    }
}
