<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class HorseGetOddFormRequest extends Request {

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
        $rules =
            [

                'venue_name'               => 'required',
                'event_date'               => 'required',
                'event_id'               => 'required',
               # 'table_type'               => 'required',
                'odd_type'               => 'required'
            ];



        return $rules;
    }

}
