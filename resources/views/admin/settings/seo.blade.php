@extends('layouts.admin', ['title' => 'SEO & Site'])

@php
    $social = old('social_links', $settings->social_links ?? []);
    $socialFields = [
        'x' => 'X / Twitter',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
    ];
@endphp

@section('content')
    <form class="admin-form" method="POST" action="{{ route('admin.settings.seo.update') }}">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert error" role="alert">
                <strong>Revise os campos destacados.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Identidade &amp; Home</h2>
                    <p class="muted">Define o que aparece no &lt;title&gt;, na descricao da home e nas previas sociais.</p>
                </div>
            </div>

            <div class="form-grid">
                <label class="field">
                    <span>Nome do site</span>
                    <input name="site_name" value="{{ old('site_name', $settings->site_name) }}" placeholder="{{ config('app.name') }}">
                </label>

                <label class="field">
                    <span>Tagline</span>
                    <input name="tagline" value="{{ old('tagline', $settings->tagline) }}" placeholder="Subtitulo curto da home">
                </label>

                <label class="field span-2">
                    <span>Titulo da Home (&lt;title&gt;)</span>
                    <input name="home_title" value="{{ old('home_title', $settings->home_title) }}" placeholder="Padrao: nome do site">
                </label>

                <label class="field span-2">
                    <span>Meta description da Home</span>
                    <textarea name="home_meta_description" placeholder="Resumo da home para buscadores (ate ~160 caracteres)">{{ old('home_meta_description', $settings->home_meta_description) }}</textarea>
                </label>

                <label class="field span-2">
                    <span>Meta description padrao (fallback)</span>
                    <textarea name="default_meta_description" placeholder="Usada em paginas sem descricao propria">{{ old('default_meta_description', $settings->default_meta_description) }}</textarea>
                </label>

                <div class="field">
                    <span>Imagem social padrao (OG)</span>
                    @php $ogImage = old('default_og_image', $settings->default_og_image); @endphp
                    <div class="uploader" data-uploader>
                        <div class="preview" data-uploader-preview>
                            @if ($ogImage)
                                <img src="{{ $settings->imageUrl($ogImage) }}" alt="OG image">
                            @else
                                <span class="placeholder">1200x630 recomendado</span>
                            @endif
                        </div>
                        <input type="text" name="default_og_image" value="{{ $ogImage }}" data-uploader-value placeholder="URL/caminho ou envie um arquivo">
                        <div class="uploader-actions">
                            <label class="button secondary">Enviar imagem <input type="file" accept="image/*" hidden></label>
                            <button type="button" class="ghost" data-uploader-clear>Limpar</button>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <span>Logo (exibido no cabeçalho e no JSON-LD)</span>
                    @php $logo = old('logo_path', $settings->logo_path); @endphp
                    <div class="uploader" data-uploader>
                        <div class="preview" data-uploader-preview>
                            @if ($logo)
                                <img src="{{ $settings->imageUrl($logo) }}" alt="Logo">
                            @else
                                <span class="placeholder">Logo da marca (PNG/SVG)</span>
                            @endif
                        </div>
                        <input type="text" name="logo_path" value="{{ $logo }}" data-uploader-value placeholder="URL/caminho ou envie um arquivo">
                        <div class="uploader-actions">
                            <label class="button secondary">Enviar imagem <input type="file" accept="image/*" hidden></label>
                            <button type="button" class="ghost" data-uploader-clear>Limpar</button>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <span>Ícone do site (favicon — aba do navegador)</span>
                    @php $favicon = old('favicon_path', $settings->favicon_path); @endphp
                    <div class="uploader" data-uploader>
                        <div class="preview" data-uploader-preview>
                            @if ($favicon)
                                <img src="{{ $settings->imageUrl($favicon) }}" alt="Favicon">
                            @else
                                <span class="placeholder">Quadrado, 512x512 (PNG)</span>
                            @endif
                        </div>
                        <input type="text" name="favicon_path" value="{{ $favicon }}" data-uploader-value placeholder="URL/caminho ou envie um PNG/ICO">
                        <div class="uploader-actions">
                            <label class="button secondary">Enviar PNG <input type="file" accept="image/png,image/webp" hidden></label>
                            <button type="button" class="ghost" data-uploader-clear>Limpar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Organizacao &amp; Redes</h2>
                    <p class="muted">Sinais de marca/autoridade para SEO e GEO (JSON-LD Organization, sameAs).</p>
                </div>
            </div>

            <div class="form-grid">
                <label class="field">
                    <span>Nome da organizacao</span>
                    <input name="organization_name" value="{{ old('organization_name', $settings->organization_name) }}" placeholder="Padrao: nome do site">
                </label>

                <label class="field">
                    <span>@ no X/Twitter (twitter:site)</span>
                    <input name="twitter_handle" value="{{ old('twitter_handle', $settings->twitter_handle) }}" placeholder="@suamarca">
                </label>

                <label class="field span-2">
                    <span>Descricao da organizacao</span>
                    <textarea name="organization_description">{{ old('organization_description', $settings->organization_description) }}</textarea>
                </label>

                <label class="field">
                    <span>Email de contato</span>
                    <input name="contact_email" type="email" value="{{ old('contact_email', $settings->contact_email) }}">
                </label>

                <label class="field">
                    <span>Telefone de contato</span>
                    <input name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}">
                </label>

                <fieldset class="span-2">
                    <legend>Perfis sociais (sameAs)</legend>
                    <div class="form-grid">
                        @foreach ($socialFields as $key => $label)
                            <label class="field">
                                <span>{{ $label }}</span>
                                <input type="url" name="social_links[{{ $key }}]" value="{{ data_get($social, $key) }}" placeholder="https://...">
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>GEO &amp; IA</h2>
                    <p class="muted">Controla o llms.txt e a politica de bots de IA no robots.txt.</p>
                </div>
            </div>

            <div class="form-grid">
                <label class="field">
                    <span>Idioma (html lang / inLanguage)</span>
                    <input name="language" value="{{ old('language', $settings->language ?: 'pt-BR') }}">
                </label>

                <label class="field">
                    <span>Locale (og:locale)</span>
                    <input name="locale" value="{{ old('locale', $settings->locale ?: 'pt_BR') }}">
                </label>

                <label class="field span-2">
                    <span>Resumo para IAs (llms.txt)</span>
                    <textarea name="llms_summary" placeholder="Descreva em 1-3 frases sobre o que e o site, para agentes de IA citarem com precisao.">{{ old('llms_summary', $settings->llms_summary) }}</textarea>
                </label>

                <label class="field span-2">
                    <span>Politica de uso por IA (llms.txt)</span>
                    <textarea name="ai_policy" placeholder="Ex.: Conteudo pode ser citado com atribuicao e link para a fonte.">{{ old('ai_policy', $settings->ai_policy) }}</textarea>
                </label>

                <label class="check-field">
                    <input type="hidden" name="allow_ai_search" value="0">
                    <input type="checkbox" name="allow_ai_search" value="1" @checked((bool) old('allow_ai_search', $settings->allow_ai_search))>
                    Permitir bots de busca/citacao por IA (OAI-SearchBot, PerplexityBot)
                </label>

                <label class="check-field">
                    <input type="hidden" name="allow_ai_training" value="0">
                    <input type="checkbox" name="allow_ai_training" value="1" @checked((bool) old('allow_ai_training', $settings->allow_ai_training))>
                    Permitir bots de treinamento de IA (GPTBot, Google-Extended, ClaudeBot)
                </label>
            </div>
        </section>

        <div class="form-footer">
            <button type="submit">Salvar configuracoes</button>
            <a class="button secondary" href="{{ route('admin.dashboard') }}">Voltar</a>
        </div>
    </form>
@endsection
