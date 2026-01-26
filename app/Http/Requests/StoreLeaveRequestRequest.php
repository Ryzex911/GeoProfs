<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_date' => $this->input('start_date') ?? $this->input('from'),
            'end_date'   => $this->input('end_date') ?? $this->input('to'),
        ]);
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'reason'        => ['nullable', 'string', 'max:255'],

            'start_date'    => ['required', 'date', 'after_or_equal:today'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],

            // bestand (foto/video/pdf/word/ppt...)
            'proof' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx,mp4,mov,avi,webm',
                'max:51200', // 50MB
            ],

            // externe link (YouTube/Drive/website)
            'proof_link' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateVacationRule($validator);
            $this->validateProofRule($validator);
            $this->validateSickLeaveRules($validator);
        });
    }

    private function validateVacationRule($validator): void
    {
        $leaveType = LeaveType::find($this->input('leave_type_id'));
        if (!$leaveType) return;

        if (strtolower($leaveType->name) !== 'vakantie') return;

        $startDate = Carbon::parse($this->input('start_date'))->startOfDay();
        $today = Carbon::today();

        if ($today->diffInDays($startDate, false) < 7) {
            $validator->errors()->add('start_date', 'Vakantie moet minimaal 7 dagen van tevoren worden aangevraagd.');
        }
    }

    private function validateProofRule($validator): void
    {
        $leaveType = LeaveType::find($this->input('leave_type_id'));
        if (!$leaveType) return;

        $requires = (bool)($leaveType->requires_proof ?? false);
        if (!$requires) return;

        $hasFile = $this->hasFile('proof');
        $hasLink = filled($this->input('proof_link'));

        if (!$hasFile && !$hasLink) {
            $validator->errors()->add('proof', 'Bewijs is verplicht: upload een bestand of geef een externe link op.');
        }
    }

    protected function validateSickLeaveRules($validator): void
    {
        $leaveType = LeaveType::find($this->input('leave_type_id'));
        if (!$leaveType || strtolower($leaveType->name) !== 'ziek') return;

        $startDate = Carbon::parse($this->input('start_date'))->startOfDay();
        $endDate   = Carbon::parse($this->input('end_date'))->startOfDay();
        $today     = Carbon::today();
        $now       = Carbon::now();

        if (!$startDate->isSameDay($today)) {
            $validator->errors()->add('start_date', 'Ziekmeldingen kunnen alleen voor vandaag worden ingediend.');
        }

        if ($now->hour >= 9) {
            $validator->errors()->add('start_date', 'Ziekmeldingen voor vandaag moeten voor 09:00 uur worden ingediend.');
        }

        if (!$startDate->isSameDay($endDate)) {
            $validator->errors()->add('end_date', 'Ziekmeldingen kunnen alleen voor één dag tegelijk worden ingediend.');
        }
    }
}
