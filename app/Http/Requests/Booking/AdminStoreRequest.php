<?php
declare(strict_types=1);

namespace App\Http\Requests\Booking;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class AdminStoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->whereNot('id', request('master_id'))
            ],
        ] + (new StoreRequest)->rules();
    }
}
