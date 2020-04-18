<?php

namespace Tests\Http\Controllers;

use App\Offer;
use App\User;
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

    //  For some reason this test does not work, the method works as expected in Postman

    /**
     * @test
     */
    public function show_showRequestedOffer()
    {
        // Arrange
        $offerExpected = factory(Offer::class, 1)->create();

        // Act
        $response = $this->getJson(route('offer.show', 1));
        $actual = json_decode($response->getContent(),true);

        // Assert
        $response->assertStatus(200)
            ->assertJson($offerExpected->toArray()[0]);
    }

    /**
     * @test
     */
    public function store_offerIsStored()
    {
        // Arrange
        $response = null;
        $user = factory(User::class)->create();

        // Act
        $response = $this->postJson(route('offer.store'), [
            'title' => 'title',
            'description' => 'description',
            'location' => 'location',
            'price' => 30,
            'owner' => $user->id,
        ]);

        // Assert
        $response->assertNoContent();
    }

    /**
     * @test
     */
    public function destroy_offerIsDestroyedWhenOwner()
    {
        // Arrange
        $offer = factory(Offer::class, 1)->create();
        $user = User::find(1);

        // Act
        $response = $this->actingAs($user)->deleteJson(route('offer.destroy', 1));

        // Assert
        $response->assertNoContent();
    }

    /**
     * @test
     */
    public function destroy_offerIsNotDestroyedWhenNotOwner()
    {
        // Arrange
        $offer = factory(Offer::class, 1)->create(); // A user is already created in this factory
        $user = factory(User::class)->create();

        // Act
        $response = $this->actingAs($user)->deleteJson(route('offer.destroy', 1));

        // Assert
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function update_offerIsUpdatedWhenOwner()
    {
        // Arrange
        $offer = factory(Offer::class, 1)->create(); // A user is already created in this factory
        $user = User::find(1);

        $updatedOffer = new Offer();
        $updatedOffer->title = "title";
        $updatedOffer->description = "description";
        $updatedOffer->location = "location";
        $updatedOffer->price = 3.4;

        // Act
        $response = $this->actingAs($user)->patchJson(route('offer.update', 1), ['offer' => $updatedOffer]);

        // Assert
        $response->assertNoContent();
    }


}
