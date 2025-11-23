<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Log;
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Fortify's default validation, adapted for your 'username' field
        Validator::make($input, [
            'username' => ['required', 'string', 'max:255', 'unique:users'], // Using 'username' from your Vue form
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(), // Standard password rules
            'passwordConfirm' => ['required', 'same:password'], // Added to match your front-end model

            // Add validation for your T&C field if needed:
            // 'isTCAccepted' => ['accepted', 'required'],
        ])->validate();

        return User::create([
            'name' => $input['username'], // Map the 'username' field to the 'name' column
            'username' => $input['username'], // Map the 'username' field to the 'name' column
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}