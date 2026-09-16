<?php

namespace App\Http\Controllers;

use App\Support\CommunityFeed;
use Illuminate\Http\JsonResponse;

class CommunityController extends Controller
{
    public function __invoke(CommunityFeed $feed): JsonResponse
    {
        return response()->json($feed->snapshot());
    }
}
