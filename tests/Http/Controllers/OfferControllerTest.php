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
        $this->getJson(route('offer.index'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'created_at', 'updated_at', 'title', 'description', 'location', 'price', 'owner']]
            ])
            ->assertJson(['data' => $offersExpected->toArray()]);
    }

    /**
     * @test
     */
    public function index_paginationWorks()
    {
        // Arrange
        $offersExpected = factory(Offer::class, 16)->create();
        $queryParameters = 'page=2';

        // Act
        $response = $this->get('/api/offer' . '?' . $queryParameters); // this is a custom function. you can use $this->get(...)

        // Assert
        $this->assertEquals('200', $response->getStatusCode());

        // convert JSON response string to Array
        $responseArray = json_decode($response->getContent());

        // assert the second page returned the "x" additional data
        $this->assertCount(1, $responseArray->data);
    }
}
