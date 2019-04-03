<?php

namespace App\Http\Requests;


class ProjectRequest extends Request
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
            'name' => 'required|unique:projects|max:255',
            'group_id' => 'required|integer',
            'backend_name' => 'required|unique:projects|max:20',
            'desc' => 'max:255',
            'product_line' => 'required|max:255',
            'environment.test_servers.domain' => 'required',
            'environment.test_servers.servers' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名称必须',
            'product_line.required' => '产品线必须',
        ];
    }
}
