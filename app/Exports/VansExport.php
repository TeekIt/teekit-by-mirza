<?php

namespace App\Exports;

use App\Enums\OrderByEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\Van;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VansExport implements FromQuery, WithHeadings
{
    public function query(): Builder|EloquentBuilder|Relation|ScoutBuilder
    {
        $user = User::getAuthUser();

        if ($user->role_id === UserRoleEnum::SUPERADMIN->value) {
            return Van::query()
                ->select($this->getColumns())
                ->orderBy('created_at', OrderByEnum::DESC->value);
        }

        return Van::query()
            ->select($this->getColumns())
            ->where('company_id', '=', $user->id)
            ->orderBy('created_at', OrderByEnum::DESC->value);
    }

    public function getColumns(): array
    {
        return [
            'user_name',
            'operative',
            'number_plate',
            'payload',
            'width',
            'height',
            'length',
        ];
    }

    public function headings(): array
    {
        return [
            'User Name',
            'Operative',
            'Number Plate',
            'Payload',
            'Width',
            'Height',
            'Length',
        ];
    }
}
