<?php

namespace App\Tests\Service;

use App\Entity\Participant;
use App\Entity\State;
use App\Entity\Subscribe;
use App\Entity\Trip;
use App\Repository\SubscribeRepository;
use App\Service\SubscribeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SubscribeServiceTest extends TestCase
{
    private SubscribeService $subscribeService;
    private SubscribeRepository $subscribeRepository;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->subscribeRepository = $this->createMock(SubscribeRepository::class);

        $this->subscribeService = new SubscribeService(
            $entityManager,
            $this->subscribeRepository
        );
    }

    public function testCheckStateWithOpenState(): void {
        // Créer un mock pour l'entité State
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_OPEN);

        // Créer un mock pour l'entité Trip
        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        // Appeler la méthode checkState et vérifier qu'elle retourne true
        $this->assertTrue($this->subscribeService->checkState($trip));
    }

    public function testCheckStateWithClosedState(): void {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED);

        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        $this->assertTrue($this->subscribeService->checkState($trip));
    }

    public function testCheckStateWithInvalidState(): void {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn('invalid_state');

        $trip = $this->createMock(Trip::class);
        $trip->method('getTriState')->willReturn($state);

        // Appeler la méthode checkState et vérifier qu'elle retourne false pour un état non valide
        $this->assertFalse($this->subscribeService->checkState($trip));
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Created.
     *
     * The method returns false if unsubscription is not possible.
     * @return void
     */
    public function testCheckUnsubscribeWithCreatedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CREATED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Open.
     *
     * The method returns true if unsubscription is possible.
     * @return void
     */
    public function testCheckUnsubscribeWithOpenTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_OPEN);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Closed.
     *
     * The method returns true if unsubscription is possible.
     * @return void
     */
    public function testCheckUnsubscribeWithClosedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is ClosedSubscribe.
     *
     * The method returns true if unsubscription is possible.
     * @return void
     */
    public function testCheckUnsubscribeWithClosedSubscribeTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CLOSED_SUBSCRIBE);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertTrue($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is InProgress.
     *
     * The method returns false if unsubscription is not possible.
     * @return void
     */
    public function testCheckUnsubscribeWithInProgressTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_IN_PROGRESS);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Completed.
     *
     * The method returns false if unsubscription is not possible.
     * @return void
     */
    public function testCheckUnsubscribeWithCompletedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_COMPLETED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Archived.
     *
     * The method returns false if unsubscription is not possible.
     * @return void
     */
    public function testCheckUnsubscribeWithArchivedTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_ARCHIVED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    /**
     * Testing the checkUnsubscribe method with a trip whose status is Cancelled.
     *
     * The method returns false if unsubscription is not possible.
     * @return void
     */
    public function testCheckUnsubscribeWithCancelledTrip(): void
    {
        $state = $this->createMock(State::class);
        $state->method('getStaLabel')->willReturn(State::STATE_CANCELLED);

        $result = $this->CheckUnsubscribeRequirements($state);

        $this->assertFalse($result);
    }

    /**
     * Creates the mocks needed to run the checkSubscribe method.
     *
     * @param State $state The state of the trip to be tested.
     * @return bool
     */
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