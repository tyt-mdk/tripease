<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/trips/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // ログインIDのフィールドをカスタマイズ
    public function username()
    {
        $login = request()->input('login');
        
        // メールアドレス形式かどうかで判断
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        request()->merge([$field => $login]);
        
        return $field;
    }

    // バリデーションルールをカスタマイズ
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    // 認証の試行
    protected function attemptLogin(Request $request)
    {
        $login = $request->input('login');
        $password = $request->input('password');

        // デバッグ情報を記録
        \Log::info('Login attempt:', [
            'input' => $login,
            'exists_as_email' => \App\Models\User::where('email', $login)->exists(),
            'exists_as_name' => \App\Models\User::where('name', $login)->exists()
        ]);

        // メールアドレスとユーザー名の両方で試行
        $success = Auth::attempt(['email' => $login, 'password' => $password]) ||
                  Auth::attempt(['name' => $login, 'password' => $password]);

        \Log::info('Login result:', ['success' => $success]);

        return $success;
    }

    // ログイン失敗時のメッセージをカスタマイズ
    protected function sendFailedLoginResponse(Request $request)
    {
        return redirect()->back()
            ->withInput($request->only('login'))
            ->withErrors([
                'login' => [trans('auth.failed')],
            ]);
    }

    protected function authenticated(Request $request, $user)
    {
        // セッションに保存された元のURLがあればそこにリダイレクト
        if (session()->has('url.intended')) {
            $url = session()->pull('url.intended');
            return redirect($url);
        }

        // なければデフォルトのリダイレクト先へ
        return redirect()->intended(route('dashboard'));
    }
}