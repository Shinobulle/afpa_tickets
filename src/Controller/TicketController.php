<?php

namespace App\Controller;


use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
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

    public function __construct(TicketRepository $ticketRepository, TranslatorInterface $ts, MailerInterface $mailer)
    {
        $this->ticketRepository = $ticketRepository;
        $this->ts = $ts;
        $this->mailer = $mailer;
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

            $ticket->setIsActive(true)
                ->setCreateAt(new \DateTimeImmutable());
            // $title = 'Création d\'un ticket';
            $title = $this->ts->trans("title.ticket.create");
            $flag = true;
        } else {
            // $title = "Update du ticket : {$ticket->getId()}";
            $title = $this->ts->trans("title.ticket.update") . " :  {$ticket->getId()}";
            $flag = false;
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
}
