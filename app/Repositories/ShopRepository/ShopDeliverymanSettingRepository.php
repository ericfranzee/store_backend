<?php
declare(strict_types=1);

namespace App\Repositories\ShopRepository;

use App\Models\ShopDeliverymanSetting;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopDeliverymanSettingRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return ShopDeliverymanSetting::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        /** @var ShopDeliverymanSetting $models */
        $models = $this->model();

        return $models
            ->filter($filter)
            ->orderBy($filter['column'] ?? 'id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }

    /**
     * @param ShopDeliverymanSetting $model
     * @return ShopDeliverymanSetting|null
     */
    public function show(ShopDeliverymanSetting $model): ShopDeliverymanSetting|null
    {
        return $model;
    }
}
