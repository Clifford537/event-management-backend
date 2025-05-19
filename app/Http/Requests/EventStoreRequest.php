<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class EventStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'date' => 'required|date|after:now',
            'price' => 'required|numeric|min:0',
            'max_attendees' => 'required|integer|min:1',
            'status' => 'in:draft,published,cancelled',
        ];
    }
}