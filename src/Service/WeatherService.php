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
            $result['wind']['direction'] = $this->getDirection($result['wind']['deg']);
        } elseif ($response->getStatusCode() == 404) {
            $result = [
                'error' => 'City not found.'
            ];
        } else {
            $result = [
                'error' => 'Error occured.'
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
                    'date' => $content['daily'][$i]['dt'],
                    'temp' => $content['daily'][$i]['temp']['day']
                ];
            }
        }

        return $result;
    }

    private function getDirection($bearing)
    {
        $cardinalDirections = array(
            'N' => array(337.5, 22.5),
            'NE' => array(22.5, 67.5),
            'E' => array(67.5, 112.5),
            'SE' => array(112.5, 157.5),
            'S' => array(157.5, 202.5),
            'SW' => array(202.5, 247.5),
            'W' => array(247.5, 292.5),
            'NW' => array(292.5, 337.5)
        );

        foreach ($cardinalDirections as $dir => $angles)
        {
            if ($bearing >= $angles[0] && $bearing < $angles[1])
            {
                $direction = $dir;
                break;
            }
        }
        return $direction;
    }
}