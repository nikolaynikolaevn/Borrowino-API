<?php

namespace Tests\Feature;

use App\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function anonymousUser_canSee_offerIndex()
    {
        // Arrange
        factory(Offer::class, 2)->create();

        // Act
        $request = $this->getJson(route('offers.index'));

        //Assert
        $request->assertStatus(200);
    }

    /**
     * @test
     */
    public function anonymousUser_canSee_offerShow()
    {
        // Arrange
        factory(Offer::class)->create();

        // Act
        $request = $this->getJson(route('offers.index',1));

        // Assert
        $request->assertStatus(200);
    }

}
