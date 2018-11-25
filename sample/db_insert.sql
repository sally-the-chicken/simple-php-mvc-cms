INSERT INTO `acl_resources` (`id`, `name`, `description`, `created_date`, `modified_date`, `created_by`, `modified_by`) VALUES 
(NULL, 'users.view', 'View users', now(), now(), '1', '1'), 
(NULL, 'users.edit', 'Edit users', now(), now(), '1', '1'), 
(NULL, 'articles.view', 'View articles', now(), now(), '1', '1'), 
(NULL, 'articles.edit', 'Edit articles', now(), now(), '1', '1');

INSERT INTO `acl_users` (`id`, `login`, `password`, `email`, `display_name`, `activation_key`, `status`, `last_login_time`, `last_login_ip`, `created_date`, `modified_date`, `created_by`, `modified_by`) VALUES 
(NULL, 'admin', '7b3f3263d7fa2881d39f7383cdd4b89204c6c4f1', 'admin@email.org', 'Admin', NULL, '1', NULL, NULL, now(), now(), '1', '1');

INSERT INTO `acl_accessrights` (`id`, `role_id`, `resource_id`, `role_name`, `created_date`, `modified_date`, `created_by`, `modified_by`) VALUES 
(NULL, NULL, NULL, 'admin', now(), now(), '1', '1'), 
(NULL, NULL, NULL, 'editor', now(), now(), '1', '1'), 
(NULL, NULL, NULL, 'guest', now(), now(), '1', '1');

INSERT INTO `acl_accessrights` (`id`, `role_id`, `resource_id`, `role_name`, `created_date`, `modified_date`, `created_by`, `modified_by`) 
SELECT 
NULL, `accessright_role`.`id`, `resource`.`id`, NULL, now(), now(), '1', '1'
FROM `acl_resources` as `resource`, 
`acl_accessrights` as `accessright_role`
WHERE `accessright_role`.`role_name` = 'admin'
AND `resource`.`name` IN ('users.view', 'users.edit', 'articles.view', 'articles.edit');

INSERT INTO `acl_accessrights` (`id`, `role_id`, `resource_id`, `role_name`, `created_date`, `modified_date`, `created_by`, `modified_by`) 
SELECT 
NULL, `accessright_role`.`id`, `resource`.`id`, NULL, now(), now(), '1', '1'
FROM `acl_resources` as `resource`, 
`acl_accessrights` as `accessright_role`
WHERE `accessright_role`.`role_name` = 'editor'
AND `resource`.`name` IN ('articles.view', 'articles.edit');

INSERT INTO `acl_accessrights` (`id`, `role_id`, `resource_id`, `role_name`, `created_date`, `modified_date`, `created_by`, `modified_by`) 
SELECT 
NULL, `accessright_role`.`id`, `resource`.`id`, NULL, now(), now(), '1', '1'
FROM `acl_resources` as `resource`, 
`acl_accessrights` as `accessright_role`
WHERE `accessright_role`.`role_name` = 'guest'
AND `resource`.`name` IN ('articles.view');

INSERT INTO `acl_user_accessrights` (`id`, `user_id`, `accessright_id`, `created`)
SELECT NULL, `user`.`id`, `accessright_role`.`id`, now()
FROM `acl_accessrights` as `accessright_role`, 
`acl_users` as `user`
WHERE `accessright_role`.`role_name` = 'admin'
AND `user`.`login` = 'admin';

