<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\ReservationStatisticsDTO;
use App\DTOs\UserStatisticsDTO;
use App\DTOs\WishListStatisticsDTO;
use App\DTOs\WishStatisticsDTO;
use App\Models\User;
use App\Models\WishList;
use App\Models\Wish;
use App\Models\Reservation;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WishListRepositoryInterface;
use App\Repositories\Contracts\WishRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;
use App\Repositories\WishListRepository;
use App\Repositories\WishRepository;
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

        $this->assertInstanceOf(UserRepository::class, $userRepo);
        $this->assertInstanceOf(WishListRepository::class, $wishListRepo);
        $this->assertInstanceOf(WishRepository::class, $wishRepo);
        $this->assertInstanceOf(ReservationRepository::class, $reservationRepo);
    }

    /**
     * Test user repository functionality
     */
    public function test_user_repository_functionality(): void
    {
        $userRepo = app(UserRepositoryInterface::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $foundUser = $userRepo->findById($user->id);
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);

        $foundByEmail = $userRepo->findByEmail('test@example.com');
        $this->assertNotNull($foundByEmail);
        $this->assertEquals($user->id, $foundByEmail->id);

        $foundByName = $userRepo->findByName('Test');
        $this->assertCount(1, $foundByName);
        $this->assertNotNull($foundByName->first());
        $this->assertEquals($user->id, $foundByName->first()->id);

        $stats = $userRepo->getStatistics($user);
        $this->assertInstanceOf(UserStatisticsDTO::class, $stats);
        $this->assertEquals(0, $stats->totalWishLists);
    }

    /**
     * Test wish list repository functionality
     */
    public function test_wish_list_repository_functionality(): void
    {
        $wishListRepo = app(WishListRepositoryInterface::class);

        $user = User::factory()->create();
        $wishList = WishList::create([
            'user_id' => $user->id,
            'title' => 'Test Wish List',
            'description' => 'Test Description',
            'is_public' => true,
            'currency' => 'BYN'
        ]);

        $foundWishLists = $wishListRepo->findByUser($user);
        $this->assertCount(1, $foundWishLists);
        $this->assertNotNull($foundWishLists->first());
        $this->assertEquals($wishList->id, $foundWishLists->first()->id);

        $foundByUserId = $wishListRepo->findByUserId($user->id);
        $this->assertCount(1, $foundByUserId);

        $stats = $wishListRepo->getStatistics($user);
        $this->assertInstanceOf(WishListStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalWishLists);
    }

    /**
     * Test wish repository functionality
     */
    public function test_wish_repository_functionality(): void
    {
        $wishRepo = app(WishRepositoryInterface::class);

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

        $foundWishes = $wishRepo->findByWishList($wishList);
        $this->assertCount(1, $foundWishes);
        $this->assertNotNull($foundWishes->first());
        $this->assertEquals($wish->id, $foundWishes->first()->id);

        $foundByWishListId = $wishRepo->findByWishListId($wishList->id);
        $this->assertCount(1, $foundByWishListId);

        $stats = $wishRepo->getStatistics($wishList);
        $this->assertInstanceOf(WishStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalWishes);
        $this->assertEquals(100.00, $stats->totalPrice);
    }

    /**
     * Test reservation repository functionality
     */
    public function test_reservation_repository_functionality(): void
    {
        $reservationRepo = app(ReservationRepositoryInterface::class);

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

        $foundReservation = $reservationRepo->findByWishAndUser($wish, $user);
        $this->assertNotNull($foundReservation);
        $this->assertEquals($reservation->id, $foundReservation->id);

        $foundByUser = $reservationRepo->findByUser($user);
        $this->assertCount(1, $foundByUser);

        $foundByWishList = $reservationRepo->findByWishList($wishList);
        $this->assertCount(1, $foundByWishList);

        $stats = $reservationRepo->getStatistics($user);
        $this->assertInstanceOf(ReservationStatisticsDTO::class, $stats);
        $this->assertEquals(1, $stats->totalReservations);
    }
}
