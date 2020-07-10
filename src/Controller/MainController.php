<?php

namespace App\Controller;

use App\Form\CityType;
use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @param WeatherService $weather
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, WeatherService $weather)
    {
        $form = $this->createForm(CityType::class);
        $form->handleRequest($request);
        $currentWeather = [];
        $forecast = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $currentWeather = $weather->getCurrent($formData['city']);
            if (isset($currentWeather['coord'])) {
                $forecast = $weather->getForecast($currentWeather['coord']['lat'], $currentWeather['coord']['lon']);
            }
        }

        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
            'weather' => $currentWeather,
            'forecast' => $forecast,
        ]);
    }
}
