<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApiReferenceController extends Controller
{
    public function index(): View
    {
        return view('admin.api.index', [
            'baseUrl' => rtrim((string) config('app.url'), '/'),
            'keyConfigured' => trim((string) config('services.codex.api_key')) !== '',
        ]);
    }
}
