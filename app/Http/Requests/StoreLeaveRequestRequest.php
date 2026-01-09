<?php

namespace App\Http\Requests;


use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'reason' => ['required', 'string', 'min:10', 'max:255'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVacationRule($validator);
//            $this->validateProofRequirement($validator);
        });
    }

    private function validateVacationRule($validator): void
    {
        $leaveType = LeaveType::find($this->input('leave_type_id'));

        if (!$leaveType) {
            return;
        }

        $start = Carbon::parse($this->input('start_date'))->startOfDay();
        $today = Carbon::today();

        if (strtolower($leaveType->name) === 'vakantie') {
            $daysUntil = $today->diffInDays($start, false);
            if ($daysUntil < 30) {
                $validator->errors()->add(
                    'start_date',
                    'Vakantie moet minimaal 7 dagen van tevoren worden aangevraagd.'
                );
            }
        }


    }
}
