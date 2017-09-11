<?php

    namespace Lapshev\Sitemap\Providers;

    use Lapshev\Sitemap\Entire;
    use Lapshev\Sitemap\Exception;
    use Lapshev\Sitemap\iProvider;

    /**
     * Class Simple
     *
     * @package newsite\SiteMap\Providers
     */
    class Simple implements iProvider
    {
        private $elements = [];

        /**
         * Simple constructor.
         *
         * @param array $elements
         *  $element[0] => loc,
         *  $element[1] => lastMod,
         *  $element[2] => change freq,
         *  $element[3] => priority
         */
        public function __construct($elements = []) {
            $this->elements = $elements;
        }

        public function prepareData() {
            // nothing to do here
        }

        public function getNextEntire() {
            if($element = array_shift($this->elements)) {

                if(empty($element[0])) {
                    throw new Exception('empty loc passed with element');
                }

                if(empty($element[1])) {
                    $element[1] = new \DateTime();
                }

                if(empty($element[2])) {
                    $element[2] = 'daily';
                }

                if(empty($element[3])) {
                    $element[3] = '0.5';
                }

                $entire = new Entire($element[0], $element[1], $element[2], $element[3]);

                return $entire;
            }

            return null;
        }
    }