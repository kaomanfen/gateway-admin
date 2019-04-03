<?php

namespace App\Http\Requests;


class CaseRequest extends Request
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
            'api_id' => 'required|integer',
            'project_id' => 'required|integer',
            'name' => 'required|max:255',
            'collect_id' => 'required|integer',
            'env' => 'required',
            'method' => 'required',
            'request' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名称必须',
        ];
    }
}
