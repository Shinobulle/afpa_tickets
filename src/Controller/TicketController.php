<?php

namespace App\Controller;


use App\Repository\TicketRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TicketController extends AbstractController
{

    protected TicketRepository $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @Route("/ticket", name="app_ticket")
     */
    public function index(): Response
    {
        $tickets = $this->ticketRepository->findAll();

        // dd($tickets);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}
