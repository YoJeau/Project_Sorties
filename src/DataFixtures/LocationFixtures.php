<?php
    // src/DataFixtures/LocationFixtures.php
    namespace App\DataFixtures;

    use App\Entity\Location;
    use App\Entity\City;
    use Doctrine\Bundle\FixturesBundle\Fixture;
    use Doctrine\Persistence\ObjectManager;
    use Doctrine\Common\DataFixtures\DependentFixtureInterface;

    class LocationFixtures extends Fixture implements DependentFixtureInterface
    {
        public function load(ObjectManager $manager): void
        {
            // Récupération des villes existantes pour associer aux lieux
            $paris = $manager->getRepository(City::class)->findOneBy(['citName' => 'Paris']);
            $lyon = $manager->getRepository(City::class)->findOneBy(['citName' => 'Lyon']);
            $marseille = $manager->getRepository(City::class)->findOneBy(['citName' => 'Marseille']);

            // Lieux à insérer
            $locationsData = [
                ['name' => 'Tour Eiffel', 'street' => 'Champ de Mars', 'latitude' => 48.8584, 'longitude' => 2.2945, 'city' => $paris],
                ['name' => 'Musee du Louvre', 'street' => 'Rue de Rivoli', 'latitude' => 48.8606, 'longitude' => 2.3376, 'city' => $paris],
                ['name' => 'Basilique de Fourviere', 'street' => '8 Place de Fourvière', 'latitude' => 45.7624, 'longitude' => 4.8224, 'city' => $lyon],
                ['name' => 'Vieux Port', 'street' => 'Quai des Belges', 'latitude' => 43.2965, 'longitude' => 5.3698, 'city' => $marseille],
            ];

            // Boucle pour créer chaque lieu
            foreach ($locationsData as $key => $data) {
                $location = new Location();
                $location->setLocName($data['name']);
                $location->setLocStreet($data['street']);
                $location->setLocLatitude($data['latitude']);
                $location->setLocLongitude($data['longitude']);
                $location->setLocCity($data['city']); // Association de la ville au lieu

                // Ajoutez la référence ici
                $this->addReference('location_' . strtolower(str_replace(' ', '_', $data['name'])), $location);

                // Persister l'entité Location
                $manager->persist($location);
            }

            // Sauvegarder les lieux dans la base de données
            $manager->flush();
        }

        // Dépendance pour garantir que les villes soient créées avant les lieux
        public function getDependencies()
        {
            return [
                CityFixtures::class,
            ];
        }
    }
