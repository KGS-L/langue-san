<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contribution\TranscribeContributionRequest;
use App\Models\Contribution;
use App\Services\ContributionService;
use App\Services\VarietyService;
class ContributionController extends Controller { public function __construct(private readonly ContributionService $service,private readonly VarietyService $varieties){} public function index(){ $this->authorize('viewAny',Contribution::class);return view('admin.contributions.index',['contributions'=>$this->service->paginate()]);} public function show(Contribution $contribution){$this->authorize('view',$contribution);$contribution->load(['user','prompt.category','locality','recording','validations.validator','validations.variety']);return view('admin.contributions.show',['contribution'=>$contribution,'varieties'=>$this->varieties->active()]);} public function transcribe(TranscribeContributionRequest $r,Contribution $contribution){$this->service->transcribe($contribution,$r->validated('san_text'));return back()->with('success','Transcription enregistrée.');} }
