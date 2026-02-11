<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(SubjectRepository $subjectRepository): Response
    {
        $latestSubjects = $subjectRepository->findActive(10);

        return $this->render('home/index.html.twig', [
            'subjects' => $latestSubjects,
        ]);
    }
}
