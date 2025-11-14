CREATE TABLE IF NOT EXISTS likes (
                                     id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                     user_id INT UNSIGNED NOT NULL,
                                     post_id INT UNSIGNED NOT NULL,
                                     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                     PRIMARY KEY (id),
    UNIQUE KEY uq_user_post (user_id, post_id),
    INDEX (post_id),
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
