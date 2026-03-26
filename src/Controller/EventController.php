<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;


final class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $location = $request->query->get('location');
        $date = $request->query->get('date');
        $seats = $request->query->get('seats');

        $events = $eventRepository->searchEvents($location, $date, $seats);

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }
}
