CREATE TABLE IF NOT EXISTS t_artist_names (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    processed TINYINT,
    PRIMARY KEY(id)
);
