<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiFormRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'base.category_id' => 'required|integer',
            'base.project_id' => 'required|integer',
            'base.name' => 'required|max:200',
            'base.is_sign' => 'required',
            'base.is_auth' => 'required',
            'base.network' => 'required',
            'base.description' => 'required',
            'frontend.version' => 'required|max:10',
            'frontend.method' => 'required|max:10',
            'frontend.path' => 'required',
            'frontend.request.path' => 'array',
            'frontend.request.query' => 'array',
            'frontend.request.header' => 'array',
            'frontend.request.body' => 'array',
            'backend.server_path' => 'required',
            'backend.timeout' => 'required|integer',
            'response.response_type' => 'required|integer',
        ];
    }
}
