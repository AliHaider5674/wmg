<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Order request that verify request data is in the right format
 *
 * Class OrderRequest
 * @category WMG
 * @package  App\Http\Requests
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderRequest extends FormRequest
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
            'request_id' => 'required',
            'sales_channel' => 'required',
            'order_id' => 'required',
            'source_id' => 'required',
            'shipping_method' => 'required',
            'shipping_address' => 'required',
            'items' => 'required',
            'items.*.order_line_id' => 'required',
            'items.*.sku' => 'required',
            'items.*.name' => 'required',
            'items.*.order_line_price.net_amount' => 'required',
            'items.*.order_line_price.currency' => 'required',
            'items.*.order_line_price.quantity' => 'required',
        ];
    }
}
