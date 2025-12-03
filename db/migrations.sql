-- таблица категорий
CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- таблица пользователей
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(100),
  is_admin BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- таблица продуктов
CREATE TABLE products (
  id SERIAL PRIMARY KEY,
  category_id INTEGER NOT NULL REFERENCES categories(id),
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price NUMERIC(10,2) NOT NULL,
  stock INTEGER DEFAULT 0,
  image_url TEXT,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- таблица заказов
CREATE TABLE orders (
  id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(id),
  client_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  delivery_address TEXT NOT NULL,
  coupon_code VARCHAR(50),
  discount_amount NUMERIC(10,2) NOT NULL DEFAULT 0;

  -- бизнес-статус заказа
  status VARCHAR(20) NOT NULL DEFAULT 'new',

  -- Платёжные поля
  payment_method      VARCHAR(20) NOT NULL DEFAULT 'cod',    -- 'sbp', 'cod' и т.п.
  payment_status      VARCHAR(20) NOT NULL DEFAULT 'pending',-- 'pending','paid','failed','cancelled'
  payment_external_id VARCHAR(64),                           -- ID платежа в системе банка/мока
  total_amount        NUMERIC(10,2) NOT NULL DEFAULT 0,      -- зафиксированная сумма заказа
  paid_at             TIMESTAMPTZ,                           -- когда заказ был оплачен

  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- таблица позиций заказа
CREATE TABLE order_items (
  id SERIAL PRIMARY KEY,
  order_id INTEGER NOT NULL REFERENCES orders(id),
  product_id INTEGER NOT NULL REFERENCES products(id),
  quantity INTEGER NOT NULL DEFAULT 1,
  unit_price NUMERIC(10,2) NOT NULL
);

-- таблица купонов
CREATE TABLE coupons (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percent NUMERIC(5,2) NOT NULL CHECK (discount_percent > 0 AND discount_percent <= 100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    expires_at TIMESTAMP NULL
);
