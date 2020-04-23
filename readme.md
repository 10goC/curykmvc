# Documentation
## 1. Getting started
This guide will tell you the basic guidelines to build a project using this MVC library for PHP.

### 1.1 Directory structure
The project should containt the following structure:

```
application/
	Application/
		Controller/
		Model/
		Boostrap.php
	languages/
	view/
	config.global.php
	config.local.php
lib/
	Abm/
	Mvc/
public/
	.htaccess
	index.php
```
*Note: The /lib folder may actually not be present inside the project, but linked from outside of it.*

Every HTTP request will go through /public/index.php, which will in turn load the library, parse the request, call the corresponding controller and render a view (unless the controller says not to).

The ideal way of acheiving this is to create a virtual host, so that the root directory of the website is /public.
In the case this is not possible due to server configuration restrictions, there must be an .htaccess file inside the root directory targeting all requests to /public directory.

### 1.2 Workflow
By default, all requests will be parsed like this:

http://domain.com/`basepath`/`controller`/`action`

The `basepath` is optional and can be defined in configuration (See configuration section)

### 1.2.1 Controller
A `controller` must be created matching the name of the URL portion, i.e:

Let's assume the following URL: `http://domain.com/basepath/start`

Create a file named /application/Application/Controller/StartController.php
The controller code must be as follows:

```php
<?php namespace Application\Controller;

use Mvc\Controller;
	
class StartController extends Controller
{
}
```	
### 1.2.2 Action
Following the previous example, since no action is specified in the URL, index will be assumed by default. Hence, create an index action inside the controller class:

```php
<?php namespace Application\Controller;
	
use Mvc\Controller;
	
class StartController extends Controller
{
	public function indexAction()
	{
	}
}
```

### 1.2.3 View
Create a template for rendering the desired view. The template files must contain the PHTML extension.

Following with the same example, the file would be located at /application/view/start/index.phtml

### 1.2.4 Layout
By default, all actions will be rendered inside a layout. This can be disabled from within the controller, like this:

```php
$this->setLayout(null);
```

The default layout will be located at /application/view/layout/layout.phtml.

The same controller method is used for changing the layout to use.

```php
$this->setLayout( 'another-layout' );
```

The phtml file must be inside the same directory (in this case */application/view/layout/***another-layout***.phtml*).

## 2. Configuration
Two configuration files are loaded automatically:

/application/config.global.php
/application/config.local.php
Config.local.php should be excluded from version control.
All configurations set in local file will override the global setup

### 2.1 Working in a subdirectory
Include a key 'basepath' in config with the name of the subdirectory, i.e:

```php
'basepath' => '/lib/mvc/trunk/public',
```

To make links work from within the view use baseUrl helper for generating absolute links inside the same domain, i.e:

```php
echo $this->baseUrl('/assets/js/script.js');
```

Or serverUrl for full URLs, i.e:

```php
echo $this->serverUrl('/assets/js/script.js');
```

## 3. Database interaction
Database configuration goes in config files (see configuration section) and require at least these options to be set:

* db
 * host
 * user
 * pass
 * name

### 3.1 The Table model
The Mvc\Db\Table model can be extended by your own models or used directly by creating a new instance and providing a table name to the 
constructor, i.e:

```php
$table = new Table($this, 'table_name');
```

You can extend the class and set the protected `$table` property to be able to instantiate the class without passing the second parameter to the constructor.

This model has the following methods:

* `fetch( $select, $bind = array() )`

	Takes up to two arguments. The first is a SQL string that may contain '?' placeholders and the second is an optional array of values to be bound in case placeholders are present in the query.

	Makes a SQL select query and returns a Resultset object containing the rows fetched. This object can be accessed like an array.
 
* `insert(array $values)`

	Takes one argument, which is an associative array of values to be inserted, where the key names should correspond to column names.

	Returns the inserted row primary key value on success or null in case of failure.

* `update($key, $id, $values)`

	Takes three arguments. The first one is a string representing the column name to be used as a reference (this will usually be the primary key).

	The second one is the value (usually the ID) of the row to be updated.

	And the third argument is an associative array of values, where the key names should correspond to column names.

* `delete($key, $id)`

	Takes two arguments. The first one is a string representing the column name to be used as a reference (this will usually be the primary key).

	The second one is the value (usually the ID) of the row to be deleted.