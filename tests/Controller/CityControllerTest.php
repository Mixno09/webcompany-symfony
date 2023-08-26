<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

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

        $crawler = $client->request('GET', '/city/create');
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

    public function test_edit_should_edit_city(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(17);

        $entityManager->persist($city);
        $entityManager->flush();

        $cityId = $city->getId();

        $crawler = $client->request('GET', "/city/{$cityId}/edit");
        $this->assertResponseIsSuccessful();
        $this->assertFormValue('.dopsity', 'city[name]', 'Minsk');
        $this->assertFormValue('.dopsity', 'city[idx]', '17');

        $form = $crawler->filter('.dopsity')->form();
        $client->submit($form, [
            'city[name]' => 'Grodno',
            'city[idx]' => '1',
        ]);

        $this->assertResponseRedirects('/', 302);

        /** @var \App\Repository\CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $editedCity = $cityRepository->findOneBy(['id' => $cityId]);

        $this->assertNotNull($editedCity);
        $this->assertEquals('Grodno', $editedCity->getName());
        $this->assertEquals(1, $editedCity->getIdx());
    }

    public function test_delete_should_delete_city(): void
    {
        $client = self::createClient();

        $city = new City();
        $city->setName('Minsk');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($city);
        $entityManager->flush();

        $cityId = $city->getId();

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('.cpsity h3:contains("Minsk")')->nextAll()->filter('form')->eq(0)->form();
        $client->submit($form);

        $this->assertResponseRedirects('/', 302);
        /** @var \App\Repository\CityRepository $cityRepository */
        $cityRepository = self::getContainer()->get(CityRepository::class);
        $this->assertNull($cityRepository->findOneBy(['id' => $cityId]));
    }

    public function test_index_should_show_form(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('.sortform');

        $form = $crawler->filter('.form.flrig form')->eq(1)->form();
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.sortform');
    }

    /**
     * @depends test_index_should_show_form
     * @dataProvider getSortFieldAndSortDirection
     */
    public function test_index_should_sort_city(array $cities, array $formValues, array $expectedResult): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        foreach ($cities as $city) {
            $entityManager->persist($city);
        }
        $entityManager->flush();

        $crawler = $client->request('GET', '/', ['form' => '1']);
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('.sortform')->closest('form')->form();
        $crawler = $client->submit($form, $formValues);

        $this->assertResponseIsSuccessful();
        $actualResult = $crawler->filter('.cpsity h3')->each(
            static fn (Crawler $node): string => $node->text()
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getSortFieldAndSortDirection(): Generator
    {
        $cities = [];
        $city = new City();
        $city->setName('Minsk');
        $city->setIdx(10);
        $cities[] = $city;

        $city = new City();
        $city->setName('Grodno');
        $city->setIdx(7);
        $cities[] = $city;

        $city = new City();
        $city->setName('Brest');
        $city->setIdx(15);
        $cities[] = $city;

        $formValues = [
            'sort' => 'name',
            'order' => 'ASC',
        ];
        $expectedResult = ['Brest', 'Grodno', 'Minsk'];
        yield [$cities, $formValues, $expectedResult];

        $formValues = [
            'sort' => 'name',
            'order' => 'DESC',
        ];
        $expectedResult = ['Minsk', 'Grodno', 'Brest'];
        yield [$cities, $formValues, $expectedResult];

        $formValues = [
            'sort' => 'idx',
            'order' => 'ASC',
        ];
        $expectedResult = ['Grodno', 'Minsk', 'Brest'];
        yield [$cities, $formValues, $expectedResult];

        $formValues = [
            'sort' => 'idx',
            'order' => 'DESC',
        ];
        $expectedResult = ['Brest', 'Minsk', 'Grodno'];
        yield [$cities, $formValues, $expectedResult];
    }
}
