<?php
declare(strict_types=1);

namespace App\Services\ModelService;

use App\Helpers\ResponseError;
use App\Models\Service;
use App\Services\CoreService;
use App\Services\ShopServices\ShopService;
use App\Traits\SetTranslations;
use DB;
use Exception;
use Throwable;

class ModelService extends CoreService
{
    use SetTranslations;

    protected function getModelClass(): string
    {
        return Service::class;
    }

    public function create(array $data): array
    {
        try {
            $model = DB::transaction(function () use ($data) {
                /** @var Service $model */
                $model = $this->model()->create($data);

                (new ShopService)->updateShopPrices($model);

                $this->setTranslations($model, $data);

                if ($model && data_get($data, 'images.0')) {
                    $model->update(['img' => data_get($data, 'previews.0') ?? data_get($data, 'images.0')]);
                    $model->uploads(data_get($data, 'images'));
                }

                return $model;
            });

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        } catch (Throwable $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param Service $service
     * @param array $data
     * @return array
     */
    public function update(Service $service, array $data): array
    {
        try {
            $service = DB::transaction(function () use ($service, $data) {
                $service->update($data);

                (new ShopService)->updateShopPrices($service);

                $this->setTranslations($service, $data);

                if (data_get($data, 'images.0')) {
                    $service->galleries()->delete();
                    $service->update(['img' => data_get($data, 'previews.0') ?? data_get($data, 'images.0')]);
                    $service->uploads(data_get($data, 'images'));
                }

                return $service;
            });

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $service];
        }
        catch (Throwable $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    public function delete(array $ids = []): array
    {
        try {
            $services = Service::whereIn('id', data_get($ids, 'ids', []))
                ->when(data_get($ids, 'shop_id'),   fn($q, $shopId) => $q->where('shop_id', $shopId))
                ->get();

            foreach ($services as $service) {
                $service->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

}
