<?php

    namespace Lapshev\Sitemap;

    /**
     * Class Entire Sitemap entire
     *
     * @package Lapshev\Sitemap
     */
    class Entire
    {
        protected $loc;
        protected $lastMod;
        protected $changeFreq;
        protected $priority;

        private $allowedFreq = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

        public function __construct($loc, \DateTime $lastMod, $changeFreq = 'monthly', $priority = '0.5') {
            if(empty($loc) || empty($lastMod)) {
                throw new Exception('Empty loc or last-mod');
            }

            if(!in_array($changeFreq, $this->allowedFreq)) {
                $mess = 'wrong changeFreq: ' . $changeFreq . ' allowed: ' . implode(',', $this->allowedFreq);
                throw new Exception($mess);
            }

            $priority = round(floatval($priority), 1);

            $this->loc = $loc;
            $this->lastMod = $lastMod->format(\DateTime::W3C);
            $this->changeFreq = $changeFreq;
            $this->priority = $priority;
        }

        public function add(\SimpleXMLElement $siteMap) {
            $url = $siteMap->addChild('url');

            //todo:
            $url->addChild('loc', $this->loc);
            $url->addChild('lastmod', $this->lastMod);
            $url->addChild('changefreq', $this->changeFreq);
            $url->addChild('priority', $this->priority);

            return $url;
        }

        public function test() {
            $url = $this->loc;   //todo

            if(filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new Exception('not_valid');
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL        => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true,
                CURLOPT_NOBODY     => true, CURLOPT_TIMEOUT_MS => 30 * 1000,
            ]);

            curl_exec($curl);

            $curlError = curl_errno($curl);

            if($curlError) {
                if($curlError === 28) {
                    throw new EntireTestException('timeout');
                }
                throw new EntireTestException('err_' . $curlError);
            }

            $curlInfo = curl_getinfo($curl);

            if($curlInfo['http_code'] !== 200) {
                throw new EntireTestException('code_' . $curlInfo['http_code']);
            }
        }

        /**
         * Set loc server url
         *
         * @param string $url
         */
        public function setLocServer($url) {
            $this->loc = $url . $this->loc;
        }

        /**
         * @return string
         */
        public function getLoc() {
            return $this->loc;
        }

        /**
         * @return string
         */
        public function getLastMod() {
            return $this->lastMod;
        }

        /**
         * @return string
         */
        public function getChangeFreq() {
            return $this->changeFreq;
        }

        /**
         * @return float
         */
        public function getPriority() {
            return $this->priority;
        }
    }