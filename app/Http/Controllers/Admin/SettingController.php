<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use App\Services\MediaService;
use App\Support\Cms as CmsSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function general(Request $request): Response
    {
        return Inertia::render('Admin/Settings/General', [
            'settings' => [
                'site_name' => CmsSupport::setting('site_name', config('app.name')),
                'tagline' => CmsSupport::setting('tagline', ''),
                'admin_email' => CmsSupport::setting('admin_email', config('mail.from.address')),
                'locale' => CmsSupport::setting('locale', config('app.locale')),
                'datetime_format' => CmsSupport::setting('datetime_format', 'Y-m-d H:i'),
                'logo_url' => CmsSupport::setting('site_logo', ''),
                'favicon_url' => CmsSupport::setting('site_favicon', ''),
            ],
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'locale' => ['required', 'string', 'max:20'],
            'datetime_format' => ['required', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:5120'], // 5MB
            'favicon' => ['nullable', 'image', 'max:2048'], // 2MB
            'logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'favicon_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ]);

        // Persist primitive settings
        $pairs = [
            'site_name' => $data['site_name'],
            'tagline' => $data['tagline'] ?? '',
            'admin_email' => $data['admin_email'],
            'locale' => $data['locale'],
            'datetime_format' => $data['datetime_format'],
        ];

        foreach ($pairs as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget('setting_'.$key);
        }

        // Handle logo (upload takes precedence over selecting existing)
        if ($request->hasFile('logo')) {
            $media = MediaService::storeFromUpload($request->file('logo'), $request->user()->id, 'uploads/'.date('Y/m'));
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $media->url()]);
            Cache::forget('setting_site_logo');
        } elseif (!empty($data['logo_media_id'])) {
            $m = Media::find($data['logo_media_id']);
            if ($m) {
                Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $m->url()]);
                Cache::forget('setting_site_logo');
            }
        }

        // Handle favicon (upload takes precedence)
        if ($request->hasFile('favicon')) {
            $media = MediaService::storeFromUpload($request->file('favicon'), $request->user()->id, 'uploads/'.date('Y/m'));
            Setting::updateOrCreate(['key' => 'site_favicon'], ['value' => $media->url()]);
            Cache::forget('setting_site_favicon');
        } elseif (!empty($data['favicon_media_id'])) {
            $m = Media::find($data['favicon_media_id']);
            if ($m) {
                Setting::updateOrCreate(['key' => 'site_favicon'], ['value' => $m->url()]);
                Cache::forget('setting_site_favicon');
            }
        }

        return redirect()->route('admin.settings.general')->with('success', 'Settings updated successfully.');
    }
}
