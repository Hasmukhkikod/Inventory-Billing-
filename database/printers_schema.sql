-- Printer Settings (Settings -> Printer Settings)
-- USB/Bluetooth pairing itself lives in the browser's own permission store
-- (WebUSB/WebBluetooth grants can't be centrally shared across computers) -
-- this table is the shop's named list of printers + which one is default,
-- plus full connection details for LAN printers (a real network address any
-- computer can use).

CREATE TABLE IF NOT EXISTS printers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    connection_type ENUM('USB','BLUETOOTH','LAN') NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    port INT(11) DEFAULT NULL,
    paper_width_dots INT(11) NOT NULL DEFAULT 576,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    created_by INT(11) DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_printers_default (is_default),
    KEY idx_printers_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
