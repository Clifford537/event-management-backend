<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AttendeeStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Public endpoint
    }
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ];
    }
}