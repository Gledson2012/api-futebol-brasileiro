<?php

namespace Tests\Unit;

use App\Scrapers\GenericEspnScraper;
use Tests\TestCase;

class GenericEspnScraperTest extends TestCase
{
    public function test_get_standings_with_mocked_html_xpath()
    {
        $html = '<html><body>
            <div class="Table__Title">
                <table>
                    <tbody>
                        <tr><td><span class="hide-mobile"><a href="/time1">Arsenal</a></span></td></tr>
                        <tr><td><span class="hide-mobile"><a href="/time2">Chelsea</a></span></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="Table__Scroller">
                <table>
                    <tbody>
                        <tr>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">0</span></td>
                            <td><span class="static-value">0</span></td>
                            <td><span class="static-value">3</span></td>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">2</span></td>
                            <td><span class="static-value">3</span></td>
                        </tr>
                        <tr>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">0</span></td>
                            <td><span class="static-value">0</span></td>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">1</span></td>
                            <td><span class="static-value">3</span></td>
                            <td><span class="static-value">-2</span></td>
                            <td><span class="static-value">0</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </body></html>';

        $scraper = $this->getMockBuilder(GenericEspnScraper::class)
            ->onlyMethods(['getHtml'])
            ->getMock();

        $scraper->expects($this->once())
            ->method('getHtml')
            ->willReturn($html);

        $results = $scraper->getStandings('http://mock.url');

        $this->assertCount(2, $results);
        $this->assertEquals('Arsenal', $results[0]['team_name']);
        $this->assertEquals(1, $results[0]['position']);
        $this->assertEquals(3, $results[0]['points']);
        $this->assertEquals(1, $results[0]['played']);

        $this->assertEquals('Chelsea', $results[1]['team_name']);
        $this->assertEquals(2, $results[1]['position']);
        $this->assertEquals(0, $results[1]['points']);
    }

    public function test_get_standings_fallback_when_xpath_fails()
    {
        $html = '<html><body>
            <span class="hide-mobile"><a href="/time1">Arsenal</a></span>
            <span class="static-value">1</span>
            <span class="static-value">1</span>
            <span class="static-value">0</span>
            <span class="static-value">0</span>
            <span class="static-value">3</span>
            <span class="static-value">1</span>
            <span class="static-value">2</span>
            <span class="static-value">3</span>
        </body></html>';

        $scraper = $this->getMockBuilder(GenericEspnScraper::class)
            ->onlyMethods(['getHtml'])
            ->getMock();

        $scraper->expects($this->once())
            ->method('getHtml')
            ->willReturn($html);

        $results = $scraper->getStandings('http://mock.url');

        $this->assertCount(1, $results);
        $this->assertEquals('Arsenal', $results[0]['team_name']);
        $this->assertEquals(3, $results[0]['points']);
    }
}
