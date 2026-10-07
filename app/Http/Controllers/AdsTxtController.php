<?php

namespace App\Http\Controllers;

use App\Models\AdsTxtEntry;
use App\Models\AdsTxtLine;
use Illuminate\Http\Response;

class AdsTxtController extends Controller
{
    public function show(): Response
    {
        $lines = AdsTxtLine::query()
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('line');

        $entries = AdsTxtEntry::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (AdsTxtEntry $entry) => $entry->line());

        $body = $lines->concat($entries)->implode("\n");

        if ($body !== '') {
            $body .= "\n";
        }

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
