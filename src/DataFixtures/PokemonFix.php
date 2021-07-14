<?php

namespace App\DataFixtures;

use App\Entity\Pokemon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PokemonFix extends Fixture
{
    public function load(ObjectManager $manager)
    {


        for ($i = 0; $i < 50; $i++) {

            $random = random_int(1, 3);

            if ($random == 1) {
                $type =  "fire";
            } elseif ($random == 2) {
                $type =  "electric";
            } else {
                $type =  "water";
            }

            $randPokemonArray = ["Neo", "Morpheus", "Trinity", "Cypher", "Tank", "pika", "turtle", "flamelizard", "egg", "planThing"];
            $rand_keys = array_rand($randPokemonArray, 2);
            $pokemon = new Pokemon();
            $pokemon->setName($randPokemonArray[$rand_keys[0]]);
            $pokemon->setType($type);

            $manager->persist($pokemon);
        }
        $manager->flush();
    }
}
