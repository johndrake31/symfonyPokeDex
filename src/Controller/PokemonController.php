<?php

namespace App\Controller;

use App\Entity\Catagory;
use App\Entity\Pokemon;
use App\Form\CatagoryType;
use App\Form\PokemonType;
use App\Repository\CatagoryRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface as EMI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;


class PokemonController extends AbstractController
{
    /**
     * 
     * @Route("/pokemon", name="pokemon")
     */
    public function index(PokemonRepository $repo): Response
    {
        $pokemons = $repo->findAll();

        return $this->render('pokemon/index.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }


    /**
     * 
     * @Route("/pokemon/new", name="pokemon_new")
     * @Route("/pokemon/edit/{id}", name="pokemon_edit")
     */
    public function new(Pokemon $pokemon = null, Request $req, EMI $entityManager, SluggerInterface $slugger): Response
    {
        $creationMode = false;
        //test to see if mode edition or creation.
        if (!$pokemon) {
            $pokemon = new Pokemon();
            $creationMode = true;
        }

        // instantiates a new form to add or edit a pokemon.
        $formula = $this->createForm(PokemonType::class, $pokemon);
        $formula->handleRequest($req);

        if ($formula->isSubmitted()) {

            if ($formula->get('image')->getData() != null) {
                //START IMAGE SUBMISSION
                /** @var UploadedFile $imageFile */
                $imageFile = $formula->get('image')->getData();

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('pokemon_directory'),
                    $newFilename
                );
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            if ($creationMode || $formula->get('image')->getData() != null) {
                $pokemon->setImage($newFilename);
            }

            //END IMAGE SUBMISSION
            $entityManager->persist($pokemon);
            $entityManager->flush();


            if (!$creationMode) {
                return $this->redirectToRoute('pokemon_show', [
                    'id' => $pokemon->getId(),
                ]);
            }

            return $this->redirect('/pokemon');
        }

        return $this->render(
            'pokemon/form.html.twig',
            [
                'form' => $formula->createView(),
                'creationMode' => $creationMode,
                'pokemon' => $pokemon
            ]
        );
    }

    /**
     * 
     * @Route("/pokemon/catagory/new", name="catagory_new")
     * @Route("/pokemon/catagory/edit/{id}", name="catagory_edit")
     */
    public function newCatagory(Catagory $catagory = null, Request $req, EMI $em, CatagoryRepository $repo): Response
    {
        $catagories = $repo->findAll();

        $creationMode = false;
        //test to see if mode edition or creation.
        if (!$catagory) {
            $catagory = new Catagory();
            $creationMode = true;
        }

        $formula = $this->createForm(CatagoryType::class, $catagory);
        $formula->handleRequest($req);

        if ($formula->isSubmitted()) {
            $em->persist($catagory);
            $em->flush();
            return $this->redirect('/pokemon');
        }

        return $this->render(
            'pokemon/catform.html.twig',
            [
                'catagories' => $catagories,
                'catForm' => $formula->createView()
            ]
        );
    }

    /**
     * 
     * @Route("/pokemon/delete/{id}", name="pokemon_delete")
     */
    public function delete(Pokemon $pokemon, EMI $em): Response
    {
        $em->remove($pokemon);
        $em->flush();
        return $this->redirect('/pokemon');
    }

    /**
     * 
     * @Route("/pokemon/{id}", name="pokemon_show")
     * 
     */
    public function show(Pokemon $pokemon): Response
    {
        if (!$pokemon) {
            return $this->redirect('/404');
        }

        return $this->render('pokemon/show.html.twig', [
            'pokemon' => $pokemon,
        ]);
    }
}
