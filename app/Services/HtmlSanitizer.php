<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Remove dangerous markup from admin/Codex authored HTML so it can be
     * rendered safely as real HTML on the public site (needed for the rich
     * editor output and for GEO/semantic structure).
     */
    public function clean(string $html): string
    {
        $clean = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;

        // Drop self-closing/standalone dangerous tags too.
        $clean = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*/?>#is', '', $clean) ?? $clean;

        // Strip inline event handlers (onclick, onerror, ...).
        $clean = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $clean) ?? $clean;

        // Neutralize javascript: URLs in href/src.
        $clean = preg_replace('#\s(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2#i', ' $1=$2#$2', $clean) ?? $clean;

        return trim($clean);
    }
}
