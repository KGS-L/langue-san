<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\UserProfileService;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function __invoke(UserProfileService $profiles): View
    {
        return view('public.community', [
            'profiles' => $profiles->publicCommunity(60),
        ]);
    }
}
