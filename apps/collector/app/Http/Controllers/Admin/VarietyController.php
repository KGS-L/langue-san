<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Variety\StoreVarietyRequest;
use App\Http\Requests\Admin\Variety\UpdateVarietyRequest;
use App\Models\Variety;
use App\Services\VarietyService;
class VarietyController extends Controller { public function __construct(private readonly VarietyService $service){} public function index(){ $this->authorize('viewAny',Variety::class);return view('admin.varieties.index',['varieties'=>$this->service->paginate()]);} public function create(){ $this->authorize('create',Variety::class);return view('admin.varieties.create');} public function store(StoreVarietyRequest $r){$this->service->create($r->validated());return redirect()->route('admin.varieties.index')->with('success','Variété créée.');} public function edit(Variety $variety){$this->authorize('update',$variety);return view('admin.varieties.edit',compact('variety'));} public function update(UpdateVarietyRequest $r,Variety $variety){$this->service->update($variety,$r->validated());return redirect()->route('admin.varieties.index')->with('success','Variété mise à jour.');} public function destroy(Variety $variety){$this->authorize('delete',$variety);$this->service->delete($variety);return back()->with('success','Variété supprimée.');} }
