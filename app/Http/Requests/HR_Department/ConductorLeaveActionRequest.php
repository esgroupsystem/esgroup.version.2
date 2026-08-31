<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use App\Enums\LeaveActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConductorLeaveActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiresProof = LeaveActionType::tryFrom((string) $this->input('action_type'))?->requiresProof() ?? false;

        return [
            'action_type' => [
                'required',
                Rule::enum(LeaveActionType::class),
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'proof_image' => [
                $requiresProof ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'action_type.required' => 'Select a valid leave action.',
            'action_type.in' => 'The selected leave action is invalid.',
            'proof_image.required' => 'Picture proof is required for warning notices.',
            'proof_image.image' => 'The proof must be a valid image.',
            'proof_image.mimes' => 'The proof must be a JPG, JPEG, PNG, or WEBP image.',
            'proof_image.max' => 'The proof image must not exceed 4 MB.',
        ];
    }
}
