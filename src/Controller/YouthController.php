<?php

namespace App\Controller;

use App\Entity\Youth;
use App\Form\YouthType;
use App\Repository\YouthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/youths')]
class YouthController extends AbstractController
{
    #[Route('/', name: 'app_youth_index', methods: ['GET'])]
    public function index(YouthRepository $youthRepository): Response
    {
        $user = $this->getUser();
        $youths = in_array('ROLE_ADMIN', $user->getRoles())
            ? $youthRepository->findAll()
            : $youthRepository->findBySector($user->getSector());

        return $this->render('youth/index.html.twig', [
            'youths' => $youths,
        ]);
    }

    #[Route('/new', name: 'app_youth_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $youth = new Youth();
        $form = $this->createForm(YouthType::class, $youth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($youth);
            $entityManager->flush();

            $this->addFlash('success', 'Le jeune a été ajouté avec succès.');
            return $this->redirectToRoute('app_youth_index');
        }

        return $this->render('youth/new.html.twig', [
            'youth' => $youth,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_youth_show', methods: ['GET'])]
    #[IsGranted('view', 'youth')]
    public function show(Youth $youth): Response
    {
        return $this->render('youth/show.html.twig', [
            'youth' => $youth,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_youth_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'youth')]
    public function edit(Request $request, Youth $youth, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(YouthType::class, $youth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le jeune a été modifié avec succès.');
            return $this->redirectToRoute('app_youth_index');
        }

        return $this->render('youth/edit.html.twig', [
            'youth' => $youth,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_youth_delete', methods: ['POST'])]
    #[IsGranted('delete', 'youth')]
    public function delete(Request $request, Youth $youth, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$youth->getId(), $request->request->get('_token'))) {
            $entityManager->remove($youth);
            $entityManager->flush();
            $this->addFlash('success', 'Le jeune a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_youth_index');
    }
}
