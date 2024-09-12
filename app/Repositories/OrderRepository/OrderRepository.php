<?php
declare(strict_types=1);

namespace App\Repositories\OrderRepository;

use App\Models\Language;
use App\Models\Order;
use App\Repositories\CoreRepository;
use App\Traits\SetCurrency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

class OrderRepository extends CoreRepository
{
    use SetCurrency;

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function getWith(?int $userId = null): array
    {
        $locale = Language::where('default', 1)->first()?->locale;

        return [
            'user',
            'currency',
            'review' => fn($q) => $userId ? $q->where('user_id', $userId) : $q,
            'shop:id,latitude,longitude,tax,background_img,open,logo_img,uuid,phone,delivery_type,delivery_time',
            'shop.translation' => fn($q) => $q
                ->select([
                    'id',
                    'shop_id',
                    'locale',
                    'title',
                    'address',
                ])
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'orderDetails' => fn($q) => $q->with([
                'galleries',
                'stock.stockExtras.value',
                'stock.product.translation' => fn($q) => $q
                    ->select([
                        'id',
                        'product_id',
                        'locale',
                        'title',
                    ])
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    })),
                'stock.stockExtras.group.translation' => function ($q) use ($locale) {
                    $q
                        ->select('id', 'extra_group_id', 'locale', 'title')
                        ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                            $q->where('locale', $this->language)->orWhere('locale', $locale);
                        }));
                },
                'replaceStock.stockExtras.value',
                'replaceStock.product.translation' => fn($q) => $q
                    ->select([
                        'id',
                        'product_id',
                        'locale',
                        'title',
                    ])
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    })),
                'replaceStock.stockExtras.group.translation' => function ($q) use ($locale) {
                    $q
                        ->select('id', 'extra_group_id', 'locale', 'title')
                        ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                            $q->where('locale', $this->language)->orWhere('locale', $locale);
                        }));
                },
            ]),
            'deliveryman.deliveryManSetting',
            'orderRefunds',
            'transaction.paymentSystem',
            'galleries',
            'myAddress',
            'deliveryPrice',
            'deliveryPoint.workingDays',
            'deliveryPoint.closedDates',
            'coupon',
            'pointHistories',
            'notes'
        ];
    }
    /**
     * @param array $filter
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public function ordersList(array $filter = []): array|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model()
            ->filter($filter)
            ->with([
                'deliveryman',
            ])
            ->get();
    }

    /**
     * This is only for users route
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function ordersPaginate(array $filter = []): LengthAwarePaginator
    {
        /** @var Order $order */
        $order = $this->model();

        return $order
            ->withCount('orderDetails')
            ->with([
                'children:id,total_price,parent_id',
                'shop:id,logo_img',
                'shop.translation' => fn($q) => $q->select([
                    'title',
                    'locale',
                    'shop_id',
                    'id',
                ])->where('locale', $this->language),
                'currency',
                'user:id,firstname,lastname,uuid,img,phone',
            ])
            ->filter($filter)
            ->orderBy($filter['column'] ?? 'id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * This is only for users route
     * @param array $filter
     * @return Paginator
     */
    public function simpleOrdersPaginate(array $filter = []): Paginator
    {
        /** @var Order $order */
        $order = $this->model();

        return $order
            ->filter($filter)
            ->select([
                'id',
                'user_id',
                'total_price',
                'delivery_date',
                'total_tax',
                'currency_id',
                'rate',
                'status',
                'total_discount',
            ])
            ->simplePaginate($filter['perPage'] ?? 10);
    }

    /**
     * @param int $id
     * @param int|null $shopId
     * @param int|null $userId
     * @return Order|null
     */
    public function orderById(int $id, ?int $shopId = null, ?int $userId = null): ?Order
    {
        return $this->model()
            ->with($this->getWith($userId))
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->find($id);
    }

    /**
     * @param int $id
     * @param int|null $shopId
     * @param int|null $userId
     * @return Collection|null
     */
    public function ordersByParentId(int $id, ?int $shopId = null, ?int $userId = null): ?Collection
    {
        return $this->model()
            ->with($this->getWith($userId))
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->where(fn($q) => $q->where('id', $id)->orWhere('parent_id', $id))
            ->orderBy('id', 'asc')
            ->get();
    }

}
