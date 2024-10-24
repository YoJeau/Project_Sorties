<?php

namespace App\Tests\Service;

use App\Entity\Participant;
use App\Entity\State;
use App\Entity\Subscribe;
use App\Entity\Trip;
use App\Repository\SubscribeRepository;
use App\Repository\TripRepository;
use App\Service\SubscribeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SubscribeServiceTest extends TestCase
{
    private SubscribeService $subscribeService;
    private SubscribeRepository $subscribeRepository;

    protected function setUp(): void
    {
        $tripRepository = $this->createMock(TripRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->subscribeRepository = $this->createMock(SubscribeRepository::class);

        $this->subscribeService = new SubscribeService(
            $tripRepository,
            $entityManager,
            $this->subscribeRepository
        );
    }

    public function testCheckUnsubscribeWithCreatedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CREATED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    public function testCheckUnsubscribeWithOpenTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_OPEN);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    public function testCheckUnsubscribeWithClosedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    public function testCheckUnsubscribeWithClosedSubscribeTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED_SUBSCRIBE);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    public function testCheckUnsubscribeWithInProgressTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_IN_PROGRESS);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    public function testCheckUnsubscribeWithCompletedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_COMPLETED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    public function testCheckUnsubscribeWithArchivedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_ARCHIVED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    public function testCheckUnsubscribeWithCancelledTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CANCELLED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    private function CheckUnsubscribeRequirements(State $state): bool
    {
        $trip = $this->createMock(Trip::class);
        $participant = $this->createMock(Participant::class);

        $subscribe = $this->createMock(Subscribe::class);
        $subscribe->setSubTripId($trip);
        $subscribe->setSubParticipantId($participant);

        $trip->addSubscribe($subscribe);
        $trip->method('getTriState')->willReturn($state);

        $this->subscribeRepository->method('findOneBy')->willReturn($subscribe);

        return $this->subscribeService->checkUnsubscribe($trip, $participant);
    }
}