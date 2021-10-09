# Prepare for project

Required settings: PHP at least 7.3, composer, MySQL.

Optional: redis, supervisor

Install php packages:

`composer install`

Create .env file

`cp .env.example .env`

Generate app key:

`php artisan key:generate`

Config database connection in `.env`

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=user
DB_PASSWORD=password
```

For better performance, redis should be added to the system to cache data and store queue jobs.

If redis is available we can change some variables in `.env`

```
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

Update CSV paths in `.env`

```
DATA_CSV_PATH=/full/path/to/Data8277.csv
AGE_CSV_PATH=/full/path/to/DimenLookupAge8277.csv
AREA_CSV_PATH=/full/path/to/DimenLookupArea8277.csv
ETHNIC_CSV_PATH=/full/path/to/DimenLookupEthnic8277.csv
GENDER_CSV_PATH=/full/path/to/DimenLookupSex8277.csv
YEAR_CSV_PATH=/full/path/to/DimenLookupYear8277.csv
```

# DB structure:

Run `php artisan migrate` to create all tables

# Import reference tables

Run `php artisan import:base` to import data to the following tables:

- ages
- areas
- ethnicities
- gender
- items
- years

# Import main

Import data to `items` table

Run `php artisan import:items`

`Data8277.csv` contains nearly 35 million rows, so we need to chunk data into multiple array.

Each chunk will be sent to a job (`InsertItems`) and will be processed in a queue.

Chunk size can be configured by changing `LIMIT_INSERT_ROWS` variable from `.env`.
Default value is 1000.
This value can be increased depend on computer resource.

## Running the queue worker

**Option 1**: use `queue:work` command (https://laravel.com/docs/8.x/queues#the-queue-work-command)

**Option 2**: use `supervisor` (https://laravel.com/docs/8.x/queues#supervisor-configuration)

It's recommended that we should run multiple queue workers by using supervisor for better performance.

Config for supervisor

`/etc/supervisor/conf.d/test-worker.conf`

If we use `QUEUE_CONNECTION=redis`, config queue connection as `redis` in config file.

```
[program:test-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/artisan queue:work redis --tries=3 --max-time=10800
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=chungvh
numprocs=10
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/import.log
stopwaitsecs=3600
```
# Query

Query that counts the rows From Data8277.csv where

- Area is ‘Hampstead’
- Age is above 45
- Female
- Year is 2018
- Asian

**Pre-requirement**: make sure you have already imported data to base table
by running command `php artisan import:base`.

Run the following command:

`php artisan query:row`
