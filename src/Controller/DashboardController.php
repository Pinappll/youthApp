<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\YouthRepository;
use App\Repository\AttendanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        EventRepository $eventRepository,
        YouthRepository $youthRepository,
        AttendanceRepository $attendanceRepository
    ): Response {
        $now = new \DateTime();
        $thirtyDaysLater = (new \DateTime())->modify('+30 days');

        $statistics = [
            'totalYouths' => $youthRepository->count([]),
            'upcomingEvents' => $eventRepository->countUpcoming(),
            'averageAttendance' => $attendanceRepository->getAverageAttendanceRate(),
        ];

        return $this->render('dashboard/index.html.twig', [
            'statistics' => $statistics,
            'upcomingEvents' => $eventRepository->findUpcoming(5),
            'upcomingBirthdays' => $youthRepository->findUpcomingBirthdays($now, $thirtyDaysLater),
        ]);
    }
}