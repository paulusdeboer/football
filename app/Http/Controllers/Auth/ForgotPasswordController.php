<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $user = User::where('name', $request->name)->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', __('password_reset_link_sent', ['username' => '<strong><em>' . $request->name . '</em></strong>']));
    }
}
