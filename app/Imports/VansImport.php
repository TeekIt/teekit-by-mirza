<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Van;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class VansImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Van([
            'company_id' => User::getAuthUser()->id,
            'user_name' => $row['userName'],
            'operative' => $row['operative'],
            'number_plate' => $row['numberPlate'],
            'payload' => $row['payload'],
            'width' => $row['width'],
            'height' => $row['height'],
            'length' => $row['length'],
            'password' => bcrypt($row['password']),
        ]);
    }

    public function rules(): array
    {
        return (new Van)->getValidationRules();
    }

    /**
     * limit the amount of queries in one time
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * limit the chunk size loaded into the memory at a time
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
