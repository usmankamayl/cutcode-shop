<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordFormRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;



class ForgotPasswordController extends Controller
{
    public function page(): Factory|View|Application|RedirectResponse
    {
        return view('auth.forgot-password');
    }

    public function handle(ForgotPasswordFormRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['message' => __($status)])
            : back()->withErrors(['email' => __($status)]);

    }

}
