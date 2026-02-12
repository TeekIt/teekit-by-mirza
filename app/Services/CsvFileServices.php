<?php

namespace App\Services;

use App\Models\Products;

final class CsvFileServices
{
    public static function exportAsCsv(object $products, int $sellerId)
    {
        $allProducts = [];

        foreach ($products as $product) {
            $product = json_decode(json_encode(Products::getProductInfoWithRelations($sellerId, $product->id, ['*'])->toArray()));
            
            unset($product->category);
            unset($product->ratting);
            unset($product->id);
            unset($product->user_id);
            unset($product->created_at);
            unset($product->updated_at);

            $tempImgs = [];
            if (isset($product->images)) {
                foreach ($product->images as $singleIndex) {
                    $tempImgs[] = $singleIndex->product_image;
                }
            }

            $product->images = implode(',', $tempImgs);
            $allProducts[] = $product;
        }

        $destinationPath = public_path().'/upload/csv/';
        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $fileName = time().'_export.csv';

        return self::jsonToCsv(json_encode($allProducts), $destinationPath.$fileName, true);
    }

    public static function jsonToCsv($json, $csvFilePath = false)
    {
        if (empty($json)) {
            exit('The JSON string is empty!');
        }

        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }

        $strTempFile = public_path().'/upload/csv/'.'csvOutput'.date('U').'.csv';
        $file = fopen($strTempFile, 'w+');
        $csvFilePath = $strTempFile;
        $firstLineKeys = false;

        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($file, array_map('strval', $firstLineKeys));
                $firstLineKeys = array_flip($firstLineKeys);
            }

            /* Using array_merge is important to maintain the order of keys according to the first element */
            // $line = array_map('strval', $line);
            fputcsv($file, array_merge($firstLineKeys, $line));
        }

        fclose($file);

        return response()->download($csvFilePath, null, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
    }
}
