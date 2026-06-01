<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.seo', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::query()->orderBy('id')->first() ?? new SiteSetting;

        $settings->fill($request->validated())->save();

        return redirect()
            ->route('admin.settings.seo.edit')
            ->with('status', 'Configuracoes de SEO atualizadas.');
    }
}
