<?php

namespace App\Http\Controllers;

use App\Models\AdsTxtLine;
use Illuminate\Http\Response;

class AdsTxtController extends Controller
{
    public function show(): Response
    {
        $body = AdsTxtLine::query()
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('line')
            ->implode("\n");

        if ($body !== '') {
            $body .= "\n";
        }

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
