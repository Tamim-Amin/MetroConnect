CREATE TABLE IF NOT EXISTS comments (
                                        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        post_id INT UNSIGNED NOT NULL,
                                        user_id INT UNSIGNED NOT NULL,
                                        parent_id INT UNSIGNED NULL,
                                        content TEXT NOT NULL,
                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                        PRIMARY KEY (id),
    INDEX (post_id),
    INDEX (user_id),
    INDEX (parent_id),
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
