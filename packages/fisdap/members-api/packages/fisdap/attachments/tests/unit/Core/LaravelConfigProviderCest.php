<?php

use Fisdap\Attachments\Core\ConfigProvider\LaravelConfigProvider;
use Illuminate\Config\Repository;


class LaravelConfigProviderCest
{
    /**
     * @var Repository|Mockery\Mock
     */
    private $laravelConfigRepositoryMock;

    /**
     * @var LaravelConfigProvider
     */
    private $laravelConfigProvider;


    public function _before(UnitTester $I)
    {
        $this->laravelConfigRepositoryMock = Mockery::mock(Repository::class);
        $this->laravelConfigProvider = new LaravelConfigProvider($this->laravelConfigRepositoryMock);
    }

    public function _after(UnitTester $I)
    {
    }


    public function it_can_get_a_configuration_value(UnitTester $I)
    {
        // arrange
        $this->laravelConfigRepositoryMock->shouldReceive('get')->withArgs(['attachments.foo', null])->once()->andReturn('bar');

        // act
        $configValue = $this->laravelConfigProvider->get('foo');

        // assert
        $I->assertEquals('bar', $configValue);
    }


    public function it_can_set_a_configuration_value(UnitTester $I)
    {
        // arrange
        $this->laravelConfigRepositoryMock->shouldReceive('set')->with('attachments.foo', 'bar')->once();
        $this->laravelConfigRepositoryMock->shouldReceive('get')->withArgs(['attachments.foo', null])->once()->andReturn('bar');

        // act
        $this->laravelConfigProvider->set('foo', 'bar');

        // assert
        $I->assertEquals('bar', $this->laravelConfigProvider->get('foo'));
    }
}