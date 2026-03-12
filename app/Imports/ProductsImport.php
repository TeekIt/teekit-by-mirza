<?php

namespace App\Imports;

use App\Models\Products;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    public function __construct(public int $seller_id) {}

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $product = Products::add([
            'seller_id' => $this->seller_id,
            'category_id' => $row['category_id'],
            'product_name' => $row['product_name'],
            'sku' => $row['sku'],
            'price' => str_replace(',', '', $row['price']),
            'discount_percentage' => ($row['discount_percentage'] == '') ? 0 : $row['discount_percentage'],
            'weight' => $row['weight'],
            'brand' => $row['brand'],
            'size' => ($row['size'] == 'null') ? null : $row['size'],
            'status' => $row['status'],
            'contact' => $row['contact'],
            'colors' => ($row['color'] == 'null') ? null : $row['color'],
            'bike' => $row['bike'],
            'car' => $row['car'],
            'van' => $row['van'],
            'feature_img' => $row['product_image'],
            'height' => $row['height'],
            'width' => $row['width'],
            'length' => $row['length'],
        ]);

        $product->quantities()->create([
            'seller_id' => $this->seller_id,
            'category_id' => $row['category_id'],
            'qty' => ($row['qty'] == '') ? 0 : $row['qty'],
        ]);
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
