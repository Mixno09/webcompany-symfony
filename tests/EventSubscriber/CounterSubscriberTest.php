<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CounterSubscriberTest extends WebTestCase
{
    public function test_counter_should_increase_value_by_one_current_page_and_total(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $totalCount = $crawler->filter('.postcontent h2 b')->text();
        $currentCount = $crawler->filter('.postbottom h3 b')->text();

        $this->assertSame('1', $totalCount);
        $this->assertSame('1', $currentCount);

        $crawler = $client->request('GET', '/user');

        $totalCount = $crawler->filter('.postcontent h2 b')->text();
        $currentCount = $crawler->filter('.postbottom h3 b')->text();

        $this->assertSame('2', $totalCount);
        $this->assertSame('1', $currentCount);
    }
}
