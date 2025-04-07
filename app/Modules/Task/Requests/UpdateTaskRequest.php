<?php

namespace App\Modules\Task\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           
            'id' => 'required',
            'title' => 'required',
            'status'=>['required','string','in:pending,in-progress,completed'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The payment id is required.',
            'title.required' => 'The title is required.',
            'status.required' => 'The status field is required.',
       ];
    }
}