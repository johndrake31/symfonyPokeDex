<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Form\PokemonType;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface as EMI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PokemonController extends AbstractController
{
    #[Route('/pokemon', name: 'pokemon')]
    public function index(PokemonRepository $repo): Response
    {
        $pokemons = $repo->findAll();
        return $this->render('pokemon/index.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }

    #[Route('/pokemon/new', name: 'pokemon_new')]
    #[Route('/pokemon/edit/{id}', name: 'pokemon_edit')]
    public function new(Pokemon $pokemon = null, Request $req, EMI $entityManager): Response
    {

        $creationMode = false;
        if (!$pokemon) {
            $pokemon = new Pokemon();
            $creationMode = true;
        }
        $formula = $this->createForm(PokemonType::class, $pokemon);
        $formula->handleRequest($req);

        if ($formula->isSubmitted()) {

            $entityManager->persist($pokemon);
            $entityManager->flush();

            if (!$creationMode) {
                return $this->redirectToRoute('pokemon_show', [
                    'id' => $pokemon->getId(),
                ]);
            }

            return $this->redirect('/pokemon');
            // unset($pokemon);
            // unset($formula);
        }


        return $this->render(
            'pokemon/form.html.twig',
            [
                'form' => $formula->createView(),
                'creationMode' => $creationMode
            ]
        );
    }

    #[Route('/pokemon/delete/{id}', name: 'pokemon_delete')]
    public function delete(Pokemon $pokemon, EMI $em): Response
    {
        if (!$pokemon) {
            throw $this->createNotFoundException('No pokemon found');
        }
        $em->remove($pokemon);
        $em->flush();
        return $this->redirect('/pokemon');
    }



    #[Route('/pokemon/{id}', name: 'pokemon_show')]
    public function show(Pokemon $pokemon): Response
    {
        return $this->render('pokemon/show.html.twig', [
            'pokemon' => $pokemon,
        ]);
    }
}
