<?php
use Laravel\Lumen\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExamplePassportTest extends \PassportTestCase
{
    use DatabaseTransactions;

    protected $scopes = [''];

    public function testRestrictedRoute()
    {
        $this->get('/api/user')
            ->assertResponseOk();
    }

    public function testUnrestrictedRoute()
    {
        $this->get('/api/restricted')
            ->assertResponseStatus(404);
    }
}