<?php
// =============================================================
// src/Core/QueryBuilder.php - Fluent Query Builder
// =============================================================
// Provides chainable query building with parameterized queries,
// relationships, pagination, and tenant-aware scoping
// =============================================================

namespace App\Core;

use PDO;
use PDOStatement;

class QueryBuilder
{
    private PDO $db;
    private string $table;
    private array $selects = ['*'];
    private array $joins = [];
    private array $wheres = [];
    private array $params = [];
    private array $orderBy = [];
    private array $groupBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $having = null;
    private array $havingParams = [];
    private ?int $tenantId = null;
    private ?string $tenantColumn = null;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Set target table
     */
    public function table(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        return $clone;
    }

    /**
     * Set tenant scope
     */
    public function forTenant(int $tenantId, string $column = 'tenant_id'): self
    {
        $clone = clone $this;
        $clone->tenantId = $tenantId;
        $clone->tenantColumn = $column;
        return $clone;
    }

    /**
     * SELECT columns
     */
    public function select(string ...$columns): self
    {
        $clone = clone $this;
        $clone->selects = $columns;
        return $clone;
    }

    /**
     * Add raw select expression
     */
    public function selectRaw(string $expression): self
    {
        $clone = clone $this;
        if ($clone->selects === ['*']) {
            $clone->selects = [];
        }
        $clone->selects[] = $expression;
        return $clone;
    }

    /**
     * WHERE clause
     */
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        $clone = clone $this;
        if ($value === null && $operator !== 'IS NULL' && $operator !== 'IS NOT NULL') {
            $value = $operator;
            $operator = '=';
        }

        if ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
            $clone->wheres[] = "{$column} {$operator}";
        } else {
            $clone->wheres[] = "{$column} {$operator} ?";
            $clone->params[] = $value;
        }
        return $clone;
    }

    /**
     * WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        $clone = clone $this;
        if (empty($values)) {
            $clone->wheres[] = '1 = 0';
            return $clone;
        }
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $clone->wheres[] = "{$column} IN ({$placeholders})";
        $clone->params = array_merge($clone->params, array_values($values));
        return $clone;
    }

    /**
     * WHERE NOT IN clause
     */
    public function whereNotIn(string $column, array $values): self
    {
        $clone = clone $this;
        if (empty($values)) {
            return $clone;
        }
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $clone->wheres[] = "{$column} NOT IN ({$placeholders})";
        $clone->params = array_merge($clone->params, array_values($values));
        return $clone;
    }

    /**
     * WHERE BETWEEN clause
     */
    public function whereBetween(string $column, mixed $start, mixed $end): self
    {
        $clone = clone $this;
        $clone->wheres[] = "{$column} BETWEEN ? AND ?";
        $clone->params[] = $start;
        $clone->params[] = $end;
        return $clone;
    }

    /**
     * WHERE LIKE clause
     */
    public function whereLike(string $column, string $pattern): self
    {
        return $this->where($column, 'LIKE', $pattern);
    }

    /**
     * Raw WHERE clause
     */
    public function whereRaw(string $sql, array $params = []): self
    {
        $clone = clone $this;
        $clone->wheres[] = $sql;
        $clone->params = array_merge($clone->params, $params);
        return $clone;
    }

    /**
     * JOIN clause
     */
    public function join(string $table, string $on, string $type = 'INNER'): self
    {
        $clone = clone $this;
        $clone->joins[] = "{$type} JOIN {$table} ON {$on}";
        return $clone;
    }

    /**
     * LEFT JOIN clause
     */
    public function leftJoin(string $table, string $on): self
    {
        return $this->join($table, $on, 'LEFT');
    }

    /**
     * ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $clone->orderBy[] = "{$column} {$direction}";
        return $clone;
    }

    /**
     * GROUP BY clause
     */
    public function groupBy(string ...$columns): self
    {
        $clone = clone $this;
        $clone->groupBy = array_merge($clone->groupBy, $columns);
        return $clone;
    }

    /**
     * HAVING clause
     */
    public function having(string $sql, array $params = []): self
    {
        $clone = clone $this;
        $clone->having = $sql;
        $clone->havingParams = $params;
        return $clone;
    }

    /**
     * LIMIT clause
     */
    public function limit(int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    /**
     * OFFSET clause
     */
    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->offset = $offset;
        return $clone;
    }

    /**
     * Execute and fetch all results
     */
    public function get(): array
    {
        $stmt = $this->executeQuery();
        return $stmt->fetchAll();
    }

    /**
     * Execute and fetch first result
     */
    public function first(): ?array
    {
        $clone = clone $this;
        $clone->limit = 1;
        $stmt = $clone->executeQuery();
        return $stmt->fetch() ?: null;
    }

    /**
     * Count results
     */
    public function count(): int
    {
        $clone = clone $this;
        $clone->selects = ['COUNT(*) as cnt'];
        $clone->orderBy = [];
        $clone->limit = null;
        $clone->offset = null;
        $stmt = $clone->executeQuery();
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Check if any records exist
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Paginate results (offset-based)
     */
    public function paginate(int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $total = $this->count();

        $results = $this->limit($perPage)
                        ->offset(($page - 1) * $perPage)
                        ->get();

        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
            'has_more' => ($page * $perPage) < $total,
        ];
    }

    /**
     * Cursor-based pagination (better performance)
     */
    public function cursorPaginate(int $perPage = 25, ?int $afterId = null, string $idColumn = 'id', string $direction = 'DESC'): array
    {
        $clone = clone $this;
        if ($afterId !== null) {
            $op = $direction === 'DESC' ? '<' : '>';
            $clone = $clone->where($idColumn, $op, $afterId);
        }

        $clone = $clone->orderBy($idColumn, $direction)->limit($perPage + 1);
        $results = $clone->get();

        $hasMore = count($results) > $perPage;
        if ($hasMore) {
            array_pop($results);
        }

        $nextCursor = $hasMore && !empty($results) ? end($results)[$idColumn] ?? null : null;

        return [
            'data' => $results,
            'has_more' => $hasMore,
            'next_cursor' => $nextCursor,
        ];
    }

    /**
     * Insert a record
     */
    public function insert(array $data): int
    {
        if ($this->tenantId !== null && $this->tenantColumn !== null) {
            $data[$this->tenantColumn] = $this->tenantId;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update records matching conditions
     */
    public function update(array $data): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $params = array_values($data);

        $sql = "UPDATE {$this->table} SET {$set}";
        $sql .= $this->buildWhereClause();
        $params = array_merge($params, $this->buildParams());

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Delete records matching conditions
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->buildParams());
        return $stmt->rowCount();
    }

    /**
     * Build and execute the SELECT query
     */
    private function executeQuery(): PDOStatement
    {
        $sql = "SELECT " . implode(', ', $this->selects);
        $sql .= " FROM {$this->table}";

        foreach ($this->joins as $join) {
            $sql .= " {$join}";
        }

        $sql .= $this->buildWhereClause();

        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        if ($this->having !== null) {
            $sql .= " HAVING {$this->having}";
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        $params = array_merge($this->buildParams(), $this->havingParams);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Build WHERE clause with tenant scoping
     */
    private function buildWhereClause(): string
    {
        $allWheres = $this->wheres;

        if ($this->tenantId !== null && $this->tenantColumn !== null) {
            array_unshift($allWheres, "{$this->table}.{$this->tenantColumn} = ?");
        }

        if (empty($allWheres)) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $allWheres);
    }

    /**
     * Build parameter array with tenant param
     */
    private function buildParams(): array
    {
        $params = [];
        if ($this->tenantId !== null && $this->tenantColumn !== null) {
            $params[] = $this->tenantId;
        }
        return array_merge($params, $this->params);
    }

    /**
     * Execute a callback within a database transaction
     * Automatically commits on success or rolls back on exception
     *
     * @template T
     * @param \Closure(): T $callback
     * @return T
     * @throws \Throwable Re-throws after rollback
     */
    public function transaction(\Closure $callback): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $callback($this);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
