<?php
declare(strict_types=1);

namespace App\Repositories\UserActivityRepository;

use App\Models\UserActivity;
use App\Repositories\CoreRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class UserActivityRepository extends CoreRepository
{

    protected function getModelClass(): string
    {
        return UserActivity::class;
    }

    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return $this->model()
            ->filter($filter)
            ->orderBy($filter['column'] ?? 'id', $filter['sort'] ?? 'desc')
            ->paginate($filter['perPage'] ?? 10);
    }
}
