## Some more about `provider` ##
Basically `provider` is a Class that should implement all [iProvider](/src/iProvider.php) interface methods.

### How it's works ###
At first, you should add provider to generator object
```php
$sitemap = new Sitemap\Generator('http://example.com');
$provider = new Providers\Simple([
    ['/', new \DateTime(), 'daily', 0.5], 
    ['/news/', new \DateTime(), 'monthly', 1]
]);
$sitemap->addProvider('filename', $provider);
```
You can add more than one provider with the same filename. At the result, this file will contain merged providers
result ordered by method call order.

Let take a look at provider methods:

`prepareData()` - will be executed just before generator start fetching providers elements, used to initiate
data for fetching e.g. Database queries, files lookup.

`getNextEntire()` - will be called one by one, until return `null`. Used to fetch prepared data. Should 
return [Entire](/src/Entire.php) or null if end reached.

### Examples ###
-[Bitrix Iblock Elements Provider](bitrix-iblock-index.php)