<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'behandeling_id' => ['required', 'integer', 'exists:behandelingen,id'],
            'medewerker_id' => ['required', 'integer', 'exists:medewerkers,id'],
            'datum' => ['required', 'date', 'after_or_equal:today'],
            'starttijd' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'behandeling_id' => 'behandeling',
            'medewerker_id' => 'medewerker',
            'datum' => 'datum',
            'starttijd' => 'starttijd',
        ];
    }
}
