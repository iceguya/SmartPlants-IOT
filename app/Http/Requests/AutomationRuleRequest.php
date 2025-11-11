<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutomationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition_type' => 'required|in:soil_low,soil_high,temp_low,temp_high,hum_low,hum_high',
            'threshold_value' => 'required|numeric|min:0|max:100',
            'action_duration' => 'required|integer|min:1|max:60',
            'cooldown_minutes' => 'required|integer|min:5|max:1440',
        ];
    }

    public function messages(): array
    {
        return [
            'condition_type.required' => 'Please select a condition type.',
            'threshold_value.required' => 'Please enter a threshold value.',
            'threshold_value.min' => 'Threshold must be at least 0.',
            'threshold_value.max' => 'Threshold cannot exceed 100.',
            'action_duration.min' => 'Duration must be at least 1 second.',
            'action_duration.max' => 'Duration cannot exceed 60 seconds.',
            'cooldown_minutes.min' => 'Cooldown must be at least 5 minutes.',
            'cooldown_minutes.max' => 'Cooldown cannot exceed 24 hours.',
        ];
    }
}
