<?php

namespace App\Http\Requests;

class RunFormRequest extends Request
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
            'path' => 'required',
            'host' => 'required',
            'method' => 'required',
            'server_ip' => 'required',
        ];
    }
}
