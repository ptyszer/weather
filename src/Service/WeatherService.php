<?php


namespace App\Service;


use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5/';
    private $httpClient;

    /**
     * WeatherService constructor.
     * @param $apiKey
     * @param HttpClientInterface $httpClient
     */
    public function __construct($apiKey, HttpClientInterface $httpClient)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
    }

    private function request($urlKeyword, $options)
    {
        $baseOtions = [
            'appid' => $this->apiKey,
            'units' => 'metric',
        ];
        $options = array_merge($baseOtions, $options);

        return $this->httpClient->request(
            'GET',
            $this->baseUrl . $urlKeyword,
            ['query' => $options]
        );
    }

    public function getCurrent($city)
    {
        $options = [
            'q' => $city,
        ];
        $response = $this->request('weather', $options);

        if ($response->getStatusCode() == 200 && $response->getContent()) {
            $result = json_decode($response->getContent(), true);
        } elseif ($response->getStatusCode() == 404) {
            $result = [
                'error' => 'Nie znaleziono miasta.'
            ];
        } else {
            $result = [
                'error' => 'Wystąpił błąd, spróbuj wyszukać ponownie.'
            ];
        }

        return $result;
    }

    public function getForecast($lat, $lon)
    {
        $result = [];
        $options = [
            'lat' => $lat,
            'lon' => $lon,
            'exclude' => 'hourly,current'
        ];
        $response = $this->request('onecall', $options);

        if ($response->getStatusCode() == 200 && $response->getContent()) {
            $content = json_decode($response->getContent(), true);
            for ($i = 1; $i <= 5; $i++) {
                $result[] = [
                    'date' => date("l d.m", $content['daily'][$i]['dt']),
                    'temp' => $content['daily'][$i]['temp']['day']
                ];
            }
        }

        return $result;
    }

}