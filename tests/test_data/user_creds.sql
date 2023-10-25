GRANT SELECT ON testdb_mvc.* TO 'r_user'@'%';
ALTER USER 'r_user'@'%' IDENTIFIED BY 'r_pwd';

GRANT SELECT,INSERT,UPDATE,DELETE ON testdb_mvc.* TO 'w_user'@'%';
ALTER USER 'w_user'@'%' IDENTIFIED BY 'w_pwd';

GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,REFERENCES,ALTER ON testdb_mvc.* TO 'a_user'@'%';
ALTER USER 'a_user'@'%' IDENTIFIED BY 'a_pwd';

FLUSH PRIVILEGES;
