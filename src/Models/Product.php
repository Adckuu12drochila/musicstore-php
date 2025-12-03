<?php
namespace App\Models;

use PDO;

class Product
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает список продуктов с опциональным фильтром по категории, поиском и сортировкой
     *
     * @param int         $limit
     * @param int         $offset
     * @param int|null    $categoryId — фильтр по категории
     * @param string|null $search     — строка поиска по имени
     * @param string|null $sort       — ключ сортировки: 'price_asc', 'price_desc', 'oldest', 'newest'
     * @return array
     */
    public function all(
        int $limit = 100,
        int $offset = 0,
        ?int $categoryId = null,
        ?string $search = null,
        ?string $sort = null
    ): array {
        $sql    = 'SELECT p.*, c.name AS category_name
                   FROM products p
                   JOIN categories c ON c.id = p.category_id';
        $where  = [];
        $params = [];

        if ($categoryId !== null) {
            $where[]       = 'p.category_id = :cat';
            $params['cat'] = $categoryId;
        }
        if ($search !== null && $search !== '') {
            $where[]          = 'p.name ILIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Определяем ORDER BY в зависимости от параметра сортировки
        switch ($sort) {
            case 'price_asc':
                $orderBy = 'p.price ASC';
                break;
            case 'price_desc':
                $orderBy = 'p.price DESC';
                break;
            case 'oldest':
                $orderBy = 'p.created_at ASC';
                break;
            default:
                // 'newest' и всё остальное
                $orderBy = 'p.created_at DESC';
        }
        $sql .= " ORDER BY $orderBy
                  LIMIT :lim OFFSET :off";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $type);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Возвращает общее число продуктов с теми же фильтрами (без пагинации и сортировки)
     *
     * @param int|null    $categoryId
     * @param string|null $search
     * @return int
     */
    public function count(?int $categoryId = null, ?string $search = null): int
    {
        $sql    = 'SELECT COUNT(*) FROM products p';
        $where  = [];
        $params = [];

        if ($categoryId !== null) {
            $where[]       = 'p.category_id = :cat';
            $params['cat'] = $categoryId;
        }
        if ($search !== null && $search !== '') {
            $where[]          = 'p.name ILIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $type);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product === false ? null : $product;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (category_id, name, description, price, stock, image_url)
             VALUES (:category_id, :name, :description, :price, :stock, :image_url)'
        );
        $stmt->execute([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => $data['price'],
            'stock'       => $data['stock'] ?? 0,
            'image_url'   => $data['image_url'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET category_id = :category_id,
                 name        = :name,
                 description = :description,
                 price       = :price,
                 stock       = :stock,
                 image_url   = :image_url
             WHERE id = :id'
        );
        return $stmt->execute([
            'id'          => $id,
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'price'       => $data['price'],
            'stock'       => $data['stock'] ?? 0,
            'image_url'   => $data['image_url'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
