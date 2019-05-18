create database omnify DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE omnify.users (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name varchar(255) NULL,
	email varchar(255) NULL,
	google_id TEXT NULL,
    refresh_token TEXT NULL,
    needs_events_refresh BOOL DEFAULT false NULL,
    resource_id varchar(255) NULL,
    access_token TEXT NULL,
	created_at TIMESTAMP NULL,
	CONSTRAINT users_PK PRIMARY KEY (id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

CREATE TABLE omnify.events (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id INT UNSIGNED NOT NULL,
	event_id varchar(255) NULL,
	status varchar(255) NULL,
	event_created_at DATETIME NULL,
	event_updated_at DATETIME NULL,
	summary TEXT NULL,
	description TEXT NULL,
	visibility varchar(255) NULL,
	location varchar(255) NULL,
	created_at TIMESTAMP NULL,
	updated_at TIMESTAMP NULL,
	CONSTRAINT events_PK PRIMARY KEY (id),
	CONSTRAINT events_users_FK FOREIGN KEY (user_id) REFERENCES omnify.users(id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;
