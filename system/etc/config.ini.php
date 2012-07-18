;<?php die("No access;"); ?>
;File written by Cerenkov at 16:07, Sunday 20 July 2008
[cerenkov]
path = "/cerenkov/"
host = "localhost"

[sql]
sql_server = "1"
db_name = "db"
db_host = "localhost"
db_user = "user"
db_pass = "password"
db_ports = "3306"
prefix = ""

[template]
type = "file"
name = "example"

[tidy]
use_tidy = "false"

[debug]
debug = "true"

[auth]
auth_scheme = "simple"
activation_salt = "cerenkov"
activation_page = "http://example.com/index.php"
activate = "false"
cookie_name = "cerenkov"

[iosystem]
strip_tags = "true"
allowed_tags = ""
entity_quote_html = "true"
hash_type = "sha512"

[file]
path_type = "unix"
create_mode = "0755"
temp_dir = "system/tmp/"
temp_prefix = "CERENKOV_"

[cache]
enable_cache = "false"
time = 1
image_time = "60"
