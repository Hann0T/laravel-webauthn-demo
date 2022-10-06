<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use LaravelWebauthn\Actions\PrepareAssertionData;

class CustomLoginController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->cookie('email');
        $user = User::where('webauthn', $email)->get()->first();

        $publicKey = app(PrepareAssertionData::class)($user);
        return Inertia::render('WebAuthnLogin', ['publicKey' => $publicKey]);
    }

    public function webAuthnRemember()
    {
        $minutes = 600;
        $response = new Response();

        try {
            $response->withCookie(
                cookie('webauthn', auth()->user()->email, $minutes)
            );
            $response->setContent('cookie stored');
            return $response;
        } catch (Exception $e) {
            $response->setContent('cookie not stored');
            return $response;
        }
    }
}
