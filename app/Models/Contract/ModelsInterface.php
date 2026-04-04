<?php

namespace App\Models\Contract;

use App\Enums\OrderByEnum;

interface ModelsInterface
{
    /** 
     * Can have multi-parameters or a single parameter
     */
    public static function add(): self;

    /** 
     * Always have multi-parameters
     */
    public static function addOrUpdate(): self;

     /** 
     * Always requires $id & $columns parameters, with $columns having a default value of ['*']
     * No other parameters are allowed
     */
    public static function getById(int $id, array $columns = ['*']): self;

    /** 
     * Always requires $orderBy & $columns parameters, with $columns having a default value of ['*']
     * Other parameters can vary model wise, same goes for the return type
     */
    public static function getAll(OrderByEnum $orderBy, array $columns = ['*']);

    /** 
     * Always have multi-parameters
     */
    public static function updateInfo(): bool;

    /** 
     * Always requires $id parameter
     * No other parameters are allowed
     */
    public static function deleteTemporarily(int $id): bool;

    /** 
     * Always requires $id parameter
     * No other parameters are allowed
     */
    public static function deletePermanently(int $id): bool;
}
