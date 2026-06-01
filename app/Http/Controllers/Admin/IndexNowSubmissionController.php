<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndexNowSubmission;
use App\Services\IndexNowClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexNowSubmissionController extends Controller
{
    public function index(): View
    {
        return view('admin.indexnow.index', [
            'submissions' => IndexNowSubmission::query()
                ->latest('submitted_at')
                ->paginate(20),
        ]);
    }

    public function store(Request $request, IndexNowClient $client): RedirectResponse
    {
        $data = $request->validate([
            'url' => [
                'required',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->usesConfiguredAppHost((string) $value)) {
                        $fail('A URL deve pertencer ao host configurado da aplicacao.');
                    }
                },
            ],
        ]);

        $response = $client->submit([$data['url']]);

        if ($response === null) {
            return redirect()
                ->route('admin.indexnow.index')
                ->with('error', 'Nao foi possivel conectar ao IndexNow. A tentativa foi registrada.');
        }

        return redirect()
            ->route('admin.indexnow.index')
            ->with('status', 'URL enviada ao IndexNow.');
    }

    private function usesConfiguredAppHost(string $url): bool
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if (! is_string($appHost) || ! is_string($urlHost)) {
            return false;
        }

        return strtolower($appHost) === strtolower($urlHost)
            && parse_url((string) config('app.url'), PHP_URL_PORT) === parse_url($url, PHP_URL_PORT);
    }
}
