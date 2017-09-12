<?php

    namespace Example\SitemapProviders;

    use Bitrix\Main\ArgumentException;
    use Bitrix\Main\Loader;
    use Lapshev\Sitemap\BaseProvider;
    use Lapshev\Sitemap\Entire;
    use Lapshev\Sitemap\iProvider;

    class IblockElement extends BaseProvider implements iProvider
    {
        /** @var \CIBlockResult */
        private $dbRes;

        /* params */
        private $iblockId;
        private $filter;

        public function __construct($params = []) {
            parent::__construct($params);

            if(!empty(intval($params['iblockId']))) {
                $this->iblockId = intval($params['iblockId']);
            }
            else {
                throw new ArgumentException('iblockId is required param');
            }

            if(!empty($params['filter'])) {
                $this->filter = $params['filter'];
            }
        }

        /**
         * @inheritdoc
         */
        public function prepareData() {
            Loader::includeModule('iblock');

            $filter = array_merge([
                '=IBLOCK_ID' => $this->iblockId, '=ACTIVE' => 'Y',
            ], $this->filter);

            $this->dbRes = \CIBlockElement::GetList([], $filter, false, false, ['DETAIL_PAGE_URL']);
        }

        /**
         * @inheritdoc
         */
        public function getNextEntire() {

            if($arRes = $this->dbRes->GetNext()) {
                return new Entire($arRes['DETAIL_PAGE_URL'], new \DateTime(), 'daily', 0.5);
            }

            return null;
        }
    }