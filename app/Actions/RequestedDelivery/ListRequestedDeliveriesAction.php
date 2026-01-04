<?php

namespace App\Actions\RequestedDelivery;

use App\Enums\OrderByEnum;
use App\Models\RequestedDelivery;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListRequestedDeliveriesAction
{
    public function execute(
        OrderByEnum $orderByEnum,
        ?string $createdAt = null,
        ?int $creatorId = null,
        array $columns = ['*']
    ): LengthAwarePaginator {
        return RequestedDelivery::getForView(
            orderBy: $orderByEnum,
            createdAt: $createdAt,
            creatorId: $creatorId,
            columns: $columns
        );
    }
}
