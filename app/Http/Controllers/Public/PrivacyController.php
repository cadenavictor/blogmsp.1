<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SeoMetaBuilder;
use Illuminate\Contracts\View\View;

class PrivacyController extends Controller
{
    public function __invoke(SeoMetaBuilder $seo): View
    {
        return view('public.privacy', [
            'seoMeta' => $seo->forPage(
                'Política de Privacidade | Melhores de São Paulo',
                'Entenda como o Melhores de São Paulo trata dados pessoais, cookies e scripts de terceiros em conformidade com a LGPD.',
                route('privacy'),
            ),
        ]);
    }
}
