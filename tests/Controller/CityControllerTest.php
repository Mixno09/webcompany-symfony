<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CityControllerTest extends WebTestCase
{
    public function test_index_should_be_blank_when_no_cities(): void
    {
        $client = self::createClient();

        /** @var \App\Repository\CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $this->assertEquals(0, $cityRepository->count([]));

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(0, '.cpsity');
    }

    public function test_create_should_create_city(): void
    {
        $client = self::createClient();

        /** @var \App\Repository\CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $this->assertNull($cityRepository->findOneBy(['name' => 'Minsk']));

        $crawler = $client->request('GET', '/create/city');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('.dopsity')->form();

        $client->submit($form, [
            'city[name]' => 'Minsk',
            'city[idx]' => '17',
        ]);

        $this->assertResponseRedirects('/', 302);
        $city = $cityRepository->findOneBy(['name' => 'Minsk']);
        $this->assertInstanceOf(City::class, $city);
        $this->assertEquals(17, $city->getIdx());
    }
}
