<?php

namespace App\Services;

use App\Products;

final class CsvFileServices
{
    public static function exportAsCsv(object $products, int $user_id)
    {
        $all_products = [];
        foreach ($products as $product) {
            $pt = json_decode(json_encode(Products::getProductInfo($user_id, $product->id, ['*'])->toArray()));
            unset($pt->category);
            unset($pt->ratting);
            unset($pt->id);
            unset($pt->user_id);
            unset($pt->created_at);
            unset($pt->updated_at);
            $temp_img = [];
            if (isset($pt->images)) {
                foreach ($pt->images as $img) $temp_img[] = $img->product_image;
            }
            $pt->images = implode(',', $temp_img);
            $all_products[] = $pt;
        }
        $destinationPath = public_path() . "/upload/csv/";
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }
        $file = time() . '_export.csv';
        return  self::jsonToCsv(json_encode($all_products), $destinationPath . $file, true);
    }

    public static function jsonToCsv($json, $csvFilePath = false, $boolOutputFile = false)
    {
        if (empty($json)) {
            die("The JSON string is empty!");
        }

        if (is_array($json) === false) {
            $json = json_decode($json, true);
        }

        $strTempFile = public_path() . "/upload/csv/" . 'csvOutput' . date("U") . ".csv";
        $f = fopen($strTempFile, "w+");
        $csvFilePath = $strTempFile;
        $firstLineKeys = false;

        foreach ($json as $line) {
            if (empty($firstLineKeys)) {
                $firstLineKeys = array_keys($line);
                fputcsv($f, array_map('strval', $firstLineKeys));
                $firstLineKeys = array_flip($firstLineKeys);
            }

            /* Using array_merge is important to maintain the order of keys according to the first element */
            // $line = array_map('strval', $line);
            fputcsv($f, array_merge($firstLineKeys, $line));
        }

        fclose($f);

        return response()->download($csvFilePath, null, ['Content-Type' => 'text/csv'])->deleteFileAfterSend();
    }
}
