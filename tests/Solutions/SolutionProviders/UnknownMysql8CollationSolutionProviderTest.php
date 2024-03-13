<?php

use Illuminate\Database\QueryException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UnknownMysql8CollationSolutionProvider;

it('can solve an an unknown mysql collation ', function () {
    $solutionProvider = new UnknownMysql8CollationSolutionProvider();

    $exception = new QueryException(
        'mariadb',
        'select table_name as `name`, (data_length + index_length) as `size`, table_comment as `comment`, engine as `engine`, table_collation as `collation` from information_schema.tables where table_schema = \'mariadb_test\' and table_type = \'BASE TABLE\' order by table_name',
        [],
        new Exception('SQLSTATE[HY000]: General error: 1273 Unknown collation: \'utf8mb4_0900_ai_ci\'')
    );

    $solutions = $solutionProvider->getSolutions($exception);

    $solution = $solutions[0];

    expect($solution->getSolutionDescription())->toBe("Laravel 11 changed the default collation for MySQL and MariaDB. It seems you are trying to use the MySQL 8 collation `utf8mb4_0900_ai_ci` with a MariaDB or MySQL 5.7 database.\n\nEdit the `.env` file and use the correct database in the `DB_CONNECTION` key.");
});
