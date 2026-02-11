<?php

namespace App\Controller;

use App\Enum\SubjectCategory;
use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subjects')]
final class SubjectController extends AbstractController
{
    public function __construct(
        private readonly SubjectRepository $subjectRepository,
    ) {
    }

    #[Route('', name: 'app_subject_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $categoryFilter = $request->query->get('category');
        $category = $categoryFilter ? SubjectCategory::tryFrom($categoryFilter) : null;

        if (null !== $category) {
            $subjects = $this->subjectRepository->findByCategory($category);
        } else {
            $subjects = $this->subjectRepository->findAllOrderedByImportance();
        }

        return $this->render('subject/index.html.twig', [
            'subjects' => $subjects,
            'categories' => SubjectCategory::cases(),
            'current_category' => $category,
        ]);
    }

    #[Route('/{slug}', name: 'app_subject_show', methods: ['GET'])]
    public function show(string $slug): Response
    {
        $subject = $this->subjectRepository->findBySlug($slug);

        if (null === $subject) {
            throw $this->createNotFoundException(\sprintf('Subject "%s" not found.', $slug));
        }

        return $this->render('subject/show.html.twig', [
            'subject' => $subject,
        ]);
    }
}
