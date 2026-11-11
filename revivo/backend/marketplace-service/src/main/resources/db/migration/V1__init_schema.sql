-- =====================================================================
-- Revivo — Marketplace : schéma initial
-- =====================================================================

CREATE TABLE users (
    id              BIGSERIAL PRIMARY KEY,
    username        VARCHAR(30)  NOT NULL UNIQUE,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(100) NOT NULL,
    bio             VARCHAR(500),
    city            VARCHAR(100),
    country         VARCHAR(2),
    avatar_url      VARCHAR(500),
    role            VARCHAR(20)  NOT NULL DEFAULT 'USER',
    banned          BOOLEAN      NOT NULL DEFAULT FALSE,
    holiday_mode    BOOLEAN      NOT NULL DEFAULT FALSE,
    rating_avg      NUMERIC(3, 2) NOT NULL DEFAULT 0,
    rating_count    INTEGER      NOT NULL DEFAULT 0,
    created_at      TIMESTAMPTZ  NOT NULL,
    updated_at      TIMESTAMPTZ  NOT NULL
);

CREATE TABLE refresh_tokens (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT       NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    token_hash  VARCHAR(64)  NOT NULL UNIQUE,
    expires_at  TIMESTAMPTZ  NOT NULL,
    revoked     BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ  NOT NULL,
    updated_at  TIMESTAMPTZ  NOT NULL
);
CREATE INDEX idx_refresh_tokens_user ON refresh_tokens (user_id);

CREATE TABLE follows (
    id           BIGSERIAL PRIMARY KEY,
    follower_id  BIGINT      NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    followed_id  BIGINT      NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    created_at   TIMESTAMPTZ NOT NULL,
    updated_at   TIMESTAMPTZ NOT NULL,
    CONSTRAINT uq_follow UNIQUE (follower_id, followed_id),
    CONSTRAINT ck_follow_self CHECK (follower_id <> followed_id)
);
CREATE INDEX idx_follows_followed ON follows (followed_id);

-- ---------------------------------------------------------------------
-- Catalogue
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id         BIGSERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    parent_id  BIGINT REFERENCES categories (id)
);

CREATE TABLE brands (
    id    BIGSERIAL PRIMARY KEY,
    name  VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE items (
    id              BIGSERIAL PRIMARY KEY,
    seller_id       BIGINT        NOT NULL REFERENCES users (id),
    category_id     BIGINT        NOT NULL REFERENCES categories (id),
    brand_id        BIGINT REFERENCES brands (id),
    title           VARCHAR(100)  NOT NULL,
    description     VARCHAR(3000) NOT NULL,
    size            VARCHAR(20),
    color           VARCHAR(30),
    item_condition  VARCHAR(20)   NOT NULL,
    price           NUMERIC(10, 2) NOT NULL CHECK (price > 0),
    status          VARCHAR(20)   NOT NULL,
    view_count      INTEGER       NOT NULL DEFAULT 0,
    favorite_count  INTEGER       NOT NULL DEFAULT 0,
    version         BIGINT        NOT NULL DEFAULT 0,
    created_at      TIMESTAMPTZ   NOT NULL,
    updated_at      TIMESTAMPTZ   NOT NULL
);
CREATE INDEX idx_items_status_created ON items (status, created_at DESC);
CREATE INDEX idx_items_seller ON items (seller_id);
CREATE INDEX idx_items_category ON items (category_id);

CREATE TABLE item_photos (
    id        BIGSERIAL PRIMARY KEY,
    item_id   BIGINT       NOT NULL REFERENCES items (id) ON DELETE CASCADE,
    url       VARCHAR(500) NOT NULL,
    position  INTEGER      NOT NULL
);
CREATE INDEX idx_item_photos_item ON item_photos (item_id);

CREATE TABLE favorites (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT      NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    item_id     BIGINT      NOT NULL REFERENCES items (id) ON DELETE CASCADE,
    created_at  TIMESTAMPTZ NOT NULL,
    updated_at  TIMESTAMPTZ NOT NULL,
    CONSTRAINT uq_favorite UNIQUE (user_id, item_id)
);
CREATE INDEX idx_favorites_item ON favorites (item_id);

-- ---------------------------------------------------------------------
-- Transactions
-- ---------------------------------------------------------------------
CREATE TABLE offers (
    id          BIGSERIAL PRIMARY KEY,
    item_id     BIGINT         NOT NULL REFERENCES items (id) ON DELETE CASCADE,
    buyer_id    BIGINT         NOT NULL REFERENCES users (id),
    amount      NUMERIC(10, 2) NOT NULL CHECK (amount > 0),
    status      VARCHAR(20)    NOT NULL,
    expires_at  TIMESTAMPTZ    NOT NULL,
    created_at  TIMESTAMPTZ    NOT NULL,
    updated_at  TIMESTAMPTZ    NOT NULL
);
CREATE INDEX idx_offers_item_buyer ON offers (item_id, buyer_id);
CREATE INDEX idx_offers_status_expires ON offers (status, expires_at);

CREATE TABLE orders (
    id                 BIGSERIAL PRIMARY KEY,
    item_id            BIGINT         NOT NULL REFERENCES items (id),
    buyer_id           BIGINT         NOT NULL REFERENCES users (id),
    seller_id          BIGINT         NOT NULL REFERENCES users (id),
    offer_id           BIGINT REFERENCES offers (id),
    item_price         NUMERIC(10, 2) NOT NULL,
    shipping_price     NUMERIC(10, 2) NOT NULL,
    protection_fee     NUMERIC(10, 2) NOT NULL,
    total              NUMERIC(10, 2) NOT NULL,
    status             VARCHAR(20)    NOT NULL,
    shipping_option    VARCHAR(20)    NOT NULL,
    shipping_address   VARCHAR(500),
    tracking_number    VARCHAR(100),
    payment_reference  VARCHAR(100)   NOT NULL,
    dispute_reason     VARCHAR(1000),
    paid_at            TIMESTAMPTZ,
    shipped_at         TIMESTAMPTZ,
    delivered_at       TIMESTAMPTZ,
    completed_at       TIMESTAMPTZ,
    cancelled_at       TIMESTAMPTZ,
    version            BIGINT         NOT NULL DEFAULT 0,
    created_at         TIMESTAMPTZ    NOT NULL,
    updated_at         TIMESTAMPTZ    NOT NULL
);
CREATE INDEX idx_orders_buyer ON orders (buyer_id);
CREATE INDEX idx_orders_seller ON orders (seller_id);
CREATE INDEX idx_orders_status ON orders (status, delivered_at);

CREATE TABLE wallets (
    id                 BIGSERIAL PRIMARY KEY,
    user_id            BIGINT         NOT NULL UNIQUE REFERENCES users (id) ON DELETE CASCADE,
    available_balance  NUMERIC(12, 2) NOT NULL DEFAULT 0 CHECK (available_balance >= 0),
    pending_balance    NUMERIC(12, 2) NOT NULL DEFAULT 0 CHECK (pending_balance >= 0),
    version            BIGINT         NOT NULL DEFAULT 0,
    created_at         TIMESTAMPTZ    NOT NULL,
    updated_at         TIMESTAMPTZ    NOT NULL
);

CREATE TABLE wallet_transactions (
    id           BIGSERIAL PRIMARY KEY,
    wallet_id    BIGINT         NOT NULL REFERENCES wallets (id) ON DELETE CASCADE,
    type         VARCHAR(30)    NOT NULL,
    amount       NUMERIC(12, 2) NOT NULL,
    order_id     BIGINT REFERENCES orders (id),
    description  VARCHAR(255),
    created_at   TIMESTAMPTZ    NOT NULL,
    updated_at   TIMESTAMPTZ    NOT NULL
);
CREATE INDEX idx_wallet_tx_wallet ON wallet_transactions (wallet_id, created_at DESC);

CREATE TABLE reviews (
    id              BIGSERIAL PRIMARY KEY,
    order_id        BIGINT       NOT NULL REFERENCES orders (id),
    author_id       BIGINT       NOT NULL REFERENCES users (id),
    target_user_id  BIGINT       NOT NULL REFERENCES users (id),
    rating          SMALLINT     NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment         VARCHAR(1000),
    created_at      TIMESTAMPTZ  NOT NULL,
    updated_at      TIMESTAMPTZ  NOT NULL,
    CONSTRAINT uq_review_order_author UNIQUE (order_id, author_id)
);
CREATE INDEX idx_reviews_target ON reviews (target_user_id, created_at DESC);

-- ---------------------------------------------------------------------
-- Messagerie
-- ---------------------------------------------------------------------
CREATE TABLE conversations (
    id               BIGSERIAL PRIMARY KEY,
    item_id          BIGINT REFERENCES items (id) ON DELETE SET NULL,
    participant_a    BIGINT      NOT NULL REFERENCES users (id),
    participant_b    BIGINT      NOT NULL REFERENCES users (id),
    last_message_at  TIMESTAMPTZ,
    created_at       TIMESTAMPTZ NOT NULL,
    updated_at       TIMESTAMPTZ NOT NULL,
    CONSTRAINT ck_conversation_order CHECK (participant_a < participant_b)
);
CREATE UNIQUE INDEX uq_conversation ON conversations (participant_a, participant_b, COALESCE(item_id, 0));

CREATE TABLE messages (
    id               BIGSERIAL PRIMARY KEY,
    conversation_id  BIGINT        NOT NULL REFERENCES conversations (id) ON DELETE CASCADE,
    sender_id        BIGINT        NOT NULL REFERENCES users (id),
    content          VARCHAR(2000) NOT NULL,
    read_at          TIMESTAMPTZ,
    created_at       TIMESTAMPTZ   NOT NULL,
    updated_at       TIMESTAMPTZ   NOT NULL
);
CREATE INDEX idx_messages_conversation ON messages (conversation_id, created_at);

-- ---------------------------------------------------------------------
-- Modération
-- ---------------------------------------------------------------------
CREATE TABLE reports (
    id                BIGSERIAL PRIMARY KEY,
    reporter_id       BIGINT       NOT NULL REFERENCES users (id),
    item_id           BIGINT REFERENCES items (id) ON DELETE SET NULL,
    reported_user_id  BIGINT REFERENCES users (id),
    reason            VARCHAR(30)  NOT NULL,
    details           VARCHAR(1000),
    status            VARCHAR(20)  NOT NULL,
    resolution_note   VARCHAR(500),
    resolved_at       TIMESTAMPTZ,
    created_at        TIMESTAMPTZ  NOT NULL,
    updated_at        TIMESTAMPTZ  NOT NULL
);
CREATE INDEX idx_reports_status ON reports (status, created_at);

-- ---------------------------------------------------------------------
-- Transactional Outbox (publication fiable vers Kafka)
-- ---------------------------------------------------------------------
CREATE TABLE outbox_events (
    id            UUID PRIMARY KEY,
    topic         VARCHAR(100) NOT NULL,
    event_key     VARCHAR(100) NOT NULL,
    event_type    VARCHAR(100) NOT NULL,
    payload       TEXT         NOT NULL,
    created_at    TIMESTAMPTZ  NOT NULL,
    published_at  TIMESTAMPTZ,
    attempts      INTEGER      NOT NULL DEFAULT 0,
    last_error    VARCHAR(1000)
);
CREATE INDEX idx_outbox_unpublished ON outbox_events (created_at) WHERE published_at IS NULL;
