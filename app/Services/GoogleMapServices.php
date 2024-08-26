<?php

namespace App\Services;

use App\Http\Controllers\UsersController;

final class GoogleMapServices
{
    /* Rameesha's URL */
    private const GOOGLE_DISTANCEMATRIX_API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    private const GOOGLE_DISTANCEMATRIX_API_KEY = 'AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U';

    public static function generateUrl($origing_address, $destination_address)
    {
        return self::GOOGLE_DISTANCEMATRIX_API_URL . '?units=imperial&origins=' . urlencode($origing_address) . '&destinations=' . urlencode($destination_address) . '&mode=driving&key=' . self::GOOGLE_DISTANCEMATRIX_API_KEY;
    }
    /**
     * It will fetch the curved distance between 2 points
     * Google distance matrix API is consumed
     * @author Muhammad Abdullah Mirza
     */
    public static function getDistanceInArray(float $origin_lat, float $origin_lon, float $destination_lat, float $destination_lon)
    {
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destination_address = $destination_lat . ',' . $destination_lon;

        $url = self::generateUrl($origing_address, $destination_address);
        $results = json_decode(file_get_contents($url), true);
        $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
        $distanceInMiles = (float)$meters[0] * 0.000621;

        $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
        $durationInMinutes = round((int)$durationInSeconds[0] / 60);
        return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    }

    public static function getDistanceInMiles(float $origin_lat, float $origin_lon, float $destination_lat, float $destination_lon)
    {
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destination_address = $destination_lat . ',' . $destination_lon;

        $url = self::generateUrl($origing_address, $destination_address);
        $results = json_decode(file_get_contents($url), true);
        $meters = $results['rows'][0]['elements'][0]['distance']['value'];
        $distanceInMiles = $meters * 0.000621;

        return (float) $distanceInMiles;
    }
    /*
     * Sending Multiple requests to Google Matrix at a time
     */
    public static function getDistanceForMultipleDestinations(float $origin_lat, float $origin_lon, array $destinations, int $miles)
    {
        $user_data = [];

        $origin_address = "{$origin_lat},{$origin_lon}";
        $destinations_addresses = implode('|', $destinations['users_coordinates']);

        $url = self::generateUrl($origin_address, $destinations_addresses);
        $results = json_decode(file_get_contents($url), true);

        if (isset($results['rows'][0]['elements']) && is_array($results['rows'][0]['elements'])) {
            foreach ($results['rows'][0]['elements'] as $key => $element) {
                if ($element['status'] === 'OK' && isset($destinations['users'][$key])) {

                    $distance_in_miles = $element['distance']['value'] * 0.000621371;
                    $duration_in_minutes = round($element['duration']['value'] / 60);

                    if ($distance_in_miles <= $miles) {
                        $distance_data = [
                            'distance' => $distance_in_miles,
                            'duration' => $duration_in_minutes
                        ];
                        $user_data[] = UsersController::getSellerInfo($destinations['users'][$key], $distance_data);
                    }
                }
            }
        }

        return $user_data;
    }
    /*
     * $chunk_size > 25 is not allowed
     * Because Google distance matrix API does not support destinations more then 25
     */
    public static function findDistanceByMakingChunks(float $lat, float $lon, object $users, int $chunk_size = 25)
    {
        if ($chunk_size > 25) return [];

        $all_user_data = [];

        $chunks = $users->chunk($chunk_size);
        foreach ($chunks as $chunk) {
            $destination_data = [
                'users' => $chunk->values(),
                'users_coordinates' => $chunk->map(fn($user) => "{$user->lat},{$user->lon}")->values()->toArray(),
            ];

            $temp = self::getDistanceForMultipleDestinations($lat, $lon, $destination_data, 5);
            $all_user_data = array_merge($all_user_data, $temp);
        }

        return $all_user_data;
    }
}
