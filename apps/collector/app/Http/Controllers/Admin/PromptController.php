<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PromptType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Prompt\ImportPromptRequest;
use App\Http\Requests\Admin\Prompt\IndexPromptRequest;
use App\Http\Requests\Admin\Prompt\StorePromptRequest;
use App\Http\Requests\Admin\Prompt\UpdatePromptRequest;
use App\Models\Prompt;
use App\Services\CategoryService;
use App\Services\PromptImportService;
use App\Services\PromptService;
use Illuminate\Http\RedirectResponse;

class PromptController extends Controller
{
    public function __construct(
        private readonly PromptService $service,
        private readonly CategoryService $categories,
    ) {}

    public function index(IndexPromptRequest $request)
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 20);

        return view('admin.prompts.index', [
            'prompts' => $this->service->paginate($filters, $perPage),
            'stats' => $this->service->stats(),
            'categories' => $this->categories->active(),
            'types' => PromptType::cases(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Prompt::class);

        return view('admin.prompts.create', [
            'categories' => $this->categories->active(),
            'types' => PromptType::cases(),
        ]);
    }

    public function store(StorePromptRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.prompts.index')
            ->with('success', 'Prompt créé.');
    }

    public function edit(Prompt $prompt)
    {
        $this->authorize('update', $prompt);

        return view('admin.prompts.edit', [
            'prompt' => $prompt,
            'categories' => $this->categories->active(),
            'types' => PromptType::cases(),
        ]);
    }

    public function update(UpdatePromptRequest $request, Prompt $prompt): RedirectResponse
    {
        $this->service->update($prompt, $request->validated());

        return redirect()
            ->route('admin.prompts.index')
            ->with('success', 'Prompt mis à jour.');
    }

    public function destroy(Prompt $prompt): RedirectResponse
    {
        $this->authorize('delete', $prompt);
        $deleted = $this->service->delete($prompt);

        return back()->with(
            'success',
            $deleted
                ? 'Prompt supprimé.'
                : 'Ce prompt possède déjà des contributions : il a été désactivé pour préserver l’historique.',
        );
    }

    public function import(ImportPromptRequest $request, PromptImportService $importer): RedirectResponse
    {
        $result = $importer->import($request->file('file')->getRealPath());

        return back()
            ->with('success', $result['processed'].' prompt(s) importé(s) ou mis à jour.')
            ->with('import_errors', $result['errors']);
    }
}
