# Time Management App

** UNDER DEVELOPMENT **

Sample project by [Jose Gleiser](http://www.josegleiser.com)
- Database abstraction through [PDO](http://php.net/manual/es/book.pdo.php), used with MySQL in this project.
- RESTfull Api developed with PHP [slimframework](https://www.slimframework.com/)
- Frontend and Ajax done with [jQuery](https://jquery.com/)

**Developed under**
- PHP 5.6.25
- MySQL Community Server 5.7.14

**Requirements**
- [Composer](https://getcomposer.org/)

**Install**
1. Configure database dns and auth data in *src/config/database_data.php*
2. Import file *sql/time_manager.sql* to create the tables in MySQL
3. Inside the project path, install slim, php-view and monolog with composer using the file composer.json
`composer install`
4. Change the path to <project_path>/public and start a php server
`php -S localhost:8080`
5. Open your browser and go to [http://localhost:8080/](http://localhost:8080/)
