<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
    
    
     /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validação do email
        $request->validate(['email' => 'required|email']);

        // Envio do link de redefinição de senha
        $response = Password::sendResetLink($request->only('email'));

        // Verifica a resposta e adiciona a mensagem à sessão
        if ($response == Password::RESET_LINK_SENT) {
            return back()->with('status', __('Link enviado com sucesso!, aguarde alguns muinutos e Verifique seu email.'));
        } else {
            return back()->withErrors(['email' => __('Ocorreu um erro ao enviar o link de recuperação, Favor verifique o Email digitado.')]);
        }
    }
}
