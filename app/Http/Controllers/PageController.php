<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Settings;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(Settings $settings): View
    {
        return view('public.about', [
            'siteName' => $settings->get('site_name', 'ERIBS Media'),
            'tagline' => $settings->get('tagline'),
            'deskEmail' => $settings->get('contact_email'),
            'body' => (string) $settings->get('page_about'),
            'desks' => Category::query()
                ->where('show_on_home', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function privacy(Settings $settings): View
    {
        return view('public.privacy', [
            'siteName' => $settings->get('site_name', 'ERIBS Media'),
            'deskEmail' => $settings->get('contact_email'),
            'analyticsOn' => $settings->get('analytics_enabled') === '1' && filled($settings->get('analytics_id')),
            'note' => (string) $settings->get('page_privacy'),
        ]);
    }

    public function terms(Settings $settings): View
    {
        return view('public.terms', [
            'siteName' => $settings->get('site_name', 'ERIBS Media'),
            'deskEmail' => $settings->get('contact_email'),
            'note' => (string) $settings->get('page_terms'),
        ]);
    }

    public function contact(Settings $settings): View
    {
        return view('public.contact', [
            'siteName' => $settings->get('site_name', 'ERIBS Media'),
            'deskEmail' => $settings->get('contact_email'),
        ]);
    }

}
