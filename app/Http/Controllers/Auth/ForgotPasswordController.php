<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword', ['status' => session('status')]);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string']]);
        $user = User::where('name', $request->name)->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', __('password_reset_link_sent', [
            'username' => $request->name,
        ]));
    }
}
