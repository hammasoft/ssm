-- Revivo — Notification service (base de données dédiée : « database per service »)

CREATE TABLE notifications (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT       NOT NULL,
    type        VARCHAR(50)  NOT NULL,
    title       VARCHAR(200) NOT NULL,
    body        VARCHAR(500),
    link        VARCHAR(300),
    read        BOOLEAN      NOT NULL DEFAULT FALSE,
    event_id    UUID         NOT NULL,
    created_at  TIMESTAMPTZ  NOT NULL
);
CREATE INDEX idx_notifications_user ON notifications (user_id, created_at DESC);
CREATE INDEX idx_notifications_unread ON notifications (user_id) WHERE read = FALSE;

-- Projection locale des utilisateurs (alimentée par UserRegistered) : évite tout appel
-- synchrone au marketplace-service pour retrouver un e-mail.
CREATE TABLE user_directory (
    user_id   BIGINT PRIMARY KEY,
    username  VARCHAR(30)  NOT NULL,
    email     VARCHAR(255) NOT NULL
);

-- Idempotence : Kafka garantit une livraison « at-least-once ».
CREATE TABLE processed_events (
    event_id      UUID PRIMARY KEY,
    event_type    VARCHAR(100) NOT NULL,
    processed_at  TIMESTAMPTZ  NOT NULL
);
