<?php

namespace Tests\Feature;

use App\Offer;
use App\OfferReport;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfferReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = factory(User::class)->create([
            'is_admin' => true,
        ]);
        $this->user = factory(User::class)->create([
            'is_admin' => false,
        ]);
    }

    /**
     * @test
     */
    public function index_returnAllOfferReports()
    {
        // Arrange
        $offerReportsExpected = factory(OfferReport::class, 2)->create();

        // Act
        $response = $this->actingAs($this->admin, 'api')->getJson(route('offer-reports.index'));

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'created_at', 'updated_at', 'offer_id', 'description', 'reporter_id']]
            ])
            ->assertJson(['data' => $offerReportsExpected->toArray()]);
    }

    /**
     * @test
     */
    public function index_paginationWorks()
    {
        // Arrange
        factory(OfferReport::class, 16)->create();
        $queryParameters = 'page=2';

        // Act
        $response = $this->actingAs($this->admin, 'api')->getJson(route('offer-reports.index') . '?' . $queryParameters);
        $responseArray = json_decode($response->getContent());

        // Assert
        $response->assertOk();
        $this->assertCount(1, $responseArray->data);
    }

    /**
     * @test
     */
    public function store_offerReportIsStored()
    {
        // Arrange
        $DESCRIPTION = 'description';
        $offer = factory(Offer::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->postJson(route('offer-reports.store', compact('offer')), [
            'description' => $DESCRIPTION,
        ]);

        // Assert
        $response->assertCreated()
            ->assertJson([
                'description' => $DESCRIPTION,
                'offer_id' => $offer->id,
                'reporter_id' => $this->user->id
            ]);
    }

    /**
     * @test
     */
    public function store_errorWhenNoDescription()
    {
        // Arrange
        $offer = factory(Offer::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->postJson(route('offer-reports.store', compact('offer')));

        // Assert
        $response->assertStatus(422);
    }

    /**
     * @test
     */
    public function show_offerReportIsShown()
    {
        // Arrange
        $offer_report = factory(OfferReport::class)->create();

        // Act
        $response = $this->actingAs($this->admin, 'api')->getJson(route('offer-reports.show', compact('offer_report')));

        // Assert
        $response->assertOk()
            ->assertJson($offer_report->toArray());
    }

    /**
     * @test
     */
    public function show_unauthorizedWhenNormalUser()
    {
        // Arrange
        $offer_report = factory(OfferReport::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->getJson(route('offer-reports.show', compact('offer_report')));

        // Assert
        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function destroy_offerReportIsDestroyed()
    {
        // Arrange
        $offer_report = factory(OfferReport::class)->create();

        // Act
        $response = $this->actingAs($this->admin, 'api')->deleteJson(route('offer-reports.delete', compact('offer_report')));

        // Assert
        $response->assertNoContent();
    }

    /**
     * @test
     */
    public function destroy_unauthorizedWhenNormalUser()
    {
        // Arrange
        $offer_report = factory(OfferReport::class)->create();

        // Act
        $response = $this->actingAs($this->user, 'api')->deleteJson(route('offer-reports.delete', compact('offer_report')));

        // Assert
        $response->assertUnauthorized();
    }
}
