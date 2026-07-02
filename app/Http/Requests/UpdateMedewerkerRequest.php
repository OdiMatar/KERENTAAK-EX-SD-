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
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
