<?php

namespace App\Http\Requests;

use App\Models\Medewerker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedewerkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:medewerkers,email,'.$this->route('medewerker')->id],
            'role' => ['required', 'string', 'max:50', Rule::in(array_keys(Medewerker::roles()))],
            'phone' => ['nullable', 'digits:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.digits' => 'Het telefoonnummer moet uit 10 cijfers bestaan',
            'email.unique' => 'Dit e-mailadres is al in gebruik',
        ];
    }
}
