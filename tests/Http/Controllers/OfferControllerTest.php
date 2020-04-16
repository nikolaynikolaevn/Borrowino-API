<?php

namespace Tests\Http\Controllers;

use App\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\TestCase;

class OfferControllerTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;
}
