<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
class CategoryController extends Controller { public function __construct(private readonly CategoryService $service){} public function index(){ $this->authorize('viewAny',Category::class); return view('admin.categories.index',['categories'=>$this->service->paginate()]); } public function create(){ $this->authorize('create',Category::class); return view('admin.categories.create'); } public function store(StoreCategoryRequest $r){$this->service->create($r->validated());return redirect()->route('admin.categories.index')->with('success','Catégorie créée.');} public function edit(Category $category){$this->authorize('update',$category);return view('admin.categories.edit',compact('category'));} public function update(UpdateCategoryRequest $r,Category $category){$this->service->update($category,$r->validated());return redirect()->route('admin.categories.index')->with('success','Catégorie mise à jour.');} public function destroy(Category $category){$this->authorize('delete',$category);$this->service->delete($category);return back()->with('success','Catégorie supprimée.');} }
