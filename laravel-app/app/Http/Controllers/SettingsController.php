<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SettingsController extends Controller
{
    /**
     * Update user settings.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'currency' => 'required|string|in:' . implode(',', User::getSupportedCurrencies()),
        ]);

        $user->update([
            'currency' => $request->currency,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.settings_updated'),
                'currency' => $user->currency
            ]);
        }

        return back()->with('success', __('messages.settings_updated'));
    }
}
