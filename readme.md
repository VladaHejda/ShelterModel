LazyDataMapper
===

### add composer dependency

```json
"require": {
    "vladahejda/lazydatamapper": "@dev"
}
```

### create `LazyDataMapper\IExternalCache`

Apply some **persistent** caching way in `LazyDataMapper\IExternalCache` (`SomeFileCache` class is illustrative).

```php
class LazyDataMapperCache implements \LazyDataMapper\IExternalCache
{
	/** @var \SomeFileCache */
	private $cache;

	public function __construct(SomeFileCache $cache)
	{
		$this->cache = $cache;
	}

	public function load($key)
	{
		return $this->cache->load($key);
	}

	public function save($key, $value)
	{
		$this->cache->save($key, $value);
	}
}
```

### init LazyDataMapper

It does not look simple, but it let you customizing (see in DOC).

```php
$someFileCache = new \SomeFileCache;
$cache = new \LazyDataMapperCache($someFileCache);
$requestKey = new \LazyDataMapper\RequestKey;
$entityServiceAccessor = new \LazyDataMapper\EntityServiceAccessor;
$suggestorCache = new \LazyDataMapper\SuggestorCache($cache, $requestKey, $entityServiceAccessor);
$lazyDataMapperAccessor = new \LazyDataMapper\Accessor($suggestorCache, $entityServiceAccessor);
```

Or you can let build dependencies by your famous framework.

### create your first model

For each Entity model, you will need implement at least `ParamMap`, `IMapper`, `Facade` and `Entity`.

Let's imagine model of product.

Basically, the `Entity` and `Facade` works even if they are empty:

```php
class Product extends \LazyDataMapper\Entity
{
}

class ProductFacade extends \LazyDataMapper\Facade
{
}
```

`ParamMap` must define the list of parameters, which will be loaded from data storage.
When you use database, these are names of columns (primary id is not necessary).

```php
class ProductParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = [
		'name', 'price', 'count',
	];
}
```

Mapper becomes your pivotal class for each model. It is the place where you must implement data getting from anywhere.
Watch this example:

```php
use LazyDataMapper\ISuggestor,
	LazyDataMapper\IDataHolder,
	LazyDataMapper\DataHolder;

class Mapper implements \LazyDataMapper\IMapper
{

	/** @var \PDO */
	protected $pdo;

	public function __construct()
	{
		// this is not really the clear way, use dependency injection - see EntityServiceAccessor customizing DOC
		$this->pdo = new \PDO('mysql:host=127.0.0.1;dbname=test', 'user', 'pass');
	}

	public function exists($id)
	{
		$statement = $this->pdo->prepare('SELECT 1 FROM product WHERE id = ?');
		$statement->execute([$id]);
		return (bool) $statement->fetchColumn();
	}

	public function getById($id, ISuggestor $suggestor)
	{
		$params = $suggestor->getParamNames();
		$columns = '`' . implode('`,`', $params) . '`';
		$statement = $this->pdo->prepare("SELECT $columns FROM product WHERE id = ?");
		$statement->execute([$id]);
		$params = array_intersect_key($statement->fetch(), array_flip($params));
		$holder = new DataHolder($suggestor);
		$holder->setParams($params);
		return $holder;
	}

	public function getIdsByRestrictions(\LazyDataMapper\IRestrictor $restrictor)
	{
	}

	public function getByIdsRange(array $ids, ISuggestor $suggestor)
	{
	}

	public function save($id, IDataHolder $holder)
	{
	}

	public function create(IDataHolder $holder)
	{
	}

	public function remove($id)
	{
	}
}
```

And this is it! You have got the most elemental LazyDataMapper model for real work!

For simple get implementing of method `exists()` and `getById()` is needed.

- `exists()` says whether item with given id exists or not.

- `getById()` gets `Suggestor` that has method `getParamNames()` witch says,
what parameters (defined in `ParamMap`) you have to get from storage.
- `getById()` returns `new DataHolder` with data got from storage (set by `setParams()`) indexed by parameter name.

### get the Entity!

Now, create ProductFacade:

```php
$productFacade = new \ProductFacade($lazyDataMapperAccessor);
```

and happily get entities:

```php
$product = $productFacade->getById(5);

echo $product->name;
echo "\n";
echo $product->price;
```
