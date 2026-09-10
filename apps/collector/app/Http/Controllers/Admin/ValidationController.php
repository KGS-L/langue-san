<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Validation\StoreValidationRequest;
use App\Models\Contribution;
use App\Services\ValidationService;
class ValidationController extends Controller { public function store(StoreValidationRequest $request,Contribution $contribution,ValidationService $service){$service->validate($contribution,$request->user(),$request->validated());return back()->with('success','Validation enregistrée.');} }
