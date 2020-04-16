<?php

namespace Tests\Http\Controllers;

use App\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * @test
     */
    public function index_returnAllOffers()
    {
        // Arrange
        $offersExpected = factory(Offer::class, 2)->create();

        // Act

        // Assert
        $this->get(route('offer.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'created_at', 'updated_at', 'title', 'description', 'location', 'price', 'owner']]
            ])
            ->assertJson(['data' => $offersExpected->toArray()])
        ;
    }
}
