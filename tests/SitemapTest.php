<?php

    namespace Lapshev\Sitemap\Tests;

    use Lapshev\Sitemap;
    use Lapshev\Sitemap\Providers;
    use PHPUnit\Framework\TestCase;

    final class SitemapTest extends TestCase
    {
        const OUTPUT_FILE = 'example';
        const OUTPUT_PATH = __DIR__ . '/resources/';
        const TEST_URL = 'http://example.com';

        /** @var \SimpleXMLElement */
        private $xmlIndex;

        /** @var \SimpleXMLElement */
        private $xmlSample;

        private $elements = [];

        protected function setUp() {
            if(!is_dir(self::OUTPUT_PATH))  {
                mkdir(self::OUTPUT_PATH);
            }

            $sitemap = new Sitemap\Generator(self::TEST_URL);

            $this->elements = [
                ['/', new \DateTime(), 'daily', 0.5], ['/news/', new \DateTime(), 'monthly', 1],
            ];

            $simpleProvider = new Providers\Simple($this->elements);

            $sitemap->addProvider(self::OUTPUT_FILE, $simpleProvider);
            $sitemap->generate(self::OUTPUT_PATH);

            $this->xmlIndex = new \SimpleXMLElement(file_get_contents(self::OUTPUT_PATH . 'sitemap.xml'));
            $this->xmlSample = new \SimpleXMLElement(file_get_contents(self::OUTPUT_PATH . self::OUTPUT_FILE . '.xml'));
        }

        public function testIndexXml() {
            $this->assertEquals(
                1,
                $this->xmlIndex->count(), 'index xml file has wrong children elements count'
            );
        }

        public function testSampleXml() {
            $this->assertEquals(
                count($this->elements), $this->xmlSample->count(),
                'sample file has wrong elements count'
            );

            foreach($this->elements as $i => $element) {
                $this->assertEquals(
                    self::TEST_URL . $element['0'],
                    (string)$this->xmlSample->{'url'}[$i]->loc,
                    'wrong loc'
                );
                $this->assertEquals(
                    $element[1]->format(\DateTime::W3C),
                    (string)$this->xmlSample->{'url'}[$i]->lastmod,
                    'wrong lastmod'
                );
                $this->assertEquals($element[2], (string)$this->xmlSample->{'url'}[$i]->changefreq, 'wrong change-freq');
                $this->assertEquals($element[3], (string)$this->xmlSample->{'url'}[$i]->priority, 'wrong priority');
            }
        }
    }