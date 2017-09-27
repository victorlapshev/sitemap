# Sitemap generator
### Install
```sh
composer require lapshev/sitemap
```
### Usage
1. Create generator object
2. Create provider object
3. Add provider to generator
4. Generate

Specify filename while adding provider, content of this file is the result of merge multiply providers with same filename

```php
use Lapshev\Sitemap;
use Lapshev\Sitemap\Providers;

$sitemap = new Sitemap\Generator('http://example.com');
$simpleProvider = new Providers\Simple([
    ['/', new \DateTime(), 'daily', 0.5], 
    ['/news/', new \DateTime(), 'monthly', 1]
]);

$sitemap->addProvider('example', $simpleProvider);
// will output to example.xml and add this file sitemap.xml

$sitemap->generate($_SERVER['DOCUMENT_ROOT']);
```
For more advantage usage your should create your own provider that implements `iProvider` interface.
See example bellow:

```php
namespace MySite\Sitemap\Providers;

use Lapshev\Sitemap\BaseProvider;
use Lapshev\Sitemap\Entire;
use Lapshev\Sitemap\iProvider;

class DataBase extends BaseProvider implements iProvider
{
    private $dbResult;

    private $tableName;

    public function __construct($params = []) {
        parent::__construct($params);
        $this->tableName = $params['tableName'];
    }

    // will be called before fetching
    public function prepareData() {
        $servername = "localhost";
        $username = "username";
        $password = "password";
        $dbname = "myDB";

        // Create connection
        $conn = new \mysqli($servername, $username, $password, $dbname);

        $sql = "SELECT id, pageUrl, lastMod FROM {$this->tableName}";
        $this->dbResult = $conn->query($sql);
    }

    public function getNextEntire() {
        while($row = $this->dbResult->fetch_assoc()) {
            return new Entire($row['pageUrl'], $row['lastMod'], 'daily', 0.7);
        }

        return null;
    }
}
```

### Features
#### Check response
```php
$sitemap = new Sitemap\Generator('http://example.com', ['checkResponse' => true]);
```
Will check every url in the set and output wrong ones

### More examples
```php
use Lapshev\Sitemap\Generator;
use Lapshev\Sitemap\Providers\Simple;

use newsite\SiteMap\Providers\Elements;
use newsite\SiteMap\Providers\Files;
use newsite\SiteMap\Providers\Sections;
use newsite\SiteMap\Providers\Banners;

use Bitrix\Main\Type\DateTime;

require 'common.php';

$params = [
    'check_response'    => in_array('checkResponse', $argv)
];

$arScheme = [
    'catalog'   => [
        new Sections(['iBlockID' => CATALOG_SEO_MENU, 'indexID' => 1, 'priority' => 0.5]),
    ],
    'menu'      => [
        new Sections(['iBlockID' => CATALOG_LOGIC_MENU, 'indexID' => 1, 'priority' => 0.5]),
    ],
    'goods'     => [
        new Elements(['iBlockID' => CATALOG_PRODUCT_IBLOCK, 'indexID' => 1, 'priority' => 0.5]),
    ],
    'news'      => [
        new Simple([ ['/news/', new DateTime(), 'hourly', 1] ]),
        new Elements(['iBlockID' => 25, 'priority' => 0.8, 'changeFreq' => 'monthly']),
    ],
    'articles'  => [
        new Simple([ ['/helpful/', new DateTime(), 'hourly', 0.4] ]),
        new Elements(['iBlockID' => 29, 'priority' => 0.4]),
    ],
    'info'      => [
        new Simple([ ['/', new DateTime(), 'hourly', 1] ]),
        new Files(['priority' => 0.4])
    ],
    'sale'      => [
        new Banners(['type' => 'actionCatalogList', 'priority' => 1, 'changeFreq' => 'daily']),
    ]
];

$siteMap = new Generator('http://example.com', $params);

foreach($arScheme as $fileName => $providers) {
    foreach($providers as $provider){
        $siteMap->addProvider($fileName, $provider);
    }
}

$siteMap->generate($_SERVER['DOCUMENT_ROOT']);
```
