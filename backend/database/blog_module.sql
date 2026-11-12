-- Blog permission role assignment. Import after cms_database.sql and blog_module.sql.
INSERT IGNORE INTO permissions(permission_key,description) VALUES ('blog.manage','Create, edit, publish, and archive blog posts');
INSERT IGNORE INTO role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM roles r JOIN permissions p ON r.name='Super Admin' AND p.permission_key='blog.manage';
