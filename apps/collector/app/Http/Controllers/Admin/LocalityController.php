<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Locality\StoreLocalityRequest;
use App\Http\Requests\Admin\Locality\UpdateLocalityRequest;
use App\Models\Locality;
use App\Services\LocalityService;
use App\Services\VarietyService;
class LocalityController extends Controller { public function __construct(private readonly LocalityService $service,private readonly VarietyService $varieties){} public function index(){ $this->authorize('viewAny',Locality::class);return view('admin.localities.index',['localities'=>$this->service->paginate()]);} public function create(){ $this->authorize('create',Locality::class);return view('admin.localities.create',['varieties'=>$this->varieties->active()]);} public function store(StoreLocalityRequest $r){$this->service->create($r->validated());return redirect()->route('admin.localities.index')->with('success','Localité créée.');} public function edit(Locality $locality){$this->authorize('update',$locality);return view('admin.localities.edit',['locality'=>$locality,'varieties'=>$this->varieties->active()]);} public function update(UpdateLocalityRequest $r,Locality $locality){$this->service->update($locality,$r->validated());return redirect()->route('admin.localities.index')->with('success','Localité mise à jour.');} public function destroy(Locality $locality){$this->authorize('delete',$locality);$this->service->delete($locality);return back()->with('success','Localité supprimée.');} }
