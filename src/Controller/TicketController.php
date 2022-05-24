<?php

namespace App\Controller;


use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/ticket/create", name="ticket_create")
     */
    public function createTicket(Request $request)
    {
        $ticket = new Ticket;

        $ticket->setIsActive(true)
            ->setCreateAt(new \DateTimeImmutable());

        $form = $this->createForm(TicketType::class, $ticket, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //nouveauté Symfony 5.4
            $this->ticketRepository->add($ticket, true);

            return $this->redirectToRoute('app_ticket');
        }
        return $this->render('ticket/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ticket/update/{id}", name="ticket_update", requirements={"id"="\d+"})
     */
    public function updateTicket(Int $id, Request $request)
    {
        $ticket = $this->ticketRepository->findBy($id);
        dd($ticket);
    }
}
