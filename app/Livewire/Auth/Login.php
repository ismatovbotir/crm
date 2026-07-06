<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Введите email',
            'email.email' => 'Некорректный email',
            'password.required' => 'Введите пароль',
        ];
    }

    public function login()
    {
        $this->validate();

        $key = 'login.'.strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Слишком много попыток. Попробуйте через {$seconds} секунд.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key);
            $this->addError('email', 'Неверный email или пароль');
            return;
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        // Обновляем last_login_at
        Auth::user()->update(['last_login_at' => now()]);

        // Редирект по контуру
        if (\App\Helpers\Acl::isClient()) {
            return redirect()->intended('/portal');
        }

        return redirect()->intended('/admin');
    }

    public function fillDemo(string $email): void
    {
        if (! app()->environment('local')) return;

        $this->email    = $email;
        $this->password = 'password';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
