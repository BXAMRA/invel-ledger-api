<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function status(): JsonResponse
    {
        $onboarded = User::query()->count() > 0;

        return $this->success(['onboarded' => $onboarded], 'Status retrieved');
    }

    public function fresh(Request $request): JsonResponse
    {
        if (User::query()->count() > 0) {
            return $this->error('App is already setup', 400);
        }

        $request->validate([
            'admin.name' => 'required|string',
            'admin.username' => 'required|string',
            'admin.password' => 'required|string|min:8',
            'business.name' => 'required|string',
            'banking' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            User::query()->create([
                'name' => $request->input('admin.name'),
                'username' => $request->input('admin.username'),
                'password' => $request->input('admin.password'),
                'role' => 'admin',
            ]);

            foreach ($request->input('business', []) as $k => $v) {
                Setting::query()->updateOrCreate(['key' => "company.$k"], ['value' => is_array($v) ? json_encode($v) : $v]);
            }

            if ($request->has('banking')) {
                foreach ($request->input('banking', []) as $k => $v) {
                    Setting::query()->updateOrCreate(['key' => "company.bank.$k"], ['value' => is_array($v) ? json_encode($v) : $v]);
                }
            }

            Setting::query()->updateOrCreate(['key' => 'onboarded'], ['value' => 'true']);
        });

        return $this->success(null, 'Fresh setup completed successfully');
    }
}
