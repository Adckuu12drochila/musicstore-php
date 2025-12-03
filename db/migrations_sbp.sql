BEGIN;

ALTER TABLE orders
    ADD COLUMN payment_method      VARCHAR(20) NOT NULL DEFAULT 'cod',
    ADD COLUMN payment_status      VARCHAR(20) NOT NULL DEFAULT 'pending',
    ADD COLUMN payment_external_id VARCHAR(64),
    ADD COLUMN total_amount        NUMERIC(10,2) NOT NULL DEFAULT 0,
    ADD COLUMN paid_at             TIMESTAMPTZ;

CREATE TABLE sbp_payments (
  id          SERIAL PRIMARY KEY,
  external_id VARCHAR(64) UNIQUE NOT NULL,           -- ID в "банке"
  order_id    INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
  amount      NUMERIC(10,2) NOT NULL,                -- сколько должны оплатить
  status      VARCHAR(20) NOT NULL DEFAULT 'pending',-- 'pending','paid','failed'
  created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Индексы для быстрого поиска по внешнему ID и заказу
CREATE INDEX idx_sbp_payments_external_id ON sbp_payments (external_id);
CREATE INDEX idx_sbp_payments_order_id    ON sbp_payments (order_id);

COMMIT;
