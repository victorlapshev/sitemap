<?php

    namespace Lapshev\Sitemap;

    interface iProvider
    {
        /**
         * iProvider constructor. Init params
         *
         * @param array $params
         */
        public function __construct($params = []);

        /**
         * Get all necessary data
         *
         * @return mixed
         */
        public function prepareData();

        /**
         * Get next entire or null if finish fetching
         *
         * @return Entire
         */
        public function getNextEntire();
    }
