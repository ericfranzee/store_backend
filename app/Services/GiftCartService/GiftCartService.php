<?php
declare(strict_types=1);

namespace App\Services\GiftCartService;

use App\Helpers\ResponseError;
use App\Models\GiftCart;
use App\Models\UserGiftCart;
use App\Services\CoreService;
use App\Traits\SetTranslations;
use Exception;
use Illuminate\Database\Eloquent\Model;

class GiftCartService extends CoreService
{
    use SetTranslations;

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return GiftCart::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $gifCart = $this->model()->create($data);

            $this->setTranslations($gifCart, $data);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $gifCart];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param GiftCart $giftCart
     * @param array $data
     * @return array
     */
    public function update(GiftCart $giftCart, array $data): array
    {
        try {
            $giftCart->update($data);

            $this->setTranslations($giftCart, $data);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $giftCart];
        }
        catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param GiftCart $giftCart
     * @param $userId
     * @return Model|UserGiftCart
     */
    public function attach(GiftCart $giftCart, $userId): Model|UserGiftCart
    {
        return UserGiftCart::create([
            'gift_cart_id' => $giftCart->id,
            'user_id'      => $userId,
            'expired_at'   => date('Y-m-d H:i:s', strtotime("+$giftCart->time")),
            'price'        => $giftCart->price,
            'active'       => 0
        ]);
    }

    /**
     * @param array $ids
     * @param int|null $shopId
     * @return void
     */
    public function delete(array $ids, ?int $shopId = null): void
    {
        GiftCart::whereIn('id', $ids)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->delete();
    }

}
