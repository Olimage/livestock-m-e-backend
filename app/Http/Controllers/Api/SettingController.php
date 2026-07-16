<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(Request $request)
    {
        $this->authorizeManage($request);

        return response()->json(['success' => true, 'data' => $this->settings->allPublic()]);
    }

    public function update(Request $request)
    {
        $this->authorizeManage($request);
        $data = $request->validate(['settings' => 'required|array']);

        foreach ($data['settings'] as $key => $value) {
            $this->settings->set($key, $value);
        }

        return response()->json(['success' => true, 'message' => 'Settings updated.', 'data' => $this->settings->allPublic()]);
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();
        abort_unless($user->is_admin || $user->hasPermission('manage-settings'), 403, 'Forbidden.');
    }
}
