<?php

namespace App\Controller;

use App\Entity\Ticket;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TicketController extends AbstractController
{
    /**
     * @Route("/ticket", name="app_ticket")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $repositiory = $doctrine->getRepository(Ticket::class);
        $tickets = $repositiory->findAll();

        dd($tickets);

        return $this->render('ticket/index.html.twig', [
            'controller_name' => 'TicketController',
        ]);
    }
}
