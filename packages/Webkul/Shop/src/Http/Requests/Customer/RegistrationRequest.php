<?php

namespace Webkul\Shop\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Webkul\Customer\Facades\Captcha;

class RegistrationRequest extends FormRequest
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
        $rules = [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'vat_number' => 'string|required',
            'address' => 'string|required',
            'postcode' => 'string|required',
            'city' => 'string|required',
            'country' => 'string|required',
            'website' => 'nullable|url',
            'email' => 'email|required|unique:customers,email,NULL,id,channel_id,'.core()->getCurrentChannel()->id,
            'password' => 'confirmed|min:6|required',
            'newsletter' => 'required|in:yes,no',
        ];

        return Captcha::getValidations($rules);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return Captcha::getValidationMessages();
    }
}
