<?php

namespace App\Services;

use App\Http\Controllers\UsersController;

final class GoogleMap
{
    // Sending Multiple requests to Google Matrix at a time
    public static function getDistanceForMultipleDestinations(float $origin_lat, float $origin_lon, array $destinations, int $miles)
    {
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destinations_addresses = implode('|', $destinations['users_coordinates']);
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destinations_addresses) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";
        // dd($url);
        $results = json_decode(file_get_contents($url), true);
        // dd($results);
        $distance_data = [];
        $user_data = [];
        foreach ($results['rows'] as $row) {
            foreach ($row['elements'] as $key => $element) {
                $meters = explode(' ', $element['distance']['value']);
                $distance_in_miles = (float)$meters[0] * 0.000621;

                $duration_in_seconds = explode(' ', $element['duration']['value']);
                $duration_in_minutes = round((int)$duration_in_seconds[0] / 60);
                if ($distance_in_miles <= $miles) {
                    $distance_data = [
                        // 'store_id' => $destinations['users'][$key]->id,
                        'distance' => $distance_in_miles,
                        'duration' => $duration_in_minutes
                    ];
                    $user_data[] = UsersController::getSellerInfo($destinations['users'][$key], $distance_data);
                }
            }
        }
        return $user_data;
    }

    public static function findDistanceByMakingChunks(float $lat, float $lon, object $users, int $chunk_size)
    {
        $data = [];
        $destination_data = [];
        $destination_users_data = [];
        $destination_users_coordinates = [];
        $total_users = $users->count();
        $remaining_users = $total_users;
        $offset = 0;
        $inner_loop_index = 0;
        while ($remaining_users > 0) {
            // The min() function will return the minimum value of both variables 
            $current_chunk_size = min($chunk_size, $remaining_users);
            // $offset === index number of the array, $current_chunk_size === size limit of the returned slice
            $current_users = $users->slice($offset, $current_chunk_size);
            $users_to_loop = count($current_users);
            while ($users_to_loop > 0) {
                $destination_users_data[] = $current_users[$inner_loop_index];
                $destination_users_coordinates[] = $current_users[$inner_loop_index]->lat . ',' . $current_users[$inner_loop_index]->lon;
                ++$inner_loop_index;
                --$users_to_loop;
            }
            $destination_data['users'] = $destination_users_data;
            $destination_data['users_coordinates'] = $destination_users_coordinates;

            $temp = self::getDistanceForMultipleDestinations($lat, $lon, $destination_data, 5);
            if (!empty($temp)) $data = $temp;
            $offset += $current_chunk_size;
            $remaining_users -= $current_chunk_size;
            $inner_loop_index = $offset;
        }
        return $data;
    }
}
