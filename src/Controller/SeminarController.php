<?php

namespace App\Controller;

use App\Dto\SeminarCreateDto;
use App\Dto\SessionInputDto;
use App\Entity\Seminar;
use App\Entity\User;
use App\Form\SeminarType;
use App\Repository\SeminarRepository;
use App\Service\SeminarCreator;
use App\Service\SeminarDeleter;
use App\Security\Voter\SeminarVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/seminars')]
class SeminarController extends AbstractController
{

    #[Route('/', name: 'seminar_index', methods: ['GET'])]
    public function index(SeminarRepository $seminarRepository): Response
    {
        $seminars = $seminarRepository->findAll();
        return $this->render('seminar/index.html.twig', [
            'seminars' => $seminars,
        ]);
    }

    #[Route('/new', name: 'seminar_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        SeminarCreator $seminarCreator
    ): Response {
        $dto = new SeminarCreateDto();
        $dto->sessions[] = new SessionInputDto();

        $form = $this->createForm(SeminarType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('You must be logged in to create a seminar.');
            }

            $seminar = $seminarCreator->create($dto, $user);

            $this->addFlash('success', 'Seminar created successfully!');

            return $this->redirectToRoute('seminar_show', [
                'id' => $seminar->getId(),
            ]);
        }

        return $this->render('seminar/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'seminar_show', methods: ['GET'])]
    public function show(Seminar $seminar): Response
    {
        return $this->render('seminar/show.html.twig', [
            'seminar' => $seminar,
        ]);
    }

    #[Route('/{id}', name: 'seminar_delete', methods: ['POST'])]
    public function delete(Request $request, Seminar $seminar, SeminarDeleter $seminarDeleter): Response
    {
        // CSRF-Token validieren
        if (!$this->isCsrfTokenValid('delete_seminar_' . $seminar->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $this->denyAccessUnlessGranted(SeminarVoter::DELETE, $seminar);

        $seminarDeleter->delete($seminar);

        $this->addFlash('success', 'Seminar deleted successfully!');

        return $this->redirectToRoute('seminar_index');
    }

    private function debug(Request $request, SeminarCreateDto $dto, FormInterface $form): void
    {

        //debugging
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = [
                'message' => $error->getMessage(),
                'origin' => $error->getOrigin()?->getName(),
            ];
        }

        dd([
            'submitted' => $form->isSubmitted(),
            'valid' => $form->isValid(),
            'request_data' => $request->request->all(),
            'dto' => $dto,
            'errors' => $errors,
        ]);
        //
    }
}