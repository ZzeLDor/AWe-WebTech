<?php

declare(strict_types=1);

class Database
{
    private PDO    $connection;
    private string $table;
    private array  $conditions = [];
    private array  $parameters = [];
    private ?int   $limit      = null;
    private array  $columns    = [];

    /**
     * Create a new database connection.
     */
    public function __construct()
    {
        $dbName = 'web';
        $dbUser = 'web';
        $dbPass = 'web';

        $this->connection = new PDO("mysql:host=localhost;dbname=$dbName", $dbUser, $dbPass);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Set the table for the query.
     *
     * @param  string $table
     * @return $this
     */
    public function table(string $table): self
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    /**
     * Add a WHERE = condition.
     *
     * @param  string $column
     * @param  mixed  $value
     * @return $this
     */
    public function where(string $column, mixed $value): self
    {
        $param = $this->paramKey($column);
        $this->conditions[] = "$column = $param";
        $this->parameters[$param] = $value;
        return $this;
    }

    /**
     * Add a LIKE condition.
     *
     * @param  string $column
     * @param  string $pattern
     * @return $this
     */
    public function like(string $column, string $pattern): self
    {
        $param = $this->paramKey($column);
        $this->conditions[] = "$column LIKE $param";
        $this->parameters[$param] = $pattern;
        return $this;
    }

    /**
     * Add a BETWEEN condition.
     *
     * @param  string $column
     * @param  mixed  $start
     * @param  mixed  $end
     * @return $this
     */
    public function between(string $column, mixed $start, mixed $end): self
    {
        $param1 = $this->paramKey($column . '_start');
        $param2 = $this->paramKey($column . '_end');

        $this->conditions[] = "$column BETWEEN $param1 AND $param2";
        $this->parameters[$param1] = $start;
        $this->parameters[$param2] = $end;

        return $this;
    }

    /**
     * Set a LIMIT on the query.
     *
     * @param  int $n
     * @return $this
     */
    public function limit(int $n): self
    {
        $this->limit = $n;
        return $this;
    }

    /**
     * Set the columns to select.
     *
     * @param  array<int, string> $columns
     * @return $this
     */
    public function select(array $columns = []): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Execute the SELECT query.
     *
     * @return array<int, array<string, mixed>> Result rows as associative arrays
     *
     * @phpstan-return list<array<string, mixed>>
     */
    public function get(): array
    {
        $columnList = empty($this->columns) ? '*' : implode(', ', $this->columns);
        $sql        = "SELECT $columnList FROM {$this->table}";

        if ($this->conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $this->conditions);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT $this->limit";
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->parameters);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate a unique param key.
     *
     * @param  string $key
     * @return string
     */
    private function paramKey(string $key): string
    {
        return ':' . str_replace(['.', '-', ' '], '_', $key) . count($this->parameters);
    }

    /**
     * Reset the internal state (for next query).
     */

    public function insert(array $data): bool
    {
        if (empty($rows)) {
            echo "Cannot insert empty rows".PHP_EOL;
            return false;
        }
        else{$columns = array_keys($data[0]);}


    }

    private function reset(): void
    {
        $this->conditions = [];
        $this->parameters = [];
        $this->limit      = null;
        $this->columns    = [];
    }

}
