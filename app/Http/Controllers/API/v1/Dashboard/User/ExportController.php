<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Repositories\OrderRepository\OrderRepository;

class ExportController extends UserBaseController
{

    public function __construct(private OrderRepository $repository)
    {
        parent::__construct();
    }

}
