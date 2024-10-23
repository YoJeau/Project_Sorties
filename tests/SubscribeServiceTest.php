<?php

namespace App\Tests;

use App\Entity\State;
use App\Entity\Trip;
use App\Repository\SubscribeRepository;
use App\Repository\TripRepository;
use App\Service\SubscribeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SubscribeServiceTest extends TestCase
{
    private SubscribeService $subscribeService;

    protected function setUp(): void
    {
        $tripRepository = $this->createMock(TripRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $subscribeRepository = $this->createMock(SubscribeRepository::class);

        $this->subscribeService = new SubscribeService(
            $tripRepository,
            $entityManager,
            $subscribeRepository
        );
    }

        public function testCheckStateWithOpenState(): void
    {
        // Créer un mock pour l'entité State
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_OPEN);

        // Créer un mock pour l'entité Trip
        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        // Appeler la méthode checkState et vérifier qu'elle retourne true
        $this->assertTrue($this->subscribeService->checkState($trip));
    }

        public function testCheckStateWithClosedState(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED);

        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        $this->assertTrue($this->subscribeService->checkState($trip));
    }

        public function testCheckStateWithInvalidState(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn('invalid_state');

        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        // Appeler la méthode checkState et vérifier qu'elle retourne false pour un état non valide
        $this->assertFalse($this->subscribeService->checkState($trip));
    }
}
