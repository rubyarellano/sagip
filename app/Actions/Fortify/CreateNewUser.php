<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (empty($input['first_name']) && ! empty($input['name'])) {
            $parts = explode(' ', $input['name'], 2);
            $input['first_name'] = $parts[0];
            $input['last_name'] = $parts[1] ?? $parts[0];
        }

        if (empty($input['address'])) {
            $input['address'] = 'Default Address, Sorsogon';
        }

        if (empty($input['gender'])) {
            $input['gender'] = 'Other';
        }

        if (empty($input['birthday'])) {
            $input['birthday'] = '2000-01-01';
        }

        $input['name'] = trim(($input['first_name'] ?? '').' '.($input['last_name'] ?? ''));

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'birthday' => ['required', 'date', 'before:today'],
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'address' => $input['address'],
            'gender' => $input['gender'],
            'birthday' => $input['birthday'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => UserRole::Citizen->value, // Default newly registered users to citizen role
        ]);
    }
}
