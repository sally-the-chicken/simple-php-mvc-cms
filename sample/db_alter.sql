ALTER TABLE `acl_users` ADD CONSTRAINT `fk_acl_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `acl_users` ADD CONSTRAINT `fk_acl_users_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `acl_users`(`id`);

ALTER TABLE `acl_resources` ADD CONSTRAINT `fk_acl_resources_created_by` FOREIGN KEY (`created_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `acl_resources` ADD CONSTRAINT `fk_acl_resources_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `acl_users`(`id`);

ALTER TABLE `acl_accessrights` ADD CONSTRAINT `fk_acl_accessrights_created_by` FOREIGN KEY (`created_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `acl_accessrights` ADD CONSTRAINT `fk_acl_accessrights_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `acl_accessrights` ADD CONSTRAINT `fk_acl_accessrights_role_id` FOREIGN KEY (`role_id`) REFERENCES `acl_accessrights`(`id`);
ALTER TABLE `acl_accessrights` ADD CONSTRAINT `fk_acl_accessrights_resource_id` FOREIGN KEY (`resource_id`) REFERENCES `acl_resources`(`id`);

ALTER TABLE `acl_user_accessrights` ADD CONSTRAINT `fk_acl_user_accessrights_user_id` FOREIGN KEY (`user_id`) REFERENCES `acl_users`(`id`);
ALTER TABLE `acl_user_accessrights` ADD CONSTRAINT `fk_acl_user_accessrights_accessright_id` FOREIGN KEY (`accessright_id`) REFERENCES `acl_accessrights`(`id`);

ALTER TABLE `articles` ADD CONSTRAINT `fk_articles_created_by` FOREIGN KEY (`created_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `articles` ADD CONSTRAINT `fk_articles_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `acl_users`(`id`);

ALTER TABLE `tags` ADD CONSTRAINT `fk_tags_created_by` FOREIGN KEY (`created_by`) REFERENCES `acl_users`(`id`);
ALTER TABLE `tags` ADD CONSTRAINT `fk_tags_modified_by` FOREIGN KEY (`modified_by`) REFERENCES `acl_users`(`id`);

ALTER TABLE `article_tags` ADD CONSTRAINT `fk_article_tags_article_id` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`);
ALTER TABLE `article_tags` ADD CONSTRAINT `fk_article_tags_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`);
