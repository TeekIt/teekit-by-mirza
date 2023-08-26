<?php

namespace App\Services;

use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;

final class GoogleMap
{
    // Sending Multiple requests to Google Matrix at a time
    public static function getDistanceForMultipleDestinations(float $origin_lat, float $origin_lon, array $destinations, int $miles)
    {
        // dd($destinations);
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destinations_addresses = implode('|', $destinations['users_coordinates']);
        // dd($destinations_addresses);
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destinations_addresses) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";
        // dd($url);
        $results = json_decode(file_get_contents($url), true);
        // dd($results);
        $distanceData = [];
        $user_data = [];
        foreach ($results['rows'] as $row) {
            foreach ($row['elements'] as $key => $element) {
                $meters = explode(' ', $element['distance']['value']);
                $distanceInMiles = (float)$meters[0] * 0.000621;

                $durationInSeconds = explode(' ', $element['duration']['value']);
                $durationInMinutes = round((int)$durationInSeconds[0] / 60);
                if ($distanceInMiles <= $miles) {
                    $distanceData = [
                        // 'store_id' => $destinations['users'][$key]->id,
                        'distance' => $distanceInMiles,
                        'duration' => $durationInMinutes
                    ];
                    $user_data[] = UsersController::getSellerInfo($destinations['users'][$key], $distanceData);
                }
            }
        }
        return $user_data;
    }

    public static function findDistanceByMakingChunks(Request $request, object $users, int $chunk_size)
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

            $temp = self::getDistanceForMultipleDestinations($request->query('lat'), $request->query('lon'), $destination_data, 5);
            if (!empty($temp)) $data = $temp;
            $offset += $current_chunk_size;
            $remaining_users -= $current_chunk_size;
            $inner_loop_index = $offset;
        }
        return $data;
    }
}
