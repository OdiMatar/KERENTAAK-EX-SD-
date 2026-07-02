DROP PROCEDURE IF EXISTS sp_register_user;
DROP PROCEDURE IF EXISTS sp_get_user_login_summary;
DROP PROCEDURE IF EXISTS sp_get_employees_overview;

DELIMITER //

CREATE PROCEDURE sp_register_user(
    IN p_name VARCHAR(255),
    IN p_email VARCHAR(255),
    IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO users (name, email, password, created_at, updated_at)
    VALUES (p_name, p_email, p_password, NOW(), NOW());

    SELECT LAST_INSERT_ID() AS user_id;
END //

CREATE PROCEDURE sp_get_user_login_summary()
BEGIN
    SELECT
        users.id,
        users.name,
        users.email,
        MAX(technical_logs.created_at) AS last_login_at,
        COUNT(technical_logs.id) AS login_count
    FROM users
    LEFT JOIN technical_logs
        ON technical_logs.user_id = users.id
        AND technical_logs.action = 'login'
    GROUP BY users.id, users.name, users.email
    ORDER BY last_login_at DESC;
END //

CREATE PROCEDURE sp_get_employees_overview()
BEGIN
    SELECT
        users.id,
        users.name,
        users.email,
        users.role,
        users.phone
    FROM users
    WHERE users.role = 'medewerker'
    ORDER BY users.name ASC;
END //

DELIMITER ;
