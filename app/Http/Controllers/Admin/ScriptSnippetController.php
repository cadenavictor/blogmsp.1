<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScriptSnippetRequest;
use App\Models\ScriptSnippet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScriptSnippetController extends Controller
{
    public function index(): View
    {
        return view('admin.scripts.index', [
            'snippets' => ScriptSnippet::query()
                ->orderBy('position')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.scripts.create', [
            'snippet' => new ScriptSnippet(['position' => 'head_end']),
            'positions' => ScriptSnippet::positions(),
            'cookieCategories' => ScriptSnippet::cookieCategories(),
        ]);
    }

    public function store(ScriptSnippetRequest $request): RedirectResponse
    {
        ScriptSnippet::create($this->snippetData($request));

        return redirect()
            ->route('admin.scripts.index')
            ->with('status', 'Script criado.');
    }

    public function edit(ScriptSnippet $script): View
    {
        return view('admin.scripts.edit', [
            'snippet' => $script,
            'positions' => ScriptSnippet::positions(),
            'cookieCategories' => ScriptSnippet::cookieCategories(),
        ]);
    }

    public function update(ScriptSnippetRequest $request, ScriptSnippet $script): RedirectResponse
    {
        $script->update($this->snippetData($request));

        return redirect()
            ->route('admin.scripts.edit', $script)
            ->with('status', 'Script atualizado.');
    }

    public function destroy(ScriptSnippet $script): RedirectResponse
    {
        $script->delete();

        return redirect()
            ->route('admin.scripts.index')
            ->with('status', 'Script removido.');
    }

    /**
     * @return array<string, mixed>
     */
    private function snippetData(ScriptSnippetRequest $request): array
    {
        $data = $request->safe()->except(['is_active', 'requires_consent']);
        $data['is_active'] = $request->boolean('is_active');
        $data['requires_consent'] = $request->boolean('requires_consent');
        $data['cookie_category'] = $data['requires_consent']
            ? ($data['cookie_category'] ?? ScriptSnippet::COOKIE_CATEGORY_ANALYTICS)
            : ScriptSnippet::COOKIE_CATEGORY_ESSENTIAL;

        return $data;
    }
}
