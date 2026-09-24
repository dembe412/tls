<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return view('team.index', [
            'user' => $user,
            'rates' => config('support.referral_rates'),
            'vips' => Product::query()->vips()->get(),
            'levelA' => $user?->levelAMembers()->count() ?? 0,
            'levelB' => $user?->levelBMembers()->count() ?? 0,
            'levelC' => $user?->levelCMembers()->count() ?? 0,
            'inviteUrl' => $user?->inviteUrl(),
        ]);
    }
}
