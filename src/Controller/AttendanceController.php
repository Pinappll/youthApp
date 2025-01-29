<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AttendanceController extends AbstractController
{
    #[Route('/attendance', name: 'app_attendance')]
    public function index(): Response
    {
        return $this->render('attendance/index.html.twig', [
            'controller_name' => 'AttendanceController',
        ]);
    }
}
