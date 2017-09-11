<?php

    namespace Lapshev\Sitemap;

    /**
     * Class Sitemap
     *
     * Sitemap generator class
     *
     * @package Lapshev\Sitemap
     */
    class Generator
    {
        const ENTIRE_LIMIT = 30000;
        const INDEX_FILE_NAME = 'sitemap.xml';

        const FILE_TYPE_MAIN = 'main';
        const FILE_TYPE_INDEX = 'index';

        /** @var iProvider[][] Providers list */
        private $providers = [];

        private $basePath = '';

        /* configs */
        private $serverUrl;
        private $params;

        /**
         * Generator constructor.
         *
         * @param string $serverUrl Server url with protocol and w\o trailing slash
         * @param array $params
         */
        public function __construct($serverUrl, $params = []) {
            $this->serverUrl = $serverUrl;
            $this->params = $params;
        }

        /**
         * Add sitemap provider, filename can be no unique if you want
         * to merge data from different providers in one file
         *
         * @param string $fileName Without .xml extension
         * @param iProvider $provider
         *
         * @return $this
         */
        public function addProvider($fileName, iProvider $provider) {
            $this->providers[$fileName][] = $provider;

            return $this;
        }

        /**
         * Generate and save sitemap files and index file
         *
         * @param string $path Absolute output file generating path
         */
        public function generate($path = '') {
            $this->basePath = rtrim($path, '/') . '/';

            $start = time();

            $files = [];

            foreach($this->providers as $fileName => $providers) {
                $files = array_merge($this->generateFile($fileName, $providers), $files);
            }

            $this->generateIndex($files);

            echo PHP_EOL . 'Exec time: ' . (time() - $start) . ' seconds' . PHP_EOL;
            echo 'Peak memory: ' . $this->formatBytes(memory_get_peak_usage()) . PHP_EOL;
        }

        /**
         * Generate sitemap file\files, save it and return files list
         * for index file
         *
         * @param string $fileName
         * @param iProvider[] $providers
         *
         * @return array Files list
         */
        private function generateFile($fileName, $providers) {
            echo 'Processing file: ' . $fileName . PHP_EOL;

            $files = [];

            $count = 0;
            $page = 0;

            $siteMap = $this->getXmlObject();

            foreach($providers as $provider) {
                echo "\t" . 'provider: ' . get_class($provider) . PHP_EOL;

                $provider->prepareData();

                while($entire = $provider->getNextEntire()) {

                    $entire->setLocServer($this->serverUrl);

                    if($count === self::ENTIRE_LIMIT) {
                        $saveFileName = $fileName . ($page ? $page : '');
                        $siteMap->saveXML($this->basePath . $saveFileName . '.xml');
                        $files[] = $saveFileName . '.xml';

                        $page++;
                        $count = 0;

                        $siteMap = $this->getXmlObject();
                    }

                    if(!empty($this->params['checkResponse'])) {
                        try {
                            $entire->test();
                        } catch(EntireTestException $e) {
                            echo "\t" . "\t" . '[' . $e->getMessage() . '] ' . $entire->getLoc() . PHP_EOL;
                            continue;
                        }
                    }

                    $entire->add($siteMap);

                    $count++;
                }

                echo "\t" . "\t" . 'entries done: ' . (($page) * self::ENTIRE_LIMIT + $count) . PHP_EOL;
                echo "\t" . "\t" . 'memory used: ' . $this->formatBytes(memory_get_usage()) . PHP_EOL;

                unset($provider);
            }

            $saveFileName = $fileName . ($page ? $page : '');
            $siteMap->saveXML($this->basePath . $saveFileName . '.xml');
            $files[] = $saveFileName . '.xml';

            return $files;
        }

        /**
         * Generate and save sitemap index file
         *
         * @param array $files
         */
        private function generateIndex($files) {
            $xmlFile = $this->getXmlObject(self::FILE_TYPE_INDEX);

            foreach($files as $file) {
                $sitemap = $xmlFile->addChild('sitemap');
                $sitemap->addChild('loc', $this->serverUrl . '/' . $file);
            }

            $xmlFile->saveXML($this->basePath . self::INDEX_FILE_NAME);
        }

        /**
         * Get SimpleXML object base structure
         *
         * @param string $type File type index or main
         *
         * @return \SimpleXMLElement
         * @throws Exception
         */
        private function getXmlObject($type = self::FILE_TYPE_MAIN) {
            $str = '<?xml version="1.0" encoding="UTF-8"?>';

            switch($type) {
                case self::FILE_TYPE_MAIN:
                    $str .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
                    break;
                case self::FILE_TYPE_INDEX:
                    $str .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>';
                    break;
                default:
                    throw new Exception('Wrong file type: ' . $type . ' `main` or `index` are allowed');
            }

            return new \SimpleXMLElement($str);
        }

        /**
         * Helper method for pretty bytes output
         *
         * @param $bytes
         * @param int $precision
         *
         * @return string
         */
        private function formatBytes($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];

            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            $bytes /= pow(1024, $pow);

            return round($bytes, $precision) . ' ' . $units[$pow];
        }
    }