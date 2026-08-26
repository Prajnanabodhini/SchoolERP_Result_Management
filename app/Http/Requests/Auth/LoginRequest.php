<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'school' => [
                'required',
                'in:chikhali,shirgaon',
            ],

            'name' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ];
    }


    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        /*
        |--------------------------------------------------------------------------
        | RATE LIMIT
        |--------------------------------------------------------------------------
        */

        $this->ensureIsNotRateLimited();


        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        $user = \App\Models\User::where(
            'name',
            $this->name
        )->first();


        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        |
        | Keeping your existing password logic unchanged.
        |
        */

        if (
            !$user ||
            $user->password != $this->password
        ) {

            RateLimiter::hit(
                $this->throttleKey()
            );

            throw ValidationException::withMessages([
                'name' =>
                    'Invalid Username or Password.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $this->boolean('remember')
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR RATE LIMIT
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $this->throttleKey()
        );


        /*
        |--------------------------------------------------------------------------
        | STORE SELECTED SCHOOL
        |--------------------------------------------------------------------------
        */

        $this->storeSelectedSchool();
    }


    /**
     * Store selected school in session.
     */
    private function storeSelectedSchool(): void
    {
        $school =
            $this->input('school');


        /*
        |--------------------------------------------------------------------------
        | CHIKHALI
        |--------------------------------------------------------------------------
        */

        if (
            $school === 'chikhali'
        ) {

            session([
                'school_code' =>
                    'chikhali',

                'school_name' =>
                    'PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI',
            ]);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SHIRGAON
        |--------------------------------------------------------------------------
        */

        if (
            $school === 'shirgaon'
        ) {

            session([
                'school_code' =>
                    'shirgaon',

                'school_name' =>
                    'PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE SHIRGAON',
            ]);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SAFETY FALLBACK
        |--------------------------------------------------------------------------
        */

        session()->forget([
            'school_code',
            'school_name',
        ]);
    }


    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (
            !RateLimiter::tooManyAttempts(
                $this->throttleKey(),
                5
            )
        ) {
            return;
        }


        event(
            new Lockout($this)
        );


        $seconds =
            RateLimiter::availableIn(
                $this->throttleKey()
            );


        throw ValidationException::withMessages([
            'name' => trans(
                'auth.throttle',
                [
                    'seconds' =>
                        $seconds,

                    'minutes' =>
                        ceil(
                            $seconds / 60
                        ),
                ]
            ),
        ]);
    }


    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower(
                $this->string('name')
            )
            . '|'
            . $this->ip()
        );
    }
}