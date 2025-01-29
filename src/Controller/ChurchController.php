<?php

namespace App\Controller;

use App\Entity\Church;
use App\Form\ChurchType;
use App\Repository\ChurchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/churches')]
#[IsGranted('ROLE_ADMIN')]
class ChurchController extends AbstractController
{
    #[Route('/', name: 'app_church_index', methods: ['GET'])]
    public function index(ChurchRepository $churchRepository): Response
    {
        $user = $this->getUser();
        $sectorId = in_array('ROLE_ADMIN', $user->getRoles()) 
            ? null 
            : $user->getSector()->getId();

        return $this->render('church/index.html.twig', [
            'churches' => $churchRepository->findBySectorWithYouthCount($sectorId),
        ]);
    }

    #[Route('/new', name: 'app_church_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $church = new Church();
        $form = $this->createForm(ChurchType::class, $church);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($church);
            $entityManager->flush();

            $this->addFlash('success', 'L\'église a été ajoutée avec succès.');
            return $this->redirectToRoute('app_church_index');
        }

        return $this->render('church/new.html.twig', [
            'church' => $church,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_church_show', methods: ['GET'])]
    public function show(Church $church): Response
    {
        return $this->render('church/show.html.twig', [
            'church' => $church,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_church_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Church $church, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChurchType::class, $church);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'église a été modifiée avec succès.');
            return $this->redirectToRoute('app_church_index');
        }

        return $this->render('church/edit.html.twig', [
            'church' => $church,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_church_delete', methods: ['POST'])]
    public function delete(Request $request, Church $church, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$church->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($church);
                $entityManager->flush();
                $this->addFlash('success', 'L\'église a été supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de supprimer cette église car elle est liée à des jeunes.');
            }
        }

        return $this->redirectToRoute('app_church_index');
    }

    #[Route('/{id}/youths', name: 'app_church_youths', methods: ['GET'])]
    public function youths(Church $church): Response
    {
        return $this->render('church/youths.html.twig', [
            'church' => $church,
        ]);
    }
}
