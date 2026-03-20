<?php

namespace App\Controller;

use App\Dto\SeminarCreateDto;
use App\Dto\SessionInputDto;
use App\Entity\User;
use App\Form\SeminarType;
use App\Service\SeminarCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/seminars')]
class SeminarController extends AbstractController
{
    #[Route('/new', name: 'seminar_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SeminarCreator $seminarCreator): Response
    {
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

            return $this->redirectToRoute('seminar_show', ['id' => $seminar->getId()]);
        }

        return $this->render('seminar/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'seminar_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        return new Response(sprintf('Seminar details for seminar with ID: %d', $id));
    }
}