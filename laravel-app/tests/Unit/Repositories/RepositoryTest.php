<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\WishList;
use App\Models\Wish;
use App\Models\Reservation;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Repository functionality tests
 */
class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that repositories are properly bound to interfaces
     */
    public function test_repositories_are_bound_to_interfaces(): void
    {
        $userRepo = app(UserRepositoryInterface::class);
        $wishListRepo = app(WishListRepositoryInterface::class);
        $wishRepo = app(WishRepositoryInterface::class);
        $reservationRepo = app(ReservationRepositoryInterface::class);

        $this->assertInstanceOf(\App\Repositories\UserRepository::class, $userRepo);
        $this->assertInstanceOf(\App\Repositories\WishListRepository::class, $wishListRepo);
        $this->assertInstanceOf(\App\Repositories\WishRepository::class, $wishRepo);
        $this->assertInstanceOf(\App\Repositories\ReservationRepository::class, $reservationRepo);
    }

    /**
     * Test user repository functionality
     */
    public function test_user_repository_functionality(): void
    {
        $userRepo = app(UserRepositoryInterface::class);

        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Test findById
        $foundUser = $userRepo->findById($user->id);
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);

        // Test findByEmail
        $foundByEmail = $userRepo->findByEmail('test@example.com');
        $this->assertNotNull($foundByEmail);
        $this->assertEquals($user->id, $foundByEmail->id);

        // Test findByName
        $foundByName = $userRepo->findByName('Test');
        $this->assertCount(1, $foundByName);
        $this->assertEquals($user->id, $foundByName->first()->id);

        // Test getStatistics (skip friends-related methods for now)
        $stats = $userRepo->getStatistics($user);
        $this->assertInstanceOf(\App\DTOs\UserStatisticsDTO::class, $stats);
        $this->assertEquals(0, $stats->totalWishLists);
    }

    /**
     * Test wish list repository functionality
     */
    public function test_wish_list_repository_functionality(): void
    {
        $wishListRepo = app(WishListRepositoryInterface::class);

        // Create test user and wish list
        $user = User::factory()->create();
        $wishList = WishList::create([
            'user_id' => $user->id,
            'title' => 'Test Wish List',
            'description' => 'Test Description',
            'is_public' => true,
            'currency' => 'BYN'
        ]);

        // Test findByUser
        $foundWishLists = $wishListRepo->findByUser($user);
        $this->assertCount(1, $foundWishLists);
        $this->assertEquals($wishList->id, $foundWishLists->first()->id);

        // Test findByUserId
        $foundByUserId = $wishListRepo->findByUserId($user->id);
        $this->assertCount(1, $foundByUserId);

        // Test getStatistics
        $stats = $wishListRepo->getStatistics($user);
        $this->assertInstanceOf(\App\DTOs\WishListStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalWishLists);
    }

    /**
     * Test wish repository functionality
     */
    public function test_wish_repository_functionality(): void
    {
        $wishRepo = app(WishRepositoryInterface::class);

        // Create test data
        $user = User::factory()->create();
        $wishList = WishList::create([
            'user_id' => $user->id,
            'title' => 'Test Wish List',
            'description' => 'Test Description',
            'is_public' => true,
            'currency' => 'BYN'
        ]);
        $wish = Wish::create([
            'wish_list_id' => $wishList->id,
            'title' => 'Test Wish',
            'description' => 'Test Wish Description',
            'price' => 100.00
        ]);

        // Test findByWishList
        $foundWishes = $wishRepo->findByWishList($wishList);
        $this->assertCount(1, $foundWishes);
        $this->assertEquals($wish->id, $foundWishes->first()->id);

        // Test findByWishListId
        $foundByWishListId = $wishRepo->findByWishListId($wishList->id);
        $this->assertCount(1, $foundByWishListId);

        // Test getStatistics
        $stats = $wishRepo->getStatistics($wishList);
        $this->assertInstanceOf(\App\DTOs\WishStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalWishes);
        $this->assertEquals(100.00, $stats->totalPrice);
    }

    /**
     * Test reservation repository functionality
     */
    public function test_reservation_repository_functionality(): void
    {
        $reservationRepo = app(ReservationRepositoryInterface::class);

        // Create test data
        $user = User::factory()->create();
        $wishList = WishList::create([
            'user_id' => $user->id,
            'title' => 'Test Wish List',
            'description' => 'Test Description',
            'is_public' => true,
            'currency' => 'BYN'
        ]);
        $wish = Wish::create([
            'wish_list_id' => $wishList->id,
            'title' => 'Test Wish',
            'description' => 'Test Wish Description',
            'price' => 100.00,
            'is_reserved' => true
        ]);
        $reservation = Reservation::create([
            'wish_id' => $wish->id,
            'user_id' => $user->id
        ]);

        // Test findByWishAndUser
        $foundReservation = $reservationRepo->findByWishAndUser($wish, $user);
        $this->assertNotNull($foundReservation);
        $this->assertEquals($reservation->id, $foundReservation->id);

        // Test findByUser
        $foundByUser = $reservationRepo->findByUser($user);
        $this->assertCount(1, $foundByUser);

        // Test findByWishList
        $foundByWishList = $reservationRepo->findByWishList($wishList);
        $this->assertCount(1, $foundByWishList);

        // Test getStatistics
        $stats = $reservationRepo->getStatistics($user);
        $this->assertInstanceOf(\App\DTOs\ReservationStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalReservations);
    }
}
