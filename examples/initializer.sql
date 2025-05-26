CREATE TABLE treatments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    parameters TEXT,
    function TEXT
);

CREATE TABLE database_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    writable INTEGER /* 1 = SIM, 0 = NAO */
);

CREATE TABLE databases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type INTEGER,
    name TEXT,
    config TEXT
);

CREATE TABLE collections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    database_origin INTEGER,
    database_destiny INTEGER
);

CREATE TABLE migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    collection_id INTEGER,
    json TEXT,
    status INTEGER,
);