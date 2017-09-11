<?php

    namespace Lapshev\Sitemap;

    /**
     * Class BaseProvider
     *
     * @package Lapshev\Sitemap
     */
    class BaseProvider
    {
        protected $priority = 0.2;
        protected $changeFreq = 'monthly';

        public function __construct($params) {
            if(isset($params['priority'])) {
                $this->priority = $params['priority'];
                unset($params['priority']);
            }
            if(isset($params['changeFreq'])) {
                $this->changeFreq = $params['changeFreq'];
                unset($params['changeFreq']);
            }

            return $params;
        }
    }