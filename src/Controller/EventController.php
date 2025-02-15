<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;  // Add this use statement if not already present
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
use App\Repository\SectorRepository;  // Add this line

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, SectorRepository $sectorRepository, ChurchRepository $churchRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $criteria = [
            'search' => $request->query->get('search'),
            'month' => $request->query->get('month'),
            'sectors' => $request->query->all('sectors'),
            'churches' => $request->query->all('churches')
        ];

        $result = $eventRepository->findFiltered($criteria, $page);

        return $this->render('event/index.html.twig', [
            'events' => $result['events'],
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages'],
            'month' => $request->query->get('month'),
            'sectors' => $sectorRepository->findAll(),
            'churches' => $churchRepository->findAll(),
            'selectedSectors' => $criteria['sectors'],
            'selectedChurches' => $criteria['churches']
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $event->setSector($this->getUser()->getSector());
        
        // Handle AJAX requests for scope changes
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $formData = $request->request->all('event');
            if (isset($formData['scope'])) {
                $event->setScope($formData['scope']);
                // Clear sector if scope is general
                if ($formData['scope'] === 'general') {
                    $event->setSector(null);
                }
                $form = $this->createForm(EventType::class, $event);
                
                return $this->render('event/_form_fields.html.twig', [
                    'form' => $form->createView()
                ]);
            }
        }
        
        // Handle normal form submission
        $form = $this->createForm(EventType::class, $event);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // Set default scope if not set
                if (!$event->getScope()) {
                    $event->setScope('sector');
                }

                // Handle scope-specific logic
                if ($event->getScope() === 'general') {
                    $event->setSector(null);
                }
                if ($event->getScope() !== 'church') {
                    $event->setTargetChurch(null);
                }
                if ($event->getScope() !== 'sector') {
                    $event->setTargetSector(null);
                }

                $entityManager->persist($event);
                $entityManager->flush();

                // Create initial attendances based on scope
                $youths = $this->getTargetYouths($event, $entityManager);
                foreach ($youths as $youth) {
                    $attendance = new Attendance();
                    $attendance->setEvent($event)
                        ->setYouth($youth)
                        ->setCreatedBy($this->getUser());
                    $entityManager->persist($attendance);
                }

                // Add additional youths if any were selected
                if ($form->has('additionalYouths')) {
                    foreach ($form->get('additionalYouths')->getData() as $youth) {
                        if (!$event->getAttendances()->exists(function($key, $attendance) use ($youth) {
                            return $attendance->getYouth() === $youth;
                        })) {
                            $attendance = new Attendance();
                            $attendance->setEvent($event)
                                ->setYouth($youth)
                                ->setCreatedBy($this->getUser());
                            $entityManager->persist($attendance);
                        }
                    }
                }

                $entityManager->flush();
                $this->addFlash('success', 'L\'événement a été créé avec succès.');
                return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
            }
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event
        ]);
    }

    private function getTargetYouths(Event $event, EntityManagerInterface $entityManager): array
    {
        $qb = $entityManager->createQueryBuilder()
            ->select('y')
            ->from(Youth::class, 'y')
            ->join('y.church', 'c')
            ->orderBy('y.lastName', 'ASC')
            ->addOrderBy('y.firstName', 'ASC');

        switch ($event->getScope()) {
            case 'church':
                if ($event->getTargetChurch()) {
                    $qb->where('c = :church')
                       ->setParameter('church', $event->getTargetChurch());
                }
                break;
            case 'sector':
                if ($event->getTargetSector()) {
                    $qb->where('c.sector = :sector')
                       ->setParameter('sector', $event->getTargetSector());
                } elseif ($event->getSector()) {
                    $qb->where('c.sector = :sector')
                       ->setParameter('sector', $event->getSector());
                }
                break;
            case 'general':
                // No conditions needed - get all youths
                break;
        }

        return $qb->getQuery()->getResult();
    }

    private function getScopeYouths(Event $event, EntityManagerInterface $entityManager): array
    {
        $qb = $entityManager->createQueryBuilder()
            ->select('y')
            ->from(Youth::class, 'y')
            ->join('y.church', 'c')
            ->orderBy('y.lastName', 'ASC')
            ->addOrderBy('y.firstName', 'ASC');

        switch ($event->getScope()) {
            case 'church':
                if ($event->getTargetChurch()) {
                    $qb->where('c = :church')
                       ->setParameter('church', $event->getTargetChurch());
                }
                break;
            case 'sector':
                if ($event->getTargetSector()) {
                    $qb->where('c.sector = :sector')
                       ->setParameter('sector', $event->getTargetSector());
                } elseif ($event->getSector()) {
                    $qb->where('c.sector = :sector')
                       ->setParameter('sector', $event->getSector());
                }
                break;
            case 'general':
                // No conditions needed - get all youths
                break;
            default:
                if ($event->getSector()) {
                    $qb->where('c.sector = :sector')
                       ->setParameter('sector', $event->getSector());
                }
        }

        return $qb->getQuery()->getResult();
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
        // Ensure scope is set
        if (!$event->getScope()) {
            $event->setScope('sector');
        }
        
        $form = $this->createForm(EventType::class, $event, [
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'event_form'
        ]);
        
        // Handle AJAX requests for scope changes
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $formData = $request->request->all('event');
            if (isset($formData['scope'])) {
                $event->setScope($formData['scope']);
                return $this->render('event/_form_fields.html.twig', [
                    'form' => $this->createForm(EventType::class, $event)->createView()
                ]);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle scope-specific logic
            if ($event->getScope() === 'general') {
                $event->setSector(null);
            }
            if ($event->getScope() !== 'church') {
                $event->setTargetChurch(null);
            }
            if ($event->getScope() !== 'sector') {
                $event->setTargetSector(null);
            }
            
            $event->setLastModifiedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été modifié avec succès.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form
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
        
        // Get churches based on event scope
        $targetChurches = match($event->getScope()) {
            'church' => $event->getTargetChurch() ? [$event->getTargetChurch()] : [],
            'sector' => $event->getTargetSector() 
                ? $churchRepository->findBy(['sector' => $event->getTargetSector()])
                : $churchRepository->findBy(['sector' => $event->getSector()]),
            'general' => $churchRepository->findAll(),
            default => []
        };

        // Get all youths that could be added
        $allYouths = $entityManager->createQueryBuilder()
            ->select('y', 'c', 's')  // Include church and sector in the query
            ->from(Youth::class, 'y')
            ->join('y.church', 'c')
            ->join('c.sector', 's')
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->addOrderBy('y.lastName', 'ASC')
            ->addOrderBy('y.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        // Get target youths based on scope
        $scopeYouths = $this->getScopeYouths($event, $entityManager);
        $scopeYouthIds = array_map(fn($youth) => $youth->getId(), $scopeYouths);
        
        // Get existing attendances
        $attendances = [];
        foreach ($event->getAttendances() as $attendance) {
            $attendances[$attendance->getYouth()->getId()] = $attendance;
        }

        // Get selected youths IDs (both from scope and existing attendances)
        $selectedYouthIds = array_merge(
            $scopeYouthIds,
            array_keys($attendances)
        );

        // Get all youths that are not selected
        $additionalYouths = $entityManager->createQueryBuilder()
            ->select('y', 'c', 's')
            ->from(Youth::class, 'y')
            ->join('y.church', 'c')
            ->join('c.sector', 's')
            ->where('y.id NOT IN (:selectedIds)')
            ->setParameter('selectedIds', array_unique($selectedYouthIds))
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->addOrderBy('y.lastName', 'ASC')
            ->addOrderBy('y.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        // Group additional youths by sector and church
        $groupedYouths = [];
        foreach ($additionalYouths as $youth) {
            $sectorId = $youth->getChurch()->getSector()->getId();
            $churchId = $youth->getChurch()->getId();
            
            if (!isset($groupedYouths[$sectorId])) {
                $groupedYouths[$sectorId] = [
                    'name' => $youth->getChurch()->getSector()->getName(),
                    'churches' => []
                ];
            }
            
            if (!isset($groupedYouths[$sectorId]['churches'][$churchId])) {
                $groupedYouths[$sectorId]['churches'][$churchId] = [
                    'name' => $youth->getChurch()->getName(),
                    'youths' => []
                ];
            }
            
            $groupedYouths[$sectorId]['churches'][$churchId]['youths'][] = $youth;
        }

        // Sort sectors and churches
        ksort($groupedYouths);
        foreach ($groupedYouths as &$sector) {
            ksort($sector['churches']);
        }

        if ($request->isMethod('POST')) {
            // Get regular attendance data
            $attendanceData = $request->request->all('attendance') ?? [];
            
            // Get additional youths (if any)
            $additionalYouths = $request->request->all('additional_youths') ?? [];
            
            // Process regular attendances
            foreach ($attendanceData as $youthId => $data) {
                $this->processAttendance($event, (int)$youthId, $data, $attendances, $entityManager, $user);
            }
            
            // Process additional youths
            foreach ($additionalYouths as $youthId) {
                if (!isset($attendances[$youthId])) {
                    $youth = $entityManager->getReference(Youth::class, $youthId);
                    $attendance = new Attendance();
                    $attendance->setEvent($event)
                        ->setYouth($youth)
                        ->setCreatedBy($user)
                        ->setIsPresent(true);
                    $entityManager->persist($attendance);
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Les présences ont été enregistrées avec succès.');
            
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('event/attendance.html.twig', [
            'event' => $event,
            'churches' => $targetChurches,
            'grouped_youths' => $event->getScope() !== 'general' ? $groupedYouths : [],
            'attendances' => $attendances,
            'scope_youth_ids' => $scopeYouthIds,
        ]);
    }

    private function processAttendance(
        Event $event,
        int $youthId,
        array $data,
        array $attendances,
        EntityManagerInterface $entityManager,
        User $user
    ): void {
        $youth = $entityManager->getReference(Youth::class, $youthId);
        
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

    #[Route('/{id}/export', name: 'app_event_export', methods: ['GET'])]
    #[IsGranted('view', 'event')]
    public function export(Event $event, ChurchRepository $churchRepository): Response
    {
        // Get churches based on event scope
        $churches = match($event->getScope()) {
            'general' => $churchRepository->findAll(),
            'sector' => $event->getTargetSector() 
                ? $churchRepository->findBy(['sector' => $event->getTargetSector()])
                : ($event->getSector() 
                    ? $churchRepository->findBy(['sector' => $event->getSector()])
                    : []),
            'church' => $event->getTargetChurch() ? [$event->getTargetChurch()] : [],
            default => []
        };
        
        // Rest of the code remains the same
        // Get all attendances and calculate church statistics
        $attendances = [];
        $churchStats = [];
        
        foreach ($event->getAttendances() as $attendance) {
            $attendances[$attendance->getYouth()->getId()] = $attendance;
            
            if ($attendance->getIsPresent()) {
                $youth = $attendance->getYouth();
                $church = $youth->getChurch();
                $churchName = $church ? $church->getName() : 'Sans église';
                $churchStats[$churchName] = ($churchStats[$churchName] ?? 0) + 1;
            }
        }

        // Sort church statistics by name
        ksort($churchStats);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        // Generate HTML with church statistics
        $html = $this->renderView('event/pdf/attendance_report.html.twig', [
            'event' => $event,
            'churches' => $churches,
            'attendances' => $attendances,
            'churchStats' => $churchStats
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
