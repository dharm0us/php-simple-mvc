DROP USER IF EXISTS 'r_user'@'%';
CREATE USER 'r_user'@'%' IDENTIFIED BY 'r_pwd';
GRANT SELECT ON testdb_mvc.* TO 'r_user'@'%';

DROP USER IF EXISTS 'w_user'@'%';
CREATE USER 'w_user'@'%' IDENTIFIED BY 'w_pwd';
GRANT SELECT,INSERT,UPDATE,DELETE ON testdb_mvc.* TO 'w_user'@'%';

DROP USER IF EXISTS 'a_user'@'%';
CREATE USER 'a_user'@'%' IDENTIFIED BY 'a_pwd';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,REFERENCES,ALTER ON testdb_mvc.* TO 'a_user'@'%';
