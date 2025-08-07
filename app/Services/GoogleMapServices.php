<?php

namespace App\Services;

use App\Http\Controllers\UsersController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class GoogleMapServices
{
    private const GOOGLE_DISTANCEMATRIX_API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public static function getApiKey(): string
    {
        return config('google.GOOGLE_DISTANCEMATRIX_API_KEY');
    }

    public static function generateUrl($originAddress, $destinationAddress)
    {
        return self::GOOGLE_DISTANCEMATRIX_API_URL . '?units=imperial&origins=' . urlencode($originAddress) . '&destinations=' . urlencode($destinationAddress) . '&mode=driving&key=' . self::getApiKey();
    }
    /**
     * @param Collection<User> $sellersOfSameCity
     */
    public static function getNearBySellers(float $buyerLat, float $buyerLon, Collection $sellersOfSameCity, int $currentSellerId): array
    {
        return Cache::remember(
            'getNearBySellers' . $currentSellerId . $buyerLat . $buyerLon,
            Carbon::now()->addDay(),
            function () use ($buyerLat, $buyerLon, $sellersOfSameCity) {
                /* 
                 * This function will not work with "faker" generated 
                 * customer lat, lon
                 */
                return static::findNearByUsersByMakingChunks(
                    $buyerLat,
                    $buyerLon,
                    $sellersOfSameCity,
                    25
                );
            }
        );
    }
    /**
     * It will fetch the curved distance between 2 points
     * Google distance matrix API is consumed
     * @author Muhammad Abdullah Mirza
     */
    public static function getDistanceInArray(float $originLat, float $originLon, float $destinationLat, float $destinationLon)
    {
        $originAddress = $originLat . ',' . $originLon;
        $destinationAddress = $destinationLat . ',' . $destinationLon;

        $url = self::generateUrl($originAddress, $destinationAddress);
        $results = json_decode(file_get_contents($url), true);
        $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
        $distanceInMiles = (float)$meters[0] * 0.000621;

        $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
        $durationInMinutes = round((int)$durationInSeconds[0] / 60);
        return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    }

    public static function getDistanceInMiles(float $originLat, float $originLon, float $destinationLat, float $destinationLon)
    {
        $originAddress = $originLat . ',' . $originLon;
        $destinationAddress = $destinationLat . ',' . $destinationLon;

        $url = self::generateUrl($originAddress, $destinationAddress);
        $results = json_decode(file_get_contents($url), true);
        $meters = $results['rows'][0]['elements'][0]['distance']['value'];
        $distanceInMiles = $meters * 0.000621;

        return (float) $distanceInMiles;
    }
    /*
     * Sending Multiple requests to Google Matrix at a time
     */
    public static function getNearByUsersFromMultipleDestinations(float $originLat, float $originLon, array $destinations, int $nearByMiles)
    {
        $userData = [];

        $originAddress = "{$originLat},{$originLon}";
        $destinationsAddresses = implode('|', $destinations['users_coordinates']);

        $url = self::generateUrl($originAddress, $destinationsAddresses);
        $results = json_decode(file_get_contents($url), true);

        if (isset($results['rows'][0]['elements']) && is_array($results['rows'][0]['elements'])) {
            foreach ($results['rows'][0]['elements'] as $key => $element) {
                if ($element['status'] === 'OK' && isset($destinations['users'][$key])) {

                    $distanceInMiles = $element['distance']['value'] * 0.000621371;
                    $durationInMinutes = round($element['duration']['value'] / 60);

                    if ($distanceInMiles <= $nearByMiles) {
                        $distanceData = [
                            'distance' => $distanceInMiles,
                            'duration' => $durationInMinutes
                        ];
                        $userData[] = UsersController::getSellerInfo($destinations['users'][$key], $distanceData);
                    }
                }
            }
        }

        return $userData;
    }
    /*
     * $chunk_size > 25 is not allowed
     * Because Google distance matrix API does not support destinations more then 25
     */
    public static function findNearByUsersByMakingChunks(
        float $lat,
        float $lon,
        Collection $users,
        int $chunkSize = 25,
        int $nearByMiles = 3
    ): array {
        if ($chunkSize > 25) return [];

        $allUserData = [];

        $chunks = $users->chunk($chunkSize);
        foreach ($chunks as $chunk) {
            $destinationData = [
                'users' => $chunk->values(),
                'users_coordinates' => $chunk->map(fn($user) => "{$user->lat},{$user->lon}")->values()->toArray(),
            ];

            $temp = self::getNearByUsersFromMultipleDestinations($lat, $lon, $destinationData, $nearByMiles);
            $allUserData = array_merge($allUserData, $temp);
        }

        return $allUserData;
    }
}
