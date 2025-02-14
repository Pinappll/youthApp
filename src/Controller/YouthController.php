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
use App\Entity\Church;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Attendance;

#[Route('/youths')]
class YouthController extends AbstractController
{
    #[Route('/', name: 'app_youth_index', methods: ['GET'])]
    public function index(
        YouthRepository $youthRepository, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $search = $request->query->get('search', '');
        $filterChurch = $request->query->all('filter_church');
        $filterSector = $request->query->all('filter_sector');
        $ageGroup = $request->query->get('age_group');

        // Sauvegarder la page courante dans la session
        $request->getSession()->set('youth_list_page', $page);

        // Get all churches and sectors for the filters
        $churches = $entityManager->getRepository(Church::class)->findAll();
        $sectors = $entityManager->getRepository(Church::class)->findDistinctSectors();

        // Build criteria array
        $criteria = [];
        if (!empty($search)) {
            $criteria['firstName'] = $search;
        }
        if (!empty($filterChurch)) {
            $criteria['church'] = $filterChurch;
        }
        if (!empty($filterSector)) {
            $criteria['sector'] = $filterSector;
        }
        if (!empty($ageGroup)) {
            $criteria['ageGroup'] = $ageGroup;
        }

        // Get filtered youths with pagination
        $youths = $youthRepository->findByCriteria(
            $criteria, 
            null, 
            $limit, 
            ($page - 1) * $limit
        );

        // Get total count for pagination
        $totalYouths = $youthRepository->countByCriteria($criteria);

        return $this->render('youth/index.html.twig', [
            'youths' => $youths,
            'churches' => $churches,
            'sectors' => $sectors,
            'currentPage' => $page,
            'totalPages' => ceil($totalYouths / $limit),
            'currentAgeGroup' => $ageGroup,
            'currentChurches' => $filterChurch,
            'currentSectors' => $filterSector,
        ]);
    }

    #[Route('/new', name: 'app_youth_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $youth = new Youth();
        $form = $this->createForm(YouthType::class, $youth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                // Explicitly check MIME type
                $mimeType = $photoFile->getMimeType();
                if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                    $this->addFlash('error', 'Le fichier doit être une image JPG ou PNG');
                    return $this->redirectToRoute('app_youth_new');
                }

                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('youth_photos_directory'),
                        $newFilename
                    );
                    $youth->setPhoto('uploads/youth/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo');
                }
            }

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
    public function show(
        Youth $youth, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('view', $youth);
        
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $presence = $request->query->get('presence');
        $month = $request->query->get('month');

        $criteria = [
            'youth' => $youth->getId(),
            'presence' => $presence,
            'month' => $month,
        ];

        $attendances = $entityManager
            ->getRepository(Attendance::class)
            ->findAttendancesByCriteria(
                $criteria,
                $limit,
                ($page - 1) * $limit
            );

        $totalAttendances = $entityManager
            ->getRepository(Attendance::class)
            ->countAttendancesByCriteria($criteria);

        return $this->render('youth/show.html.twig', [
            'youth' => $youth,
            'attendances' => $attendances,
            'currentPage' => $page,
            'totalPages' => ceil($totalAttendances / $limit),
            'presence' => $presence,
            'month' => $month,
            'returnPage' => $request->getSession()->get('youth_list_page', 1),
            'search' => $request->query->get('search'),
            'filter_church' => $request->query->all('filter_church'),
            'filter_sector' => $request->query->all('filter_sector'),
            'age_group' => $request->query->get('age_group')
        ]);
    }

    #[Route('/{id}/edit', name: 'app_youth_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Youth $youth, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('edit', $youth);
        $form = $this->createForm(YouthType::class, $youth);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                // Explicitly check MIME type
                $mimeType = $photoFile->getMimeType();
                if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                    $this->addFlash('error', 'Le fichier doit être une image JPG ou PNG');
                    return $this->redirectToRoute('app_youth_edit', ['id' => $youth->getId()]);
                }

                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('youth_photos_directory'),
                        $newFilename
                    );
                    
                    // Delete old file if exists
                    if ($youth->getPhoto()) {
                        $oldFilePath = $this->getParameter('kernel.project_dir').'/public/'.$youth->getPhoto();
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $youth->setPhoto('uploads/youth/' . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo');
                }
            }

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
    public function delete(Request $request, Youth $youth, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('delete', $youth);
        if ($this->isCsrfTokenValid('delete'.$youth->getId(), $request->request->get('_token'))) {
            $entityManager->remove($youth);
            $entityManager->flush();
            $this->addFlash('success', 'Le jeune a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_youth_index');
    }
}
