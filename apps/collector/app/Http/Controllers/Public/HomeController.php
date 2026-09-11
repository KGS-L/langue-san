<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\UserProfileService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(UserProfileService $profiles): View
    {
        return view('public.home', [
            'communityProfiles' => $profiles->publicCommunity(4),
        ]);
    }
}
