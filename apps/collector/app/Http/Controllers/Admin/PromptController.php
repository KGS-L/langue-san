<?php
namespace App\Http\Controllers\Admin;
use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Prompt\StorePromptRequest;
use App\Http\Requests\Admin\Prompt\UpdatePromptRequest;
use App\Models\Prompt;
use App\Services\CategoryService;
use App\Services\PromptService;
class PromptController extends Controller { public function __construct(private readonly PromptService $service,private readonly CategoryService $categories){} public function index(){ $this->authorize('viewAny',Prompt::class);return view('admin.prompts.index',['prompts'=>$this->service->paginate()]);} public function create(){ $this->authorize('create',Prompt::class);return view('admin.prompts.create',['categories'=>$this->categories->active(),'types'=>PromptType::cases()]);} public function store(StorePromptRequest $r){$this->service->create($r->validated());return redirect()->route('admin.prompts.index')->with('success','Prompt créé.');} public function edit(Prompt $prompt){$this->authorize('update',$prompt);return view('admin.prompts.edit',['prompt'=>$prompt,'categories'=>$this->categories->active(),'types'=>PromptType::cases()]);} public function update(UpdatePromptRequest $r,Prompt $prompt){$this->service->update($prompt,$r->validated());return redirect()->route('admin.prompts.index')->with('success','Prompt mis à jour.');} public function destroy(Prompt $prompt){$this->authorize('delete',$prompt);$this->service->delete($prompt);return back()->with('success','Prompt supprimé.');} }
