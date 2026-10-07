<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MediaLibrary;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(Settings $settings): View
    {
        abort_unless(auth()->user()->hasPermission('settings.manage'), 403);

        return view('admin.settings.edit', [
            'settings' => $settings->all(),
            'seo' => false,
            'account' => auth()->user(),
            'users' => User::query()->with('roles')->orderBy('name')->get(),
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function seo(Settings $settings): View
    {
        abort_unless(auth()->user()->hasPermission('seo.manage'), 403);

        return view('admin.seo.edit', [
            'settings' => $settings->all(),
            'logoUrl' => $settings->logoUrl(),
        ]);
    }

    public function update(Request $request, Settings $settings, MediaLibrary $media): RedirectResponse
    {
        $seo = $request->boolean('seo_form');
        abort_unless(auth()->user()->hasPermission($seo ? 'seo.manage' : 'settings.manage'), 403);

        $rules = $seo
            ? [
                'seo_title_suffix' => ['nullable', 'string', 'max:80'],
                'seo_default_description' => ['nullable', 'string', 'max:320'],
                'publisher_name' => ['nullable', 'string', 'max:160'],
                'publisher_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
                'remove_publisher_logo' => ['sometimes', 'boolean'],
                'twitter_handle' => ['nullable', 'string', 'max:80'],
                'page_about' => ['nullable', 'string', 'max:20000'],
                'page_privacy' => ['nullable', 'string', 'max:20000'],
                'page_terms' => ['nullable', 'string', 'max:20000'],
                'analytics_id' => ['nullable', 'string', 'max:40'],
                'analytics_enabled' => ['sometimes', 'boolean'],
            ]
            : [
                'site_name' => ['required', 'string', 'max:120'],
                'tagline' => ['nullable', 'string', 'max:180'],
                'copyright' => ['nullable', 'string', 'max:180'],
                'contact_email' => ['nullable', 'email', 'max:255'],
                'social_facebook' => ['nullable', 'url', 'max:2048'],
                'social_x' => ['nullable', 'url', 'max:2048'],
                'social_instagram' => ['nullable', 'url', 'max:2048'],
                'social_youtube' => ['nullable', 'url', 'max:2048'],
                'social_pinterest' => ['nullable', 'url', 'max:2048'],
            ];

        $data = $request->validate($rules);

        if ($seo) {
            $data['analytics_enabled'] = $request->boolean('analytics_enabled') ? '1' : '0';
            $logo = $this->publisherLogo($request, $settings, $media);
            unset($data['publisher_logo'], $data['remove_publisher_logo']);

            if ($logo !== null) {
                $data['publisher_logo'] = $logo;
            }
        }

        $settings->setMany($data);

        return back()->with('status', 'Settings saved.');
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('status', 'Login details updated.');
    }

    private function publisherLogo(Request $request, Settings $settings, MediaLibrary $media): ?string
    {
        $current = (string) $settings->get('publisher_logo', '');
        $file = $request->file('publisher_logo');

        if (! $file instanceof UploadedFile && ! $request->boolean('remove_publisher_logo')) {
            return null;
        }

        $this->deleteStoredLogo($current, $media);

        if (! $file instanceof UploadedFile) {
            return '';
        }

        return $media->store($file, $request->user(), [
            'alt_text' => $request->input('publisher_name') ?: 'Publisher logo',
        ])->path;
    }

    private function deleteStoredLogo(string $current, MediaLibrary $media): void
    {
        if ($current === '' || str_starts_with($current, 'http://') || str_starts_with($current, 'https://')) {
            return;
        }

        $stored = Media::query()->where('path', $current)->first();

        if ($stored) {
            $media->delete($stored);

            return;
        }

        Storage::disk('public')->delete($current);
    }
}
