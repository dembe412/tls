<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'slug' => 'welcome-to-tsl',
                'title' => 'Welcome to TSL — own a lock, earn every day',
                'excerpt' => 'Every Tuya smart lock on TSL pays a daily amount set by the manager. Cash out any day once you reach 2,000 UGX.',
                'badge' => 'Start here',
                'published_at' => now()->subDays(4),
                'body' => <<<'TXT'
TSL is simple on purpose.

You pick a smart lock. You send the money. A manager switches that lock on for your account. From that day, it pays the daily interest shown on the lock — every morning.

You can cash out any day once available earnings reach 2,000 UGX. A 6% charge applies on each withdraw.

No hidden packages. The lock you see on the home page is the lock you own.
TXT,
            ],
            [
                'slug' => 'how-cash-out-works',
                'title' => 'How daily cash-out works',
                'excerpt' => 'Withdraw any day you have at least 2,000 UGX available — a 6% fee applies. No wait for day 35.',
                'badge' => 'Guide',
                'published_at' => now()->subDays(2),
                'body' => <<<'TXT'
Day 1 starts when your lock is marked as bought.

Each day after that, your owner card adds the lock’s daily income. Cash out whenever available earnings are at least 2,000 UGX — any day, any time. A 6% charge is taken on each withdraw.

Tap Cash out on the owner card. A manager confirms the payment the same way they confirmed your lock.
TXT,
            ],
            [
                'slug' => 'new-ts50-flagship',
                'title' => 'TS-50 is in stock — the flagship lock',
                'excerpt' => 'One million UGX. 51,389 UGX every day. The top of the TSL range.',
                'badge' => 'New',
                'published_at' => now()->subDay(),
                'body' => <<<'TXT'
The TS-50 is the lock people save for.

Fingerprint, keypad, and the strongest daily return on the shelf. If you already own a TS-20 or TS-30, this is the upgrade that turns a side income into a real monthly cycle.

Create your account, request the lock, and send the money. We switch it on the same day we receive it.
TXT,
            ],
        ];

        $authorId = User::query()->where('role', 'admin')->orderBy('id')->value('id');

        foreach ($articles as $article) {
            if ($authorId) {
                $article['user_id'] = $authorId;
            }

            NewsArticle::query()->updateOrCreate(
                ['slug' => $article['slug']],
                $article
            );
        }
    }
}
