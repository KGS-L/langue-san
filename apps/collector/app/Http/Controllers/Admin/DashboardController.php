<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contribution;
use App\Models\Prompt;
use App\Models\User;
class DashboardController extends Controller { public function __invoke(){ return view('admin.dashboard.index',['stats'=>['contributors'=>User::where('role','contributor')->count(),'categories'=>Category::count(),'prompts'=>Prompt::count(),'contributions'=>Contribution::count()]]); } }
