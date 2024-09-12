<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Booking\StoreRequest as BookingStoreRequest;
use App\Http\Requests\Order\StoreRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $userId         = auth('sanctum')->id();

        $cartId         = request('cart_id');
        $bookingId      = request('booking_id');
        $memberShipId   = request('member_ship_id');
        $giftCartId     = request('gift_cart_id');
        $parcelId       = request('parcel_id');
        $subscriptionId = request('subscription_id');
        $adsPackageId   = request('ads_package_id');
        $walletId       = request('wallet_id');
        $tips           = request('tips');

        $rules = [];

        if ($cartId) {
            $rules = (new StoreRequest)->rules();
        } else if ($parcelId) {
            $rules = (new BookingStoreRequest)->rules();
        }

        return [
            'cart_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$adsPackageId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('carts', 'id')->where('owner_id', $userId)
            ],
            'booking_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('bookings', 'id')->where('user_id', $userId)->when(!$tips, fn($q) => $q->whereNull('parent_id'))
            ],
            'gift_cart_id' => [
                 !$tips && !$memberShipId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('gift_carts', 'id')->where('active', true)
            ],
            'member_ship_id' => [
                !$giftCartId && !$tips && !$bookingId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('member_ships', 'id')->where('active', true)
            ],
            'parcel_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$cartId && !$adsPackageId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('parcel_orders', 'id')->where('user_id', $userId)
            ],
            'subscription_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$cartId && !$adsPackageId && !$parcelId && !$walletId ? 'required' : 'nullable',
                Rule::exists('subscriptions', 'id')->where('active', true)
            ],
            'ads_package_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$cartId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('ads_packages', 'id')->where('active', true)
            ],
            'wallet_id' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId ? 'required' : 'nullable',
                Rule::exists('wallets', 'id')->where('user_id', auth('sanctum')->id())
            ],
            'total_price' => [
                !$giftCartId && !$tips && !$memberShipId && !$bookingId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId ? 'required' : 'nullable',
                'numeric'
            ],
            'tips' => [
                !$giftCartId && !$memberShipId && !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId ? 'required' : 'nullable',
                'max:100'
            ],
        ] + $rules;
    }

}
