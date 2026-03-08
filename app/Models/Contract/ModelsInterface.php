<?php

namespace App\Models\Contract;

use App\Enums\OrderByEnum;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelsInterface
{
    /**
     * Helpers
     */

    /** 
     * Always returns 'self' as data type, but can have varying parameters
     */
    public static function add(): self;

    /** 
     * Always returns 'self' as data type, but can have varying parameters
     */
    public static function addOrUpdate(): self;

    /** 
     * Always a multi-parameter method
     */
    public static function updateInfo(): bool;

    /** 
     * Always requires $orderBy & $columns parameters, with $columns having a default value of ['*']
     * Other parameters can vary model wise, same goes for the return type
     */
    public static function getAll(OrderByEnum $orderBy, array $columns = ['*']);
}
