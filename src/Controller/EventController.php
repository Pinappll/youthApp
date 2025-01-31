<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChurchRepository;
use App\Entity\Attendance;
use App\Entity\Youth;
use App\Form\EventType;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        $events = in_array('ROLE_ADMIN', $user->getRoles())
            ? $eventRepository->findBy([], ['date' => 'DESC'])
            : $eventRepository->findBy(['sector' => $user->getSector()], ['date' => 'DESC']);

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setSector($this->getUser()->getSector());
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('app_event_index');
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    #[IsGranted('view', 'event')]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'event')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setLastModifiedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('app_event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    #[IsGranted('delete', 'event')]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_event_index');
    }

    #[Route('/{id}/attendance', name: 'app_event_attendance', methods: ['GET', 'POST'])]
    #[IsGranted('view', 'event')]
    public function attendance(
        Event $event,
        Request $request,
        EntityManagerInterface $entityManager,
        ChurchRepository $churchRepository
    ): Response {
        $user = $this->getUser();
        $sector = $event->getSector();
        
        // Récupérer les églises du secteur
        $churches = $churchRepository->findBy(['sector' => $sector]);
        
        // Récupérer les présences existantes
        $attendances = [];
        foreach ($event->getAttendances() as $attendance) {
            $attendances[$attendance->getYouth()->getId()] = $attendance;
        }

        if ($request->isMethod('POST')) {
            $attendanceData = $request->request->get('attendance', []);
            
            foreach ($attendanceData as $youthId => $data) {
                $youth = $entityManager->getReference(Youth::class, $youthId);
                
                // Créer ou mettre à jour la présence
                if (!isset($attendances[$youthId])) {
                    $attendance = new Attendance();
                    $attendance->setEvent($event)
                        ->setYouth($youth)
                        ->setCreatedBy($user);
                    $entityManager->persist($attendance);
                } else {
                    $attendance = $attendances[$youthId];
                    $attendance->setLastModifiedAt(new \DateTime());
                }
                
                $attendance->setIsPresent(!empty($data['isPresent']))
                    ->setComment($data['comment'] ?? null);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Les présences ont été enregistrées avec succès.');
            
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/attendance.html.twig', [
            'event' => $event,
            'churches' => $churches,
            'attendances' => $attendances,
        ]);
    }

    #[Route('/{id}/export', name: 'app_event_export', methods: ['GET'])]
    #[IsGranted('view', 'event')]
    public function export(Event $event, ChurchRepository $churchRepository): Response
    {
        // Récupérer les données nécessaires
        $churches = $churchRepository->findBy(['sector' => $event->getSector()]);
        
        // Préparer les présences
        $attendances = [];
        foreach ($event->getAttendances() as $attendance) {
            $attendances[$attendance->getYouth()->getId()] = $attendance;
        }

        // Configurer Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        // Générer le HTML
        $html = $this->renderView('event/pdf/attendance_report.html.twig', [
            'event' => $event,
            'churches' => $churches,
            'attendances' => $attendances
        ]);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Configurer le format du papier
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Générer le nom du fichier
        $filename = sprintf('presence_%s_%s.pdf',
            $event->getName(),
            $event->getDate()->format('Y-m-d')
        );

        // Envoyer le PDF au navigateur
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ]
        );
    }
}
