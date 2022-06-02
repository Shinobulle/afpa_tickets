<?php

namespace App\Controller;


use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/{_locale}/ticket", requirements={"_locale": "en|fr"})
 */
class TicketController extends AbstractController
{

    protected TicketRepository $ticketRepository;
    protected TranslatorInterface $ts;
    protected MailerInterface $mailer;

    public function __construct(TicketRepository $ticketRepository, TranslatorInterface $ts, MailerInterface $mailer, Registry $registry)
    {
        $this->ticketRepository = $ticketRepository;
        $this->ts = $ts;
        $this->mailer = $mailer;
        $this->registry = $registry;
    }

    /**
     * @Route("/", name="app_ticket")
     */
    public function index(): Response
    {
        // $user = $this->getUser();

        $tickets = $this->ticketRepository->findAll();

        // dd($tickets);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * @Route("/create", name="ticket_create")
     * @Route("/update/{id}", name="ticket_update", requirements={"id"="\d+"})
     */
    public function ticket(Ticket $ticket = null, Request $request): Response
    {
        if (!$ticket) {
            $ticket = new Ticket;

            $ticket->setTicketStatut("initial")
                ->setCreateAt(new \DateTimeImmutable());
            // $title = 'Création d\'un ticket';
            $title = $this->ts->trans("title.ticket.create");
            $flag = true;
        } else {
            // $title = "Update du ticket : {$ticket->getId()}";
            $title = $this->ts->trans("title.ticket.update") . " :  {$ticket->getId()}";
            $flag = false;
            $workflow = $this->registry->get($ticket, 'ticketTraitement');
            if ($ticket->getTicketStatut() != "wip") {
                $workflow->apply($ticket, 'to_wip');
            }
        }


        $form = $this->createForm(TicketType::class, $ticket, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //nouveauté Symfony 5.4
            $this->ticketRepository->add($ticket, true);

            if ($flag) {
                MailerController::sendEmail($this->mailer, "user1@test.fr", "Ticket ajouté", " a bien été ajouté", $ticket);
                $this->addFlash(
                    'success',
                    'Votre ticket a bien été ajouté'
                );
            } else {
                MailerController::sendEmail($this->mailer, "user1@test.fr", "Ticket modifié", " a bien été modifié", $ticket);
                $this->addFlash(
                    'info',
                    'Votre ticket a bien été mis à jour'
                );
            }

            return $this->redirectToRoute('app_ticket');
        }
        return $this->render('ticket/userForm.html.twig', [
            'form' => $form->createView(),
            'title' => $title
        ]);
    }

    /**
     * @Route ("/delete/{id}", name="ticket_delete", requirements={"id"="\d+"})
     */
    public function deleteTicket(Ticket $ticket): Response
    {

        $this->ticketRepository->remove($ticket, true);
        $this->addFlash(
            'danger',
            'Votre ticket a bien été supprimé'
        );
        MailerController::sendEmail($this->mailer, "user1@test.test", "Ticket Supprimé", " a bien été supprimé", $ticket);

        return $this->redirectToRoute('app_ticket');
    }

    /**
     * @Route("/details/{id}", name="ticket_detail", requirements={"id"="\d+"})
     */
    public function detailTicket(Ticket $ticket): Response
    {
        return $this->render('ticket/detail.html.twig', ['ticket' => $ticket]);
    }

    /** 
     * @Route("/close/{id}", name="ticket_close",requirements={"id"="\d+"})
     */
    public function closeTicket(Ticket $ticket): Response
    {
        $workflow = $this->registry->get($ticket, 'ticketTraitement');
        $workflow->apply($ticket, 'to_finished');
        $this->ticketRepository->add($ticket, true);

        return $this->redirectToRoute('app_ticket');
    }
}
